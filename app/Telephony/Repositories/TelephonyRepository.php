<?php

namespace App\Telephony\Repositories;

use App\Telephony\Contracts\TelephonyRepositoryInterface;
use App\Telephony\DTOs\AgentSessionDTO;
use App\Telephony\DTOs\CallLogDTO;
use App\Telephony\DTOs\ContactDTO;
use App\Models\TelephonyAgentConfig;
use App\Models\TelephonyCall;
use App\Models\TelephonyWebhookLog;
use App\Models\PhonebookContact;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TelephonyRepository implements TelephonyRepositoryInterface
{
    public function saveAgentConfig(int $userId, AgentSessionDTO $dto): TelephonyAgentConfig
    {
        return TelephonyAgentConfig::updateOrCreate(
            ['user_id' => $userId],
            [
                'ziwo_username' => $dto->ziwoUsername,
                'ziwo_token' => $dto->ziwoToken,
                'expires_at' => $dto->expiresAt,
                'agent_status' => $dto->agentStatus,
                'last_status_change_at' => $dto->lastStatusChangeAt ?? now(),
            ]
        );
    }

    public function getAgentConfig(int $userId): ?TelephonyAgentConfig
    {
        return TelephonyAgentConfig::where('user_id', $userId)->first();
    }

    public function updateAgentStatus(int $userId, string $status): bool
    {
        return (bool)TelephonyAgentConfig::where('user_id', $userId)->update([
            'agent_status' => $status,
            'last_status_change_at' => now(),
        ]);
    }

    public function logCall(CallLogDTO $dto): TelephonyCall
    {
        $data = [
            'caller_number' => $dto->callerNumber,
            'direction' => $dto->direction,
            'status' => $dto->status,
            'recording_url' => $dto->recordingUrl,
            'duration_seconds' => $dto->durationSeconds,
        ];

        if ($dto->agentId) {
            $data['agent_id'] = $dto->agentId;
        }

        if ($dto->startTime) {
            $data['start_time'] = $dto->startTime;
        }

        if ($dto->endTime) {
            $data['end_time'] = $dto->endTime;
        }

        if (!empty($dto->metadata)) {
            // Merge existing metadata if record already exists
            $existing = null;
            if ($dto->callId) {
                $existing = TelephonyCall::where('call_id', $dto->callId)->first();
            } elseif ($dto->callUuid) {
                $existing = TelephonyCall::where('call_uuid', $dto->callUuid)->first();
            }

            if ($existing && $existing->metadata) {
                $data['metadata'] = array_merge($existing->metadata, $dto->metadata);
            } else {
                $data['metadata'] = $dto->metadata;
            }
        }

        // Construct unique lookup attributes
        $attributes = [];
        if ($dto->callId) {
            $attributes['call_id'] = $dto->callId;
        }
        if ($dto->callUuid) {
            $attributes['call_uuid'] = $dto->callUuid;
        }

        // Fallback to searching by caller number + agent if ID/UUID not set
        if (empty($attributes)) {
            return TelephonyCall::create(array_merge($data, [
                'call_id' => 'temp_' . rand(100000, 999999),
                'call_uuid' => 'temp-uuid-' . uuid_create(),
            ]));
        }

        return TelephonyCall::updateOrCreate($attributes, $data);
    }

    public function getCallByZiwoId(string $callId): ?TelephonyCall
    {
        return TelephonyCall::where('call_id', $callId)->first();
    }

    public function getCallByZiwoUuid(string $callUuid): ?TelephonyCall
    {
        return TelephonyCall::where('call_uuid', $callUuid)->first();
    }

    public function logWebhook(string $eventType, array $payload, bool $processed = false, ?string $errorMessage = null): TelephonyWebhookLog
    {
        return TelephonyWebhookLog::create([
            'event_type' => $eventType,
            'payload' => $payload,
            'processed' => $processed,
            'error_message' => $errorMessage,
        ]);
    }

    public function saveContact(ContactDTO $dto, ?int $id = null): PhonebookContact
    {
        $data = [
            'name' => $dto->name,
            'phone_number' => $dto->phoneNumber,
            'category' => $dto->category,
            'created_by' => $dto->createdBy,
            'is_favorite' => $dto->isFavorite,
            'metadata' => $dto->metadata,
        ];

        if ($id) {
            $contact = PhonebookContact::findOrFail($id);
            $contact->update($data);
            return $contact;
        }

        return PhonebookContact::create($data);
    }

    public function deleteContact(int $id): bool
    {
        $contact = PhonebookContact::find($id);
        if ($contact) {
            return $contact->delete();
        }
        return false;
    }

    public function searchContacts(string $query, ?string $category = null, ?int $userId = null): Collection
    {
        return PhonebookContact::query()
            ->when(!empty($query), function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name', 'like', "%{$query}%")
                        ->orWhere('phone_number', 'like', "%{$query}%");
                });
            })
            ->when(!empty($category), function ($q) use ($category) {
                $q->where('category', $category);
            })
            ->when($userId !== null, function ($q) use ($userId) {
                // Return public contacts or custom contacts created by this user
                $q->where(function ($sub) use ($userId) {
                    $sub->whereNull('created_by')
                        ->orWhere('created_by', $userId);
                });
            })
            ->orderBy('is_favorite', 'desc')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getCallStats(Carbon $start, Carbon $end): array
    {
        $stats = TelephonyCall::query()
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('COUNT(*) as total_calls'),
                DB::raw('SUM(case when status = "finished" then 1 else 0 end) as completed_calls'),
                DB::raw('SUM(case when status = "missed" then 1 else 0 end) as missed_calls'),
                DB::raw('SUM(case when direction = "inbound" then 1 else 0 end) as inbound_calls'),
                DB::raw('SUM(case when direction = "outbound" then 1 else 0 end) as outbound_calls'),
                DB::raw('AVG(duration_seconds) as avg_duration'),
                DB::raw('AVG(CASE WHEN direction = "inbound" THEN duration_seconds ELSE NULL END) as avg_talk_time')
            )
            ->first()
            ->toArray();

        // Round average values
        $stats['avg_duration'] = round($stats['avg_duration'] ?? 0);
        $stats['avg_talk_time'] = round($stats['avg_talk_time'] ?? 0);

        // Fetch hourly distribution
        $stats['hourly_distribution'] = TelephonyCall::query()
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->all();

        return $stats;
    }

    public function getWebhookLogs(int $limit = 50): Collection
    {
        return TelephonyWebhookLog::latest()->limit($limit)->get();
    }

    public function getLiveAgentStatuses(): Collection
    {
        return TelephonyAgentConfig::with('user:id,full_name,username')
            ->get()
            ->map(function ($config) {
                return [
                    'agent_name' => $config->user->full_name ?? $config->ziwo_username,
                    'username' => $config->user->username ?? '',
                    'status' => $config->agent_status,
                    'last_change' => $config->last_status_change_at?->diffForHumans() ?? 'N/A',
                ];
            });
    }

    public function getActiveCallsCount(): int
    {
        return TelephonyCall::whereIn('status', ['ringing', 'active', 'held'])->count();
    }

    public function getRecentCalls(int $userId, int $limit = 50): Collection
    {
        return TelephonyCall::where('agent_id', $userId)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($call) {
                return [
                    'id' => $call->id,
                    'caller_number' => $call->caller_number,
                    'direction' => $call->direction,
                    'status' => $call->status,
                    'time_ago' => $call->created_at?->diffForHumans() ?? 'N/A',
                ];
            });
    }

    public function getActiveCallForAgent(int $userId): ?TelephonyCall
    {
        return TelephonyCall::where('agent_id', $userId)
            ->whereIn('status', ['ringing', 'active', 'held'])
            ->latest()
            ->first();
    }
}

