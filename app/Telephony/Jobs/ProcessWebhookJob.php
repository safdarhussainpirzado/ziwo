<?php

namespace App\Telephony\Jobs;

use App\Telephony\Contracts\TelephonyServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $eventType,
        protected array $payload
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TelephonyServiceInterface $telephonyService): void
    {
        try {
            $telephonyService->processWebhookEvent($this->eventType, $this->payload);
        } catch (\Exception $e) {
            Log::error("ProcessWebhookJob Failed for event [{$this->eventType}]: {$e->getMessage()}");
            throw $e;
        }
    }
}
