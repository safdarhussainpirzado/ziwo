<?php

namespace App\Telephony\Clients;

use App\Telephony\Contracts\TelephonyClientInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ZiwoClient implements TelephonyClientInterface
{
    protected string $baseUrl;
    protected string $proxyUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.ziwo.base_url', 'https://api.ziwo.io/v1'), '/');
        $this->proxyUrl = rtrim(config('services.ziwo.proxy_url', 'https://nayatel-api.aswat.co'), '/');
    }

    /**
     * Helper to construct the ZIWO API URL.
     *
     * The proxy URL (e.g. https://nayatel-api.aswat.co) IS the full API base —
     * we do NOT double-wrap it with api.ziwo.io. Endpoints are appended directly.
     */
    protected function buildUrl(string $endpoint): string
    {
        $endpoint = ltrim($endpoint, '/');

        if (!empty($this->proxyUrl)) {
            return "{$this->proxyUrl}/{$endpoint}";
        }

        return "{$this->baseUrl}/{$endpoint}";
    }

    /**
     * Execute a request against the ZIWO proxy and return the decoded JSON body.
     * Throws on network failure or non-2xx response so the caller can surface
     * the real error to the UI.
     *
     * For GET/DELETE, $data is sent as a query string (matches ZIWO's
     * `?key=val&key=val` convention). For POST/PUT/PATCH, $data is sent
     * as a JSON body.
     */
    protected function request(string $method, string $endpoint, array $data = [], ?string $token = null): array
    {
        $url = $this->buildUrl($endpoint);

        $headers = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($token) {
            $headers['access_token'] = $token;
        }

        // Convert GET/DELETE to query string. Arrays (e.g. `dataset`) are
        // serialized as the repeated `key=val` form that ZIWO uses:
        //   ?dataset=tags&dataset=notes&dataset=customer
        // rather than http_build_query's bracket form `dataset[0]=...`.
        $queryOrBody = ['json' => $data];
        if (in_array(strtoupper($method), ['GET', 'DELETE'], true) && !empty($data)) {
            $sep = strpos($url, '?') === false ? '?' : '&';
            $qs = '';
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $qs .= ($qs === '' ? '' : '&') . urlencode($key) . '=' . urlencode($v);
                    }
                } else {
                    $qs .= ($qs === '' ? '' : '&') . urlencode($key) . '=' . urlencode($value);
                }
            }
            if ($qs !== '') $url .= $sep . $qs;
            $queryOrBody = [];
        }

        $response = Http::withHeaders($headers)
            ->timeout(10)
            ->send($method, $url, $queryOrBody);

        if ($response->successful()) {
            $json = $response->json() ?? [];
            if (!isset($json['status'])) {
                $json['status'] = 'success';
            }
            return $json;
        }

        Log::error("ZIWO API error: [{$response->status()}] {$method} {$url} → {$response->body()}");

        $body = $response->json();
        $message = $body['error']['message']
            ?? $body['message']
            ?? $body['content']['message']
            ?? "ZIWO gateway returned HTTP {$response->status()}";

        return [
            'result'  => false,
            'status'  => 'error',
            'message' => $message,
        ];
    }

    public function login(string $username, string $password): array
    {
        $response = $this->request('POST', 'auth/login', [
            'username' => $username,
            'password' => $password,
        ]);

        if (($response['result'] ?? false) === true && !empty($response['content']['access_token'])) {
            return [
                'status'      => 'success',
                'access_token' => $response['content']['access_token'],
                'expires_in'  => 28800,
                'user'        => [
                    'username'  => $response['content']['username'] ?? $username,
                    'extension' => $response['content']['ccLogin'] ?? '',
                ],
            ];
        }

        return [
            'status'  => 'error',
            'message' => $response['message'] ?? 'Authentication failed',
        ];
    }

    public function clickToCall(string $agentToken, string $customerNumber): array
    {
        return $this->request('POST', 'calls', [
            'phone' => $customerNumber,
        ], $agentToken);
    }

    public function holdCall(string $agentToken, string $callId): array
    {
        return $this->request('POST', "calls/{$callId}/hold", [], $agentToken);
    }

    public function resumeCall(string $agentToken, string $callId): array
    {
        return $this->request('POST', "calls/{$callId}/unhold", [], $agentToken);
    }

    public function muteCall(string $agentToken, string $callId): array
    {
        return $this->request('POST', "calls/{$callId}/mute", [], $agentToken);
    }

    public function unmuteCall(string $agentToken, string $callId): array
    {
        return $this->request('POST', "calls/{$callId}/unmute", [], $agentToken);
    }

    public function hangupCall(string $agentToken, string $callId): array
    {
        return $this->request('DELETE', "calls/{$callId}", [], $agentToken);
    }

    public function transferCall(string $agentToken, string $callId, string $targetNumber, string $type = 'blind'): array
    {
        return $this->request('POST', 'calls/transfer', [
            'call_id' => $callId,
            'target'  => $targetNumber,
            'type'    => $type,
        ], $agentToken);
    }

    public function conferenceCall(string $agentToken, string $callId, string $targetNumber, ?string $roomId = null): array
    {
        return $this->request('POST', 'calls/conference', [
            'call_id' => $callId,
            'room_id' => $roomId ?? $callId,
            'target'  => $targetNumber,
        ], $agentToken);
    }

    public function leaveConference(string $agentToken, string $roomId): array
    {
        return $this->request('POST', 'calls/conference/leave', [
            'room_id' => $roomId,
        ], $agentToken);
    }

    public function toggleRecording(string $agentToken, string $callId, bool $pause): array
    {
        $endpoint = $pause ? "calls/{$callId}/recording/pause" : "calls/{$callId}/recording/resume";
        return $this->request('POST', $endpoint, [], $agentToken);
    }

    public function getCallStatus(string $agentToken, string $callId): array
    {
        return $this->request('GET', "calls/{$callId}", [], $agentToken);
    }

    public function getHealthStatus(): bool
    {
        try {
            $url = $this->buildUrl('health');
            $response = Http::timeout(3)->get($url);
            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }

    public function lookupCrmCustomer(string $agentToken, string $phone): array
    {
        $crmBaseUrl = rtrim($this->proxyUrl ?: $this->baseUrl, '/');
        $url = "{$crmBaseUrl}/agent/crm/customers";

        try {
            $response = Http::withHeaders([
                'Accept'       => 'application/json',
                'access_token' => $agentToken,
            ])
                ->timeout(5)
                ->get($url, [
                    'phone' => $phone,
                    'order' => 'name',
                    'limit' => 1,
                    'skip'  => 0,
                ]);

            if ($response->successful()) {
                return $response->json() ?? ['result' => false, 'content' => []];
            }

            Log::warning("ZIWO CRM lookup failed [{$response->status()}] for phone {$phone}");
            return ['result' => false, 'content' => []];
        } catch (Exception $e) {
            Log::error("ZIWO CRM customer lookup exception: {$e->getMessage()}");
            return ['result' => false, 'content' => []];
        }
    }

    // ── Admin / Dashboard API methods ──

    public function getAgentsList(string $adminToken, int $limit = 50, int $skip = 0): array
    {
        return $this->request('GET', 'agents', [
            'limit' => $limit,
            'skip'  => $skip,
        ], $adminToken);
    }

    public function getAgentDetail(string $adminToken, string $username): array
    {
        return $this->request('GET', "agents/{$username}/stats", [], $adminToken);
    }

    public function getCallHistory(string $adminToken, array $filters = []): array
    {
        return $this->request('GET', 'calls/history', $filters, $adminToken);
    }

    /**
     * Live call log for the authenticated agent.
     *
     * Probed live against nayatel-api.aswat.co:
     *   GET /agents/channels/calls?limit=50&skip=0&dataset=tags&dataset=notes&dataset=customer
     * Returns { result, content: [{ callID, direction, disposition, duration, callerIDNumber,
     *                                customer, queueName, recordingFile, startedAt, ... }] }
     */
    public function getAgentCallHistory(string $agentToken, int $limit = 50, int $skip = 0): array
    {
        return $this->request('GET', 'agents/channels/calls', [
            'limit'   => $limit,
            'skip'    => $skip,
            'dataset' => ['tags', 'notes', 'customer'],
        ], $agentToken);
    }

    public function getWallboardLive(string $adminToken): array
    {
        return $this->request('GET', 'stats/live', [], $adminToken);
    }

    public function getQueues(string $adminToken): array
    {
        return $this->request('GET', 'queues', [], $adminToken);
    }

    public function getCallRecording(string $adminToken, string $callId): array
    {
        return $this->request('GET', "calls/{$callId}/recording", [], $adminToken);
    }

    /**
     * Read the live agent presence from /profile -> liveInfo.status.
     * Returns the ZIWO status string ("Available", "On Break", ...).
     */
    public function getAgentLiveStatus(string $agentToken): array
    {
        $resp = $this->request('GET', 'profile', [], $agentToken);
        if (($resp['result'] ?? false) !== true) {
            return $resp;
        }
        $live = $resp['content']['liveInfo'] ?? null;
        return [
            'result'       => true,
            'agent_status' => $live['status'] ?? 'Offline',
        ];
    }

    /**
     * Change agent presence via the ZIWO proxy.
     *
     * Probed live: PUT /agents/status with body
     *   { number: <int>, comment: "" }
     * returns 200 { result: true, content: {}, info: {} }.
     *
     * The `number` field is the ZIWO-internal id of the status row. We look
     * it up on every call from /agent/statuses?enabled=true so the mapping
     * stays correct even if the admin renumbers or renames statuses in the
     * console. Falls back to the canonical map if the lookup fails.
     */
    public function setAgentStatus(string $agentToken, string $username, string $status): array
    {
        $canonical = [
            'available' => 1,
            'break'     => 2,
            'meeting'   => 3,
            'outgoing'  => 4,
        ];
        $key = strtolower($status);
        if (!isset($canonical[$key])) {
            return ['status' => 'error', 'message' => "Unknown status '{$status}'"];
        }

        $number = $this->resolveStatusNumber($agentToken, $key, $canonical[$key]);

        return $this->request('PUT', 'agents/status', [
            'number'  => $number,
            'comment' => '',
        ], $agentToken);
    }

    /**
     * GET /agent/statuses?enabled=true returns the configured statuses:
     *   { id, name, behaviour, ... }
     *
     * We match by lower-cased `name` / `behaviour` so admin renames don't
     * silently break the wire number. Cache for the request lifetime to
     * avoid hitting the proxy on every status flip.
     */
    private ?array $statusCache = null;

    private function resolveStatusNumber(string $agentToken, string $key, int $fallback): int
    {
        if ($this->statusCache === null) {
            $resp = $this->request('GET', 'agent/statuses', ['enabled' => 'true'], $agentToken);
            $rows = $resp['content'] ?? [];
            $this->statusCache = is_array($rows) ? $rows : [];
        }

        $aliases = [
            'available' => ['available'],
            'break'     => ['on break', 'break', 'away'],
            'meeting'   => ['meeting'],
            'outgoing'  => ['outgoing', 'busy'],
        ];
        $wanted = $aliases[$key] ?? [$key];

        foreach ($this->statusCache as $row) {
            $name = strtolower((string)($row['name']       ?? ''));
            $beh  = strtolower((string)($row['behaviour'] ?? ''));
            foreach ($wanted as $w) {
                if ($name === $w || $beh === $w) {
                    return (int)($row['id'] ?? $fallback);
                }
            }
        }
        return $fallback;
    }
}
