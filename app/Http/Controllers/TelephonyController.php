<?php

namespace App\Http\Controllers;

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
        protected TelephonyRepositoryInterface $repository
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
            $result['is_mock'] = (bool)config('services.ziwo.mock', false);
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
            'is_mock'          => (bool)config('services.ziwo.mock', false),
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
     * Returns real queue data from the ZIWO API so the softphone transfer
     * panel shows live queue names instead of hardcoded mock data.
     */
    public function getQueues(Request $request)
    {
        $userId = auth()->id();
        $config = $this->repository->getAgentConfig($userId);

        if (!$config || empty($config->ziwo_token)) {
            return response()->json(['status' => 'error', 'queues' => [], 'message' => 'Agent not authenticated']);
        }

        try {
            $isMock = (bool) config('services.ziwo.mock', false);

            if ($isMock) {
                // Return structured mock queues in production-compatible format
                return response()->json([
                    'status' => 'success',
                    'queues' => [
                        ['id' => '3001', 'name' => 'Support',   'number' => '3001', 'agents' => 5, 'waiting' => 2],
                        ['id' => '3002', 'name' => 'Sales',     'number' => '3002', 'agents' => 3, 'waiting' => 0],
                        ['id' => '3003', 'name' => 'Dispatch',  'number' => '3003', 'agents' => 4, 'waiting' => 1],
                        ['id' => '3004', 'name' => 'Billing',   'number' => '3004', 'agents' => 2, 'waiting' => 3],
                    ],
                    'is_mock' => true,
                ]);
            }

            // ── Live ZIWO API: GET /queues ──
            // ZIWO API endpoint for queues list uses the agent's access_token header.
            $proxyUrl = rtrim(config('services.ziwo.proxy_url', 'https://nayatel-api.aswat.co'), '/');
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Accept'       => 'application/json',
                'access_token' => $config->ziwo_token,
            ])->timeout(8)->get("{$proxyUrl}/queues");

            if ($response->successful()) {
                $body = $response->json();
                // ZIWO returns: { result: true, content: [ { id, name, queueNumber, ... } ] }
                $rawQueues = $body['content'] ?? $body['queues'] ?? $body['data'] ?? [];
                $queues = collect($rawQueues)->map(function ($q) {
                    return [
                        'id'      => $q['id'] ?? $q['queueNumber'] ?? $q['number'] ?? '',
                        'name'    => $q['name'] ?? $q['label'] ?? 'Queue',
                        'number'  => $q['queueNumber'] ?? $q['number'] ?? $q['id'] ?? '',
                        'agents'  => $q['agentCount'] ?? $q['agents'] ?? 0,
                        'waiting' => $q['waitingCount'] ?? $q['waiting'] ?? 0,
                    ];
                })->values()->all();

                return response()->json(['status' => 'success', 'queues' => $queues, 'is_mock' => false]);
            }

            \Illuminate\Support\Facades\Log::warning("ZIWO queues API returned [{$response->status()}]: {$response->body()}");
            return response()->json(['status' => 'success', 'queues' => [], 'is_mock' => false, 'note' => 'No queues returned from gateway']);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("ZIWO getQueues exception: {$e->getMessage()}");
            return response()->json(['status' => 'error', 'queues' => [], 'message' => 'Failed to fetch queues'], 500);
        }
    }

    /**
     * Fetch the list of active ZIWO teammates (agents) for the authenticated agent.
     * Returns real teammate agent records from ZIWO API.
     */
    public function getTeammates(Request $request)
    {
        $userId = auth()->id();
        $config = $this->repository->getAgentConfig($userId);

        if (!$config || empty($config->ziwo_token)) {
            return response()->json(['status' => 'error', 'teammates' => [], 'message' => 'Agent not authenticated']);
        }

        try {
            $isMock = (bool) config('services.ziwo.mock', false);

            if ($isMock) {
                return response()->json([
                    'status' => 'success',
                    'teammates' => [
                        ['id' => 1, 'name' => 'Safdar Hussain',  'ext' => '101', 'status' => 'online',  'number' => '+921000000101'],
                        ['id' => 2, 'name' => 'Ahmed Raza',       'ext' => '102', 'status' => 'online',  'number' => '+921000000102'],
                        ['id' => 3, 'name' => 'Sara Khan',        'ext' => '103', 'status' => 'busy',    'number' => '+921000000103'],
                        ['id' => 4, 'name' => 'John Carter',      'ext' => '104', 'status' => 'offline', 'number' => '+921000000104'],
                        ['id' => 5, 'name' => 'Maria Lopez',      'ext' => '105', 'status' => 'online',  'number' => '+921000000105'],
                    ],
                    'is_mock' => true,
                ]);
            }

            // ── Live ZIWO API: GET /admin/users ──
            $proxyUrl = rtrim(config('services.ziwo.proxy_url', 'https://nayatel-api.aswat.co'), '/');
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Accept'       => 'application/json',
                'access_token' => $config->ziwo_token,
            ])->timeout(8)->get("{$proxyUrl}/admin/users");

            if ($response->successful()) {
                $body = $response->json();
                $rawUsers = $body['content'] ?? $body['users'] ?? $body['data'] ?? [];
                
                $teammates = collect($rawUsers)->map(function ($u, $idx) {
                    $firstName = $u['firstName'] ?? '';
                    $lastName = $u['lastName'] ?? '';
                    $fullName = trim($firstName . ' ' . $lastName);
                    if (empty($fullName)) {
                        $fullName = $u['username'] ?? $u['email'] ?? 'Agent';
                    }
                    
                    // ZIWO agent status mapping
                    $rawStatus = strtolower($u['status'] ?? $u['ccStatus'] ?? 'offline');
                    $status = 'offline';
                    if (in_array($rawStatus, ['online', 'available', 'active', 'ready'])) {
                        $status = 'online';
                    } elseif (in_array($rawStatus, ['busy', 'speaking', 'oncall', 'on call'])) {
                        $status = 'busy';
                    }

                    // Extract first available number (contactNumber or extension)
                    $ext = $u['contactNumber'] ?? $u['ccLogin'] ?? $u['extension'] ?? '';
                    if (empty($ext)) {
                        $ext = $u['username'] ?? '';
                    }
                    
                    return [
                        'id'     => $u['id'] ?? $idx + 1,
                        'name'   => $fullName,
                        'ext'    => $ext,
                        'status' => $status,
                        'number' => $ext, // dialable number
                    ];
                })->filter(function ($t) {
                    return !empty($t['ext']);
                })->values()->all();

                return response()->json(['status' => 'success', 'teammates' => $teammates, 'is_mock' => false]);
            }

            \Illuminate\Support\Facades\Log::warning("ZIWO admin/users API returned [{$response->status()}]: {$response->body()}");
            return response()->json(['status' => 'success', 'teammates' => [], 'is_mock' => false, 'note' => 'No teammates returned from gateway']);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("ZIWO getTeammates exception: {$e->getMessage()}");
            return response()->json(['status' => 'error', 'teammates' => [], 'message' => 'Failed to fetch teammates'], 500);
        }
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

