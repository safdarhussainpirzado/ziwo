<?php

namespace App\Telephony\Events;

use App\Models\TelephonyCall;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TelephonyCall $call
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [new Channel('telephony')];
        
        if ($this->call->agent_id) {
            $channels[] = new Channel("telephony.agent.{$this->call->agent_id}");
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'call.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->call->id,
            'call_id' => $this->call->call_id,
            'call_uuid' => $this->call->call_uuid,
            'agent_id' => $this->call->agent_id,
            'caller_number' => $this->call->caller_number,
            'direction' => $this->call->direction,
            'status' => $this->call->status,
            'recording_url' => $this->call->recording_url,
            'start_time' => $this->call->start_time?->toDateTimeString(),
            'end_time' => $this->call->end_time?->toDateTimeString(),
            'duration_seconds' => $this->call->duration_seconds,
            'metadata' => $this->call->metadata,
            'updated_at' => $this->call->updated_at?->toDateTimeString(),
        ];
    }
}
