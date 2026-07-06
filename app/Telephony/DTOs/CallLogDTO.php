<?php

namespace App\Telephony\DTOs;

use Carbon\Carbon;

class CallLogDTO
{
    public function __construct(
        public ?string $callId,
        public ?string $callUuid,
        public ?int $agentId,
        public string $callerNumber,
        public string $direction, // inbound, outbound
        public string $status, // ringing, active, held, finished, missed
        public ?string $recordingUrl = null,
        public ?Carbon $startTime = null,
        public ?Carbon $endTime = null,
        public int $durationSeconds = 0,
        public array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            callId: $data['call_id'] ?? null,
            callUuid: $data['call_uuid'] ?? null,
            agentId: $data['agent_id'] ?? null,
            callerNumber: $data['caller_number'],
            direction: $data['direction'] ?? 'inbound',
            status: $data['status'] ?? 'ringing',
            recordingUrl: $data['recording_url'] ?? null,
            startTime: isset($data['start_time']) ? Carbon::parse($data['start_time']) : null,
            endTime: isset($data['end_time']) ? Carbon::parse($data['end_time']) : null,
            durationSeconds: (int)($data['duration_seconds'] ?? 0),
            metadata: $data['metadata'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'call_id' => $this->callId,
            'call_uuid' => $this->callUuid,
            'agent_id' => $this->agentId,
            'caller_number' => $this->callerNumber,
            'direction' => $this->direction,
            'status' => $this->status,
            'recording_url' => $this->recordingUrl,
            'start_time' => $this->startTime?->toDateTimeString(),
            'end_time' => $this->endTime?->toDateTimeString(),
            'duration_seconds' => $this->durationSeconds,
            'metadata' => $this->metadata,
        ];
    }
}
