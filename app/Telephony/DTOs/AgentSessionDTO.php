<?php

namespace App\Telephony\DTOs;

use Carbon\Carbon;

class AgentSessionDTO
{
    public function __construct(
        public int $userId,
        public ?string $ziwoUsername = null,
        public ?string $ziwoToken = null,
        public ?Carbon $expiresAt = null,
        public string $agentStatus = 'offline',
        public ?Carbon $lastStatusChangeAt = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            ziwoUsername: $data['ziwo_username'] ?? null,
            ziwoToken: $data['ziwo_token'] ?? null,
            expiresAt: isset($data['expires_at']) ? Carbon::parse($data['expires_at']) : null,
            agentStatus: $data['agent_status'] ?? 'offline',
            lastStatusChangeAt: isset($data['last_status_change_at']) ? Carbon::parse($data['last_status_change_at']) : null
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'ziwo_username' => $this->ziwoUsername,
            'ziwo_token' => $this->ziwoToken,
            'expires_at' => $this->expiresAt?->toDateTimeString(),
            'agent_status' => $this->agentStatus,
            'last_status_change_at' => $this->lastStatusChangeAt?->toDateTimeString(),
        ];
    }
}
