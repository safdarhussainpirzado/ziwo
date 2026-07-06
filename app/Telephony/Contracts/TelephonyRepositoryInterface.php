<?php

namespace App\Telephony\Contracts;

use App\Telephony\DTOs\AgentSessionDTO;
use App\Telephony\DTOs\CallLogDTO;
use App\Telephony\DTOs\ContactDTO;
use App\Models\TelephonyAgentConfig;
use App\Models\TelephonyCall;
use App\Models\TelephonyWebhookLog;
use App\Models\PhonebookContact;
use Illuminate\Support\Collection;
use Carbon\Carbon;

interface TelephonyRepositoryInterface
{
    public function saveAgentConfig(int $userId, AgentSessionDTO $dto): TelephonyAgentConfig;
    
    public function getAgentConfig(int $userId): ?TelephonyAgentConfig;
    
    public function updateAgentStatus(int $userId, string $status): bool;
    
    public function logCall(CallLogDTO $dto): TelephonyCall;
    
    public function getCallByZiwoId(string $callId): ?TelephonyCall;
    
    public function getCallByZiwoUuid(string $callUuid): ?TelephonyCall;
    
    public function logWebhook(string $eventType, array $payload, bool $processed = false, ?string $errorMessage = null): TelephonyWebhookLog;
    
    public function saveContact(ContactDTO $dto, ?int $id = null): PhonebookContact;
    
    public function deleteContact(int $id): bool;
    
    public function searchContacts(string $query, ?string $category = null, ?int $userId = null): Collection;
    
    public function getCallStats(Carbon $start, Carbon $end): array;
    
    public function getWebhookLogs(int $limit = 50): Collection;

    public function getLiveAgentStatuses(): Collection;

    public function getActiveCallsCount(): int;

    public function getRecentCalls(int $userId, int $limit = 50): Collection;
}
