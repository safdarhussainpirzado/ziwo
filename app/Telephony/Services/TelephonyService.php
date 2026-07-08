<?php

namespace App\Telephony\Services;

use App\Telephony\Contracts\TelephonyClientInterface;
use App\Telephony\Contracts\TelephonyRepositoryInterface;
use App\Telephony\Contracts\TelephonyServiceInterface;
use App\Telephony\DTOs\AgentSessionDTO;
use App\Telephony\DTOs\CallLogDTO;
use App\Telephony\Events\AgentStatusChanged;
use App\Telephony\Events\CallStatusUpdated;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class TelephonyService implements TelephonyServiceInterface
{
    public function __construct(
        protected TelephonyClientInterface $client,
        protected TelephonyRepositoryInterface $repository
    ) {}

    public function authenticateAgent(int $userId, string $username, string $password): array
    {
        try {
            $user = User::findOrFail($userId);
            $response = $this->client->login($username, $password);

            if (($response['status'] ?? '') === 'success' && !empty($response['access_token'])) {
                $expiresIn = $response['expires_in'] ?? 3600;
                $dto = new AgentSessionDTO(
                    userId: $userId,
                    ziwoUsername: $username,
                    ziwoToken: $response['access_token'],
                    expiresAt: now()->addSeconds($expiresIn),
                    agentStatus: 'online',
                    lastStatusChangeAt: now()
                );

                $config = $this->repository->saveAgentConfig($userId, $dto);

                // Broadcast agent status
                broadcast(new AgentStatusChanged($user, 'online'))->toOthers();

                return [
                    'status'       => 'success',
                    'access_token' => $response['access_token'], // for frontend SDK init
                    'token'        => $response['access_token'],
                    'ziwo_username' => $username,
                    'agent_status' => 'online',
                    'message'      => 'Authenticated successfully with telephony gateway'
                ];
            }

            throw new Exception($response['message'] ?? 'Authentication failed');
        } catch (Exception $e) {
            Log::error("TelephonyService Auth Error for User ID {$userId}: {$e->getMessage()}");
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function disconnectAgent(int $userId): bool
    {
        $user = User::find($userId);
        $config = $this->repository->getAgentConfig($userId);
        
        if ($config) {
            $dto = new AgentSessionDTO(
                userId: $userId,
                ziwoUsername: $config->ziwo_username,
                ziwoToken: null,
                expiresAt: null,
                agentStatus: 'offline',
                lastStatusChangeAt: now()
            );

            $this->repository->saveAgentConfig($userId, $dto);

            if ($user) {
                broadcast(new AgentStatusChanged($user, 'offline'))->toOthers();
            }

            return true;
        }

        return false;
    }

    public function dialOutbound(int $userId, string $phoneNumber): array
    {
        $config = $this->repository->getAgentConfig($userId);
        if (!$config || empty($config->ziwo_token)) {
            return ['status' => 'error', 'message' => 'Agent is not authenticated with telephony gateway'];
        }

        try {
            $response = $this->client->clickToCall($config->ziwo_token, $phoneNumber);

            if (($response['status'] ?? '') === 'success') {
                $callId = $response['call_id'] ?? null;
                $callUuid = $response['call_uuid'] ?? null;

                $dto = new CallLogDTO(
                    callId: $callId,
                    callUuid: $callUuid,
                    agentId: $userId,
                    callerNumber: $phoneNumber,
                    direction: 'outbound',
                    status: 'ringing',
                    startTime: now(),
                    metadata: ['initiated_via' => 'crm_dialer']
                );

                $call = $this->repository->logCall($dto);

                broadcast(new CallStatusUpdated($call))->toOthers();

                return [
                    'status' => 'success',
                    'call_id' => $callId,
                    'call_uuid' => $callUuid,
                    'message' => 'Outbound call initiated'
                ];
            }

            throw new Exception($response['message'] ?? 'Unable to dial phone number');
        } catch (Exception $e) {
            Log::error("TelephonyService Outbound Dial Error for User ID {$userId}: {$e->getMessage()}");
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function hold(int $userId, string $callId): array
    {
        return $this->executeCallAction($userId, $callId, 'holdCall', 'held', ['is_held' => true]);
    }

    public function resume(int $userId, string $callId): array
    {
        return $this->executeCallAction($userId, $callId, 'resumeCall', 'active', ['is_held' => false]);
    }

    public function mute(int $userId, string $callId): array
    {
        return $this->executeCallAction($userId, $callId, 'muteCall', 'active', ['is_muted' => true]);
    }

    public function unmute(int $userId, string $callId): array
    {
        return $this->executeCallAction($userId, $callId, 'unmuteCall', 'active', ['is_muted' => false]);
    }

    public function hangup(int $userId, string $callId): array
    {
        return $this->executeCallAction($userId, $callId, 'hangupCall', 'finished', [], true);
    }

    public function transfer(int $userId, string $callId, string $targetNumber, string $type = 'blind'): array
    {
        $config = $this->repository->getAgentConfig($userId);
        if (!$config || empty($config->ziwo_token)) {
            return ['status' => 'error', 'message' => 'Agent not authenticated'];
        }

        try {
            $response = $this->client->transferCall($config->ziwo_token, $callId, $targetNumber, $type);

            if (($response['status'] ?? '') === 'success') {
                $call = $this->repository->getCallByZiwoId($callId);
                if ($call) {
                    $dto = new CallLogDTO(
                        callId: $callId,
                        callUuid: $call->call_uuid,
                        agentId: $userId,
                        callerNumber: $call->caller_number,
                        direction: $call->direction,
                        status: 'transferred',
                        metadata: ['transfer_target' => $targetNumber, 'transfer_type' => $type]
                    );
                    $updatedCall = $this->repository->logCall($dto);
                    broadcast(new CallStatusUpdated($updatedCall))->toOthers();
                }

                return ['status' => 'success', 'message' => 'Call transfer initiated'];
            }

            throw new Exception($response['message'] ?? 'Call transfer request failed');
        } catch (Exception $e) {
            Log::error("TelephonyService Call Transfer Error: {$e->getMessage()}");
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function conference(int $userId, string $callId, string $targetNumber): array
    {
        $config = $this->repository->getAgentConfig($userId);
        if (!$config || empty($config->ziwo_token)) {
            return ['status' => 'error', 'message' => 'Agent not authenticated'];
        }

        try {
            $response = $this->client->conferenceCall($config->ziwo_token, $callId, $targetNumber);

            if (($response['status'] ?? '') === 'success') {
                $call = $this->repository->getCallByZiwoId($callId);
                if ($call) {
                    $dto = new CallLogDTO(
                        callId: $callId,
                        callUuid: $call->call_uuid,
                        agentId: $userId,
                        callerNumber: $call->caller_number,
                        direction: $call->direction,
                        status: 'conference',
                        metadata: array_merge($call->metadata ?? [], ['conference_participants' => [$targetNumber]])
                    );
                    $updatedCall = $this->repository->logCall($dto);
                    broadcast(new CallStatusUpdated($updatedCall))->toOthers();
                }

                return ['status' => 'success', 'message' => 'Conference call initiated'];
            }

            throw new Exception($response['message'] ?? 'Conference request failed');
        } catch (Exception $e) {
            Log::error("TelephonyService Conference Error: {$e->getMessage()}");
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function toggleCallRecording(int $userId, string $callId, bool $pause): array
    {
        $action = $pause ? 'Pause recording' : 'Resume recording';
        return $this->executeCallAction($userId, $callId, 'toggleRecording', 'active', ['recording_paused' => $pause], false, [$pause]);
    }

    public function processWebhookEvent(string $eventType, array $payload): void
    {
        try {
            // Log webhook to DB first (Audit-friendly)
            $webhookLog = $this->repository->logWebhook($eventType, $payload);

            $callId = $payload['call_id'] ?? null;
            $callUuid = $payload['call_uuid'] ?? null;
            $agentUsername = $payload['agent_username'] ?? null;
            $callerNumber = $payload['caller_number'] ?? $payload['phone'] ?? null;
            $direction = $payload['direction'] ?? 'inbound';
            $duration = (int)($payload['duration'] ?? 0);
            $recordingUrl = $payload['recording_url'] ?? null;

            if (empty($callId) && empty($callUuid)) {
                throw new Exception("Webhook payload missing unique call indicators (call_id/call_uuid)");
            }

            // Identify Agent
            $agentId = null;
            if ($agentUsername) {
                $agentConfig = \App\Models\TelephonyAgentConfig::where('ziwo_username', $agentUsername)->first();
                if ($agentConfig) {
                    $agentId = $agentConfig->user_id;
                }
            }

            // Route call state
            $status = 'ringing';
            $metadata = [];
            $endTime = null;
            $startTime = null;

            switch ($eventType) {
                case 'call.ringing':
                    $status = 'ringing';
                    $startTime = now();
                    // Attempt CRM lookup if number is present and agent token is available
                    if ($callerNumber && $agentId) {
                        $config = $this->repository->getAgentConfig($agentId);
                        if ($config && !empty($config->ziwo_token)) {
                            try {
                                $crmData = $this->client->lookupCrmCustomer($config->ziwo_token, $callerNumber);
                                if (!empty($crmData['result']) && !empty($crmData['content'][0])) {
                                    $cust = $crmData['content'][0];
                                    $firstName = $cust['firstName'] ?? '';
                                    $lastName = $cust['lastName'] ?? '';
                                    $fullName = trim($firstName . ' ' . $lastName);
                                    if ($fullName !== '') {
                                        $metadata['caller_name'] = $fullName;
                                    }
                                }
                            } catch (Exception $crmEx) {
                                Log::warning("CRM customer lookup during webhook processing failed: " . $crmEx->getMessage());
                            }
                        }
                    }
                    break;
                case 'call.answered':
                    $status = 'active';
                    $startTime = now();
                    if ($agentId) {
                        $this->repository->updateAgentStatus($agentId, 'speaking');
                        $agent = User::find($agentId);
                        if ($agent) broadcast(new AgentStatusChanged($agent, 'speaking'))->toOthers();
                    }
                    break;
                case 'call.held':
                    $status = 'held';
                    $metadata['is_held'] = true;
                    break;
                case 'call.unheld':
                    $status = 'active';
                    $metadata['is_held'] = false;
                    break;
                case 'call.hangup':
                case 'call.finished':
                    $status = 'finished';
                    $endTime = now();
                    if ($agentId) {
                        $this->repository->updateAgentStatus($agentId, 'online');
                        $agent = User::find($agentId);
                        if ($agent) broadcast(new AgentStatusChanged($agent, 'online'))->toOthers();
                    }
                    break;
                case 'call.missed':
                    $status = 'missed';
                    $endTime = now();
                    if ($agentId) {
                        $this->repository->updateAgentStatus($agentId, 'online');
                        $agent = User::find($agentId);
                        if ($agent) broadcast(new AgentStatusChanged($agent, 'online'))->toOthers();
                    }
                    break;
            }

            // Format DTO
            $dto = new CallLogDTO(
                callId: $callId,
                callUuid: $callUuid,
                agentId: $agentId,
                callerNumber: $callerNumber ?? 'Unknown',
                direction: $direction,
                status: $status,
                recordingUrl: $recordingUrl,
                startTime: $startTime,
                endTime: $endTime,
                durationSeconds: $duration,
                metadata: $metadata
            );

            $call = $this->repository->logCall($dto);

            // Broadcast real-time call update
            broadcast(new CallStatusUpdated($call))->toOthers();

            // Mark webhook as processed
            $webhookLog->update(['processed' => true]);

        } catch (Exception $e) {
            Log::error("TelephonyService Webhook Processing Error: {$e->getMessage()}");
            if (isset($webhookLog)) {
                $webhookLog->update([
                    'processed' => false,
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }

    public function getDashboardAnalytics(): array
    {
        $start = now()->startOfDay();
        $end = now()->endOfDay();

        $stats = $this->repository->getCallStats($start, $end);
        
        // Calculate SLA compliance
        // SLA formula: Calls answered within 20 seconds. If not tracked, we fallback to completed/total ratio
        $total = $stats['total_calls'];
        $completed = $stats['completed_calls'];
        $stats['sla_percentage'] = $total > 0 ? round(($completed / $total) * 100) : 100;

        $stats['live_agents'] = $this->repository->getLiveAgentStatuses();
        $stats['active_calls_count'] = $this->repository->getActiveCallsCount();

        return $stats;
    }

    /**
     * Internal helper to wrap common call actions (hold, resume, mute, etc.).
     */
    protected function executeCallAction(
        int $userId,
        string $callId,
        string $clientMethod,
        string $targetStatus,
        array $metadataUpdate = [],
        bool $setEndTime = false,
        array $additionalParams = []
    ): array {
        $config = $this->repository->getAgentConfig($userId);
        if (!$config || empty($config->ziwo_token)) {
            return ['status' => 'error', 'message' => 'Agent not authenticated with telephony gateway'];
        }

        try {
            $params = array_merge([$config->ziwo_token, $callId], $additionalParams);
            $response = call_user_func_array([$this->client, $clientMethod], $params);

            if (($response['status'] ?? '') === 'success') {
                $call = $this->repository->getCallByZiwoId($callId);
                
                if ($call) {
                    $endTime = $setEndTime ? now() : $call->end_time;
                    $duration = 0;
                    if ($setEndTime && $call->start_time) {
                        $duration = max(0, now()->diffInSeconds($call->start_time));
                    } elseif ($call->duration_seconds) {
                        $duration = $call->duration_seconds;
                    }

                    $dto = new CallLogDTO(
                        callId: $callId,
                        callUuid: $call->call_uuid,
                        agentId: $userId,
                        callerNumber: $call->caller_number,
                        direction: $call->direction,
                        status: $targetStatus,
                        recordingUrl: $call->recording_url,
                        startTime: $call->start_time,
                        endTime: $endTime,
                        durationSeconds: $duration,
                        metadata: $metadataUpdate
                    );

                    $updatedCall = $this->repository->logCall($dto);

                    broadcast(new CallStatusUpdated($updatedCall))->toOthers();
                }

                return ['status' => 'success', 'message' => 'Telephony action completed successfully'];
            }

            throw new Exception($response['message'] ?? "Telephony API call {$clientMethod} failed");
        } catch (Exception $e) {
            Log::error("TelephonyService Call Action [{$clientMethod}] Error for User ID {$userId}: {$e->getMessage()}");
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
