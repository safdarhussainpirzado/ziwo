<?php

namespace App\Http\Controllers;

use App\Telephony\Contracts\TelephonyClientInterface;
use App\Telephony\Contracts\TelephonyServiceInterface;
use App\Telephony\Contracts\TelephonyRepositoryInterface;
use App\Telephony\DTOs\ContactDTO;
use App\Telephony\DTOs\CallLogDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class TelephonyController extends Controller
{
    public function __construct(
        protected TelephonyServiceInterface $service,
        protected TelephonyRepositoryInterface $repository,
        protected TelephonyClientInterface $client
    ) {}

    /**
     * Authenticate the active agent with ZIWO APIs.
     */
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $result = $this->service->authenticateAgent(
            auth()->id(),
            $request->input('username'),
            $request->input('password')
        );

        // Attach contact_center name for frontend SDK initialization
        if (($result['status'] ?? '') === 'success') {
            $result['contact_center'] = config('services.ziwo.contact_center', 'nayatel');
            return response()->json($result);
        }

        return response()->json($result, 401);
    }

    /**
     * Get active agent configuration and status.
     * Also returns any currently active/ringing call so the frontend
     * can show the inbound ringing overlay immediately on next poll.
     *
     * NOTE: This is a JSON-only API endpoint.
     * If accessed directly via browser (not an AJAX request), redirect to landing page.
     */
    public function getStatus(Request $request)
    {
        // ── Guard: redirect plain browser navigation away from this JSON endpoint ──
        // If someone visits /telephony/status directly (not via fetch/XHR), they
        // would see raw JSON. Detect AJAX by X-Requested-With or Accept headers.
        if (!$request->ajax() && !$request->expectsJson() && !$request->wantsJson()) {
            return redirect()->to(auth()->user()->getLandingPageRoute());
        }

        $userId = auth()->id();
        $config = $this->repository->getAgentConfig($userId);

        if (!$config || empty($config->ziwo_token)) {
            return response()->json([
                'agent_status'     => 'offline',
                'ziwo_username'    => null,
                'is_authenticated' => false,
                'active_call'      => null,
            ]);
        }

        // Look up the most recent non-terminal call for this agent
        $activeCall   = $this->repository->getActiveCallForAgent($userId);
        $agentStatus  = $config->agent_status;
        $activeCallData = null;

        if ($activeCall) {
            // Map DB call status → frontend phone status
            if ($activeCall->status === 'ringing' && $activeCall->direction === 'inbound') {
                $agentStatus = 'ringing_inbound';
            } elseif ($activeCall->status === 'ringing' && $activeCall->direction === 'outbound') {
                $agentStatus = 'ringing';
            } elseif (in_array($activeCall->status, ['active', 'speaking'])) {
                $agentStatus = 'speaking';
            } elseif ($activeCall->status === 'held') {
                $agentStatus = 'held';
            }

            $activeCallData = [
                'id'               => $activeCall->call_id,
                'uuid'             => $activeCall->call_uuid,
                'caller_number'    => $activeCall->caller_number,
                'caller_name'      => $activeCall->metadata['caller_name'] ?? '',
                'direction'        => $activeCall->direction,
                'is_held'          => $activeCall->metadata['is_held'] ?? false,
                'is_muted'         => $activeCall->metadata['is_muted'] ?? false,
                'recording_paused' => $activeCall->metadata['recording_paused'] ?? false,
                'seconds_duration' => $activeCall->start_time
                    ? (int) $activeCall->start_time->diffInSeconds(now())
                    : 0,
            ];
        } else {
            // ── Prevent stuck states ──
            // If there's no active call in our repository, the agent cannot be speaking, ringing, or held.
            if (in_array($agentStatus, ['speaking', 'ringing', 'ringing_inbound', 'held'])) {
                $agentStatus = 'online';
            }
        }

        return response()->json([
            'agent_status'     => $agentStatus,
            'ziwo_username'    => $config->ziwo_username,
            'ziwo_token'       => $config->ziwo_token,  // needed to init SDK on the frontend
            'contact_center'   => config('services.ziwo.contact_center', 'nayatel'),
            'is_authenticated' => true,
            'expires_at'       => $config->expires_at?->toIso8601String(),
            'active_call'      => $activeCallData,
        ]);

    }

    /**
     * Get recent calls for authenticated agent.
     */
    public function recentCalls()
    {
        $calls = $this->repository->getRecentCalls(auth()->id());
        return response()->json($calls);
    }

    /**
     * GET /telephony/calls/live — fetch the agent's live call history directly
     * from the ZIWO proxy.
     *
     * Live path (probed against nayatel-api.aswat.co):
     *   GET /agents/channels/calls?limit=50&skip=0&dataset=tags&dataset=notes&dataset=customer
     *
     * Each record has: callID, callerIDNumber, direction, disposition, duration,
     * result, startedAt, endedAt, queueName, position, customer, recordingFile,
     * and a tags/notes/customer sub-object when those datasets are requested.
     *
     * The proxy requires no admin token; the agent's own access_token works
     * because ZIWO scopes the call list to calls that touched that agent.
     */
    public function liveCallHistory(Request $request)
    {
        $userId = auth()->id();
        $config = $this->repository->getAgentConfig($userId);
        if (!$config || empty($config->ziwo_token)) {
            return response()->json([
                'status'  => 'error',
                'calls'   => [],
                'message' => 'Agent not authenticated with telephony gateway',
            ], 400);
        }

        $limit  = max(1, min(200, (int) $request->input('limit', 50)));
        $skip   = max(0, (int) $request->input('skip', 0));
        $resp   = $this->client->getAgentCallHistory($config->ziwo_token, $limit, $skip);

        if (($resp['result'] ?? false) !== true) {
            return response()->json([
                'status'  => 'error',
                'calls'   => [],
                'message' => $resp['message'] ?? 'ZIWO returned an error',
            ], 502);
        }

        $raw = $resp['content'] ?? [];
        $calls = collect($raw)->map(function ($c) {
            $direction = $c['direction'] ?? 'unknown';

            // For outbound, ZIWO's callerIDNumber is the trunk/PBX itself
            // (e.g. "8778150"); the dialed destination is in didCalled or
            // embedded in channelName ("verto.rtc/+923002551224").
            $dialed = $c['didCalled'] ?? $this->extractDialedFromChannel($c['channelName'] ?? '');
            $incomingNumber = $c['callerIDNumber'] ?? $c['callerIDName'] ?? null;
            $displayNumber = $direction === 'outbound' ? ($dialed ?: $incomingNumber) : $incomingNumber;
            $redialTarget = $direction === 'outbound' ? ($dialed ?: $incomingNumber) : $incomingNumber;

            $customer = $c['customer'] ?? [];
            $customerName = trim(
                ($customer['firstName'] ?? '') . ' ' . ($customer['lastName'] ?? '')
            );

            $startedAt = $c['startedAt'] ?? null;
            $timeAgo = $startedAt ? $this->timeAgoIso($startedAt) : null;

            return [
                'call_id'        => $c['callID'] ?? $c['id'] ?? null,
                'caller_number'  => $displayNumber,
                'dialed_number'  => $dialed,
                'redial_number'  => $redialTarget,
                'caller_name'    => $customerName !== '' ? $customerName : null,
                'direction'      => $direction,
                'status'         => $c['result']   ?? $c['disposition'] ?? 'unknown',
                'duration_sec'   => (int)($c['duration']  ?? 0),
                'talk_time'      => (int)($c['talkTime']  ?? 0),
                'ring_time'      => (int)($c['ringTime']  ?? 0),
                'queue_name'     => $c['queueName'] ?? null,
                'started_at'     => $startedAt,
                'started_at_iso' => $startedAt,
                'time_ago'       => $timeAgo,
                'ended_at'       => $c['endedAt']   ?? null,
                'recording_file' => $c['recordingFile'] ?? null,
                'source'         => 'ziwo_live',
            ];
        })->values()->all();

        return response()->json([
            'status' => 'success',
            'calls'  => $calls,
            'count'  => count($calls),
        ]);
    }

    /**
     * Extract the dialled phone number from a ZIWO channelName string.
     * Recognised prefixes:
     *   "verto.rtc/<number>"        → <number>   (agent leg)
     *   "sofia/external/<number>"   → <number>   (PBX outbound to PSTN)
     *   "sofia/internal/<number>"   → null       (inbound from DID — caller id is the customer)
     *   "sofia/internal/<ip>"       → null       (network address, not a number)
     */
    private function extractDialedFromChannel(string $channelName): ?string
    {
        if ($channelName === '') return null;
        foreach (['verto.rtc/', 'sofia/external/'] as $prefix) {
            if (str_starts_with($channelName, $prefix)) {
                $rest = substr($channelName, strlen($prefix));
                // Strip anything after '@' (e.g. '@10.80.11.55')
                if (($at = strpos($rest, '@')) !== false) {
                    $rest = substr($rest, 0, $at);
                }
                return $rest !== '' ? $rest : null;
            }
        }
        return null;
    }

    /**
     * Convert a ZIWO ISO timestamp into a short human-friendly "X ago" string.
     */
    private function timeAgoIso(?string $iso): ?string
    {
        if (!$iso) return null;
        try {
            $then = new \DateTimeImmutable($iso);
        } catch (\Exception) {
            return null;
        }
        $now  = new \DateTimeImmutable('now', $then->getTimezone());
        $diff = $now->getTimestamp() - $then->getTimestamp();
        if ($diff < 0)   return 'just now';
        if ($diff < 60)   return $diff . 's ago';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        return floor($diff / 86400) . 'd ago';
    }

    /**
     * POST /telephony/status — Set agent presence/availability.
     * Body: { status: "available"|"meeting"|"break"|"outgoing" }
     *
     * The ZIWO proxy has no HTTP set-status endpoint (probed 404 across all
     * candidate paths). Status changes are driven by the SDK on the client.
     * This endpoint persists the requested state locally and returns OK.
     */
    public function setStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:available,meeting,break,outgoing',
        ]);

        $userId = auth()->id();
        $user = $request->user();
        $config = $this->repository->getAgentConfig($userId);

        if (!$config || empty($config->ziwo_token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Agent not authenticated with telephony gateway',
            ], 400);
        }

        $result = $this->service->setAgentStatus(
            $userId,
            $user->username ?? $user->email,
            $config->ziwo_token,
            $request->input('status')
        );

        return response()->json($result);
    }

    /**
     * GET /telephony/status/live — pull current live presence from ZIWO.
     * Maps /profile.liveInfo.status to the UI's 4-state vocabulary.
     */
    public function refreshLiveStatus(Request $request)
    {
        $userId = auth()->id();
        $config = $this->repository->getAgentConfig($userId);
        if (!$config || empty($config->ziwo_token)) {
            return response()->json([
                'status'       => 'error',
                'agent_status' => 'offline',
            ], 400);
        }
        $resp = $this->client->getAgentLiveStatus($config->ziwo_token);
        if (!is_array($resp) || ($resp['result'] ?? false) !== true) {
            return response()->json($resp ?: ['status' => 'error', 'agent_status' => 'offline'], 502);
        }
        $raw = $resp['agent_status'] ?? 'Offline';
        $mapped = $this->mapZiwoStatusToUi($raw);
        $this->repository->updateAgentStatus($userId, $mapped);
        session(["phone_agent_status_{$userId}" => $mapped]);
        return response()->json([
            'status'       => 'success',
            'agent_status' => $mapped,
            'ziwo_status'  => $raw,
        ]);
    }

    private function mapZiwoStatusToUi(string $ziwoStatus): string
    {
        return match (strtolower($ziwoStatus)) {
            'available', 'ready'        => 'available',
            'on break', 'break', 'away' => 'break',
            'meeting'                   => 'meeting',
            'outgoing', 'busy'          => 'outgoing',
            default                     => 'offline',
        };
    }

    /**
     * Answer incoming call.
     * Updates call status to 'active' and agent status to 'speaking'.
     */
    public function answer(Request $request)
    {
        $userId = auth()->id();
        $request->validate(['call_id' => 'required|string']);

        $call = $this->repository->getCallByZiwoId($request->input('call_id'));
        if ($call) {
            $dto = new CallLogDTO(
                callId: $call->call_id,
                callUuid: $call->call_uuid,
                agentId: $userId,
                callerNumber: $call->caller_number,
                direction: $call->direction,
                status: 'active',
                startTime: $call->start_time ?? now(),
                metadata: array_merge($call->metadata ?? [], ['answered_at' => now()->toIso8601String()])
            );
            $this->repository->logCall($dto);
        }

        // Update agent status so the next poll reflects 'speaking'
        $this->repository->updateAgentStatus($userId, 'speaking');

        return response()->json(['status' => 'success', 'message' => 'Call marked as answered']);
    }

    /**
     * Disconnect the agent from ZIWO telephony.
     */
    public function disconnect()
    {
        $result = $this->service->disconnectAgent(auth()->id());
        return response()->json(['status' => $result ? 'success' : 'error']);
    }

    /**
     * Dial an outbound phone number.
     */
    public function dial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $result = $this->service->dialOutbound(auth()->id(), $request->input('phone_number'));
        return response()->json($result);
    }

    /**
     * Hold active call.
     */
    public function hold(Request $request)
    {
        $request->validate(['call_id' => 'required|string']);
        $result = $this->service->hold(auth()->id(), $request->input('call_id'));
        return response()->json($result);
    }

    /**
     * Resume held call.
     */
    public function resume(Request $request)
    {
        $request->validate(['call_id' => 'required|string']);
        $result = $this->service->resume(auth()->id(), $request->input('call_id'));
        return response()->json($result);
    }

    /**
     * Mute microphone.
     */
    public function mute(Request $request)
    {
        $request->validate(['call_id' => 'required|string']);
        $result = $this->service->mute(auth()->id(), $request->input('call_id'));
        return response()->json($result);
    }

    /**
     * Unmute microphone.
     */
    public function unmute(Request $request)
    {
        $request->validate(['call_id' => 'required|string']);
        $result = $this->service->unmute(auth()->id(), $request->input('call_id'));
        return response()->json($result);
    }

    /**
     * Hang up active call.
     */
    public function hangup(Request $request)
    {
        $request->validate(['call_id' => 'required|string']);
        $result = $this->service->hangup(auth()->id(), $request->input('call_id'));
        return response()->json($result);
    }

    /**
     * Transfer call.
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'call_id' => 'required|string',
            'target_number' => 'required|string',
            'type' => 'nullable|string|in:blind,warm'
        ]);

        $result = $this->service->transfer(
            auth()->id(),
            $request->input('call_id'),
            $request->input('target_number'),
            $request->input('type', 'blind')
        );
        return response()->json($result);
    }

    /**
     * Merge or initiate conference call.
     */
    public function conference(Request $request)
    {
        $request->validate([
            'call_id'       => 'required_without:action|string',
            'target_number' => 'required_without:action|string',
            'room_id'       => 'nullable|string',
            'action'        => 'nullable|string|in:leave',
        ]);

        $result = $this->service->conference(
            auth()->id(),
            $request->input('call_id'),
            $request->input('target_number'),
            $request->input('room_id'),
            $request->input('action')
        );
        return response()->json($result);
    }

    /**
     * Toggle active recording.
     */
    public function toggleRecording(Request $request)
    {
        $request->validate([
            'call_id' => 'required|string',
            'pause' => 'required|boolean'
        ]);

        $result = $this->service->toggleCallRecording(
            auth()->id(),
            $request->input('call_id'),
            $request->input('pause')
        );
        return response()->json($result);
    }

    /**
     * Search the live ZIWO CRM for contacts matching the query.
     * Probed live: GET /agent/crm/customers?dataset=tags&order=name&limit=500&skip=0
     *   → { result, content: [{ id, firstName, lastName, phone, ... }] }
     * Server-side filter on phone/name to keep the wire payload small.
     */
    public function searchCrm(Request $request)
    {
        $userId = auth()->id();
        $config = $this->repository->getAgentConfig($userId);
        if (!$config || empty($config->ziwo_token)) {
            return response()->json(['status' => 'error', 'contacts' => [], 'message' => 'Agent not authenticated']);
        }

        $query    = strtolower(trim((string)$request->input('query', '')));
        $limit    = (int)$request->input('limit', 500);
        $limit    = max(1, min(1000, $limit));
        $proxyUrl = rtrim(config('services.ziwo.proxy_url', 'https://nayatel-api.aswat.co'), '/');

        // Pass `q` to ZIWO so the proxy can filter server-side, and `dataset`
        // as a repeated query param (tags + notes).
        $url = $proxyUrl . '/agent/crm/customers'
            . '?dataset=tags&dataset=notes'
            . '&order=name&limit=' . $limit . '&skip=0'
            . ($query !== '' ? '&q=' . urlencode($query) : '');

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Accept'       => 'application/json',
            'access_token' => $config->ziwo_token,
        ])->timeout(10)->get($url);

        if (!$response->successful()) {
            \Illuminate\Support\Facades\Log::warning("ZIWO CRM search returned [{$response->status()}]: {$response->body()}");
            return response()->json([
                'status'   => 'error',
                'contacts' => [],
                'message'  => "ZIWO CRM returned HTTP {$response->status()}",
            ], $response->status());
        }

        $body = $response->json();
        $raw  = $body['content'] ?? $body['customers'] ?? $body['data'] ?? [];

        $contacts = collect($raw)->map(function ($c) {
            $first = $c['firstName'] ?? '';
            $last  = $c['lastName']  ?? '';
            $name  = trim($first . ' ' . $last);
            if ($name === '') {
                $name = $c['phone'] ?? 'Unknown';
            }
            return [
                'id'            => $c['id'] ?? null,
                'name'          => $name,
                'phone'         => $c['phone'] ?? '',
                'email'         => $c['email'] ?? null,
                'tags'          => $c['tags']  ?? [],
                'source'        => 'ziwo_crm',
            ];
        })->filter(function ($c) use ($query) {
            if ($query === '') return true;
            return str_contains(strtolower($c['name']), $query)
                || str_contains(strtolower($c['phone']), $query);
        })->values()->all();

        return response()->json(['status' => 'success', 'contacts' => $contacts]);
    }

    /**
     * Search and retrieve phonebook contacts.
     */
    public function searchPhonebook(Request $request)
    {
        $query = $request->input('query', '');
        $category = $request->input('category');

        $contacts = $this->repository->searchContacts($query, $category, auth()->id());
        return response()->json(['status' => 'success', 'contacts' => $contacts]);
    }

    /**
     * Save a phonebook contact.
     */
    public function storePhonebook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'phone_number' => 'required|string|max:50',
            'category' => 'required|string|in:beat,sector,zone,emergency,custom',
            'is_favorite' => 'nullable|boolean',
            'metadata' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $dto = new ContactDTO(
            name: $request->input('name'),
            phoneNumber: $request->input('phone_number'),
            category: $request->input('category'),
            createdBy: auth()->id(),
            isFavorite: (bool)$request->input('is_favorite', false),
            metadata: $request->input('metadata', [])
        );

        $contact = $this->repository->saveContact($dto, $request->input('id'));
        return response()->json(['status' => 'success', 'contact' => $contact]);
    }

    /**
     * Delete a contact.
     */
    public function destroyPhonebook($id)
    {
        $contact = \App\Models\PhonebookContact::findOrFail($id);

        // Check ownership if category is custom
        if ($contact->category === 'custom' && $contact->created_by !== auth()->id() && !auth()->user()->hasPermission('super_admin')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized deletion request'], 403);
        }

        $result = $this->repository->deleteContact($id);
        return response()->json(['status' => $result ? 'success' : 'error']);
    }

    /**
     * Fetch the list of active ZIWO queues for the authenticated agent.
     * Live path (probed against nayatel-api.aswat.co):
     *   GET /agent/queues?limit=500&skip=0
     * Returns { result, content: [{ id, name, extension, agents: [...], ... }] }
     */
    public function getQueues(Request $request)
    {
        $userId = auth()->id();
        $config = $this->repository->getAgentConfig($userId);

        if (!$config || empty($config->ziwo_token)) {
            return response()->json(['status' => 'error', 'queues' => [], 'message' => 'Agent not authenticated']);
        }

        $proxyUrl = rtrim(config('services.ziwo.proxy_url', 'https://nayatel-api.aswat.co'), '/');
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Accept'       => 'application/json',
            'access_token' => $config->ziwo_token,
        ])->timeout(8)->get("{$proxyUrl}/agent/queues", [
            'limit'   => 500,
            'skip'    => 0,
            'dataset' => ['agents', 'stats'],
        ]);

        if (!$response->successful()) {
            \Illuminate\Support\Facades\Log::warning("ZIWO agent/queues API returned [{$response->status()}]: {$response->body()}");
            return response()->json([
                'status'  => 'error',
                'queues'  => [],
                'message' => "ZIWO /agent/queues returned HTTP {$response->status()}",
            ], $response->status());
        }

        $body = $response->json();
        $rawQueues = $body['content'] ?? $body['queues'] ?? $body['data'] ?? [];
        $queues = collect($rawQueues)->map(function ($q) {
            $ext = $q['extension'] ?? $q['queueNumber'] ?? $q['number'] ?? $q['id'] ?? '';
            return [
                'id'      => (string)($q['id'] ?? $ext),
                'name'    => $q['name'] ?? 'Queue',
                'number'  => (string)$ext,
                'agents'  => is_array($q['agents'] ?? null) ? count($q['agents']) : ($q['agentCount'] ?? 0),
                'waiting' => $q['waitingCount'] ?? $q['waiting'] ?? 0,
            ];
        })->values()->all();

        return response()->json(['status' => 'success', 'queues' => $queues]);
    }

    /**
     * Fetch the list of active ZIWO teammates (agents) for the authenticated agent.
     * Live path (probed against nayatel-api.aswat.co):
     *   GET /agents/channels/calls/listAgents
     * Returns { result, content: [{ id, firstName, lastName, ccLogin, ... }] }
     */
    public function getTeammates(Request $request)
    {
        $userId = auth()->id();
        $config = $this->repository->getAgentConfig($userId);

        if (!$config || empty($config->ziwo_token)) {
            return response()->json(['status' => 'error', 'teammates' => [], 'message' => 'Agent not authenticated']);
        }

        $proxyUrl = rtrim(config('services.ziwo.proxy_url', 'https://nayatel-api.aswat.co'), '/');
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Accept'       => 'application/json',
            'access_token' => $config->ziwo_token,
        ])->timeout(8)->get("{$proxyUrl}/agents/channels/calls/listAgents");

        if (!$response->successful()) {
            \Illuminate\Support\Facades\Log::warning("ZIWO listAgents API returned [{$response->status()}]: {$response->body()}");
            return response()->json([
                'status'     => 'error',
                'teammates'  => [],
                'message'    => "ZIWO /agents/channels/calls/listAgents returned HTTP {$response->status()}",
            ], $response->status());
        }

        $body = $response->json();
        $rawUsers = $body['content'] ?? $body['users'] ?? $body['data'] ?? [];

        $teammates = collect($rawUsers)->map(function ($u, $idx) {
            $firstName = $u['firstName'] ?? '';
            $lastName  = $u['lastName'] ?? '';
            $fullName  = trim($firstName . ' ' . $lastName);
            if (empty($fullName)) {
                $fullName = $u['username'] ?? $u['email'] ?? 'Agent';
            }

            $rawStatus = strtolower($u['status'] ?? 'active');
            $status = match (true) {
                in_array($rawStatus, ['active', 'online', 'available', 'ready']) => 'online',
                in_array($rawStatus, ['busy', 'oncall', 'speaking'])              => 'busy',
                in_array($rawStatus, ['break', 'away', 'paused'])                  => 'away',
                in_array($rawStatus, ['meeting', 'dnd'])                          => 'meeting',
                default                                                          => 'offline',
            };

            $ext = $u['ccLogin'] ?? $u['contactNumber'] ?? '';
            if (empty($ext)) {
                $ext = (string)($u['id'] ?? '');
            }

            return [
                'id'     => $u['id'] ?? $idx + 1,
                'name'   => $fullName,
                'ext'    => (string)$ext,
                'status' => $status,
                'number' => (string)$ext,
            ];
        })->values()->all();

        return response()->json(['status' => 'success', 'teammates' => $teammates]);
    }

    /**
     * Roster of agents (local users with telephony config OR 'agent' role),
     * enriched with live ZIWO presence. Used by the teammates list in the
     * transfer overlay and the Add-or-Call panel. Sorted online-first.
     */
    public function agents(Request $request)
    {
        $localUsers = \App\Models\User::query()
            ->where(function ($q) {
                $q->whereHas('telephonyAgentConfig')
                  ->orWhereHas('roles', fn ($r) => $r->where('name', 'agent'));
            })
            ->with('telephonyAgentConfig')
            ->get();

        // Try to fetch live presence from ZIWO; tolerate failure (fall back to DB status).
        $ziwoIndex = [];
        $config = $this->repository->getAgentConfig(auth()->id());
        if ($config && !empty($config->ziwo_token) && !(bool) config('services.ziwo.mock', false)) {
            try {
                $proxyUrl = rtrim(config('services.ziwo.proxy_url', 'https://nayatel-api.aswat.co'), '/');
                $resp = \Illuminate\Support\Facades\Http::withHeaders([
                    'Accept' => 'application/json',
                    'access_token' => $config->ziwo_token,
                ])->timeout(5)->get("{$proxyUrl}/agents/channels/calls/listAgents");
                if ($resp->successful()) {
                    foreach (($resp->json()['content'] ?? []) as $u) {
                        $key = $u['username'] ?? $u['ccLogin'] ?? null;
                        if ($key) $ziwoIndex[$key] = $u;
                    }
                }
            } catch (\Exception $e) {
                // ignore — we'll fall back to local agent_status
            }
        }

        $agents = $localUsers->map(function ($u) use ($ziwoIndex) {
            $ziwoUser = $u->telephonyAgentConfig?->ziwo_username
                ? ($ziwoIndex[$u->telephonyAgentConfig->ziwo_username] ?? null)
                : null;
            $ext = $ziwoUser['contactNumber'] ?? $ziwoUser['ccLogin'] ?? $ziwoUser['extension']
                ?? $u->telephonyAgentConfig?->ziwo_username ?? '';
            $raw = strtolower($ziwoUser['status'] ?? $ziwoUser['ccStatus']
                ?? $u->telephonyAgentConfig?->agent_status ?? 'offline');
            $status = match (true) {
                in_array($raw, ['available','online','active','ready']) => 'available',
                in_array($raw, ['busy','speaking','on-call','oncall','on call']) => 'on-call',
                in_array($raw, ['away','break','lunch','training']) => 'away',
                $raw === 'break' => 'break',
                default => 'offline',
            };
            return [
                'id' => $u->id,
                'name' => $u->name,
                'ext' => (string) $ext,
                'number' => (string) $ext,
                'status' => $status,
                'ziwo_username' => $u->telephonyAgentConfig?->ziwo_username,
                'updated_at' => $u->telephonyAgentConfig?->last_status_change_at?->toIso8601String(),
            ];
        })->sortBy(fn ($a) => match ($a['status']) {
            'available' => 0, 'on-call' => 1, 'away' => 2, 'break' => 3, default => 4,
        })->values();

        return response()->json(['status' => 'success', 'agents' => $agents, 'count' => $agents->count()]);
    }

    /**
     * Live status for a single agent (used when roster > 50 or for targeted refresh).
     */
    public function agentStatus(Request $request, $userId)
    {
        $u = \App\Models\User::with('telephonyAgentConfig')->find($userId);
        if (!$u) return response()->json(['status' => 'error', 'message' => 'Not found'], 404);
        $cfg = $u->telephonyAgentConfig;
        $raw = strtolower($cfg?->agent_status ?? 'offline');
        $status = match (true) {
            in_array($raw, ['available','online','active','ready']) => 'available',
            in_array($raw, ['busy','speaking','on-call']) => 'on-call',
            in_array($raw, ['away','break']) => 'away',
            default => 'offline',
        };
        return response()->json([
            'status' => 'success',
            'user_id' => $u->id,
            'agent_status' => $status,
            'updated_at' => $cfg?->last_status_change_at?->toIso8601String(),
        ]);
    }

    /**
     * Diagnostic endpoint: returns the last N raw webhook payloads.
     *
     * Why this exists:
     *   Outbound calls have been self-disconnecting at ziwo-early without ever
     *   reaching ziwo-active. The root cause is in the PBX response — SIP code,
     *   hangup cause, disconnected_by — all of which arrive in the call.hangup
     *   webhook payload. We log every webhook verbatim in telephony_webhook_logs;
     *   this endpoint exposes the last N records so the agent can see WHY a
     *   particular call was dropped.
     *
     * Usage:
     *   GET /telephony/diagnostics/webhooks?limit=20
     *   GET /telephony/diagnostics/webhooks?limit=20&event=call.hangup
     */
    public function diagnosticWebhooks(Request $request)
    {
        $limit = (int) $request->input('limit', 20);
        $limit = max(1, min(200, $limit));
        $eventFilter = $request->input('event');

        $query = \App\Models\TelephonyWebhookLog::query()
            ->orderByDesc('created_at')
            ->limit($limit);
        if ($eventFilter) {
            $query->where('event_type', $eventFilter);
        }
        $records = $query->get(['id', 'event_type', 'payload', 'processed', 'error_message', 'created_at']);

        return response()->json([
            'status'  => 'success',
            'count'   => $records->count(),
            'records' => $records,
        ]);
    }

    /**
     * Diagnostic endpoint: probes the Aswat/nayatel telephony proxy for the
     * agent's recent call history. Tries the most common call-logs endpoint
     * shapes and returns the raw response from each so the agent (or developer)
     * can see which shape the proxy actually supports.
     *
     * Usage:
     *   GET /telephony/diagnostics/call-history
     */
    public function diagnosticCallHistory(Request $request)
    {
        $userId = auth()->id();
        $config = $this->repository->getAgentConfig($userId);
        if (!$config || empty($config->ziwo_token)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Agent not authenticated with telephony gateway',
            ], 401);
        }

        $proxyUrl = rtrim(config('services.ziwo.proxy_url', 'https://nayatel-api.aswat.co'), '/');
        $token    = $config->ziwo_token;

        $candidates = [
            ['method' => 'GET', 'path' => '/calls', 'query' => ['agent' => $config->ziwo_username, 'limit' => 20]],
            ['method' => 'GET', 'path' => '/calls', 'query' => ['limit' => 20]],
            ['method' => 'GET', 'path' => '/admin/calls', 'query' => ['limit' => 20]],
            ['method' => 'GET', 'path' => '/admin/agents/' . $config->ziwo_username . '/calls', 'query' => ['limit' => 20]],
            ['method' => 'GET', 'path' => '/agent/calls', 'query' => ['limit' => 20]],
            ['method' => 'GET', 'path' => '/calls/history', 'query' => ['limit' => 20]],
        ];

        $results = [];
        foreach ($candidates as $c) {
            $url = $proxyUrl . $c['path'];
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Accept'       => 'application/json',
                    'access_token' => $token,
                ])->timeout(5)->get($url, $c['query']);

                $results[] = [
                    'method'      => $c['method'],
                    'path'        => $c['path'],
                    'query'       => $c['query'],
                    'http_status' => $response->status(),
                    'ok'          => $response->successful(),
                    'body'        => $response->json() ?? $response->body(),
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'method' => $c['method'],
                    'path'   => $c['path'],
                    'query'  => $c['query'],
                    'ok'     => false,
                    'error'  => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'status'     => 'success',
            'proxy_url'  => $proxyUrl,
            'candidates' => $results,
        ]);
    }

}

