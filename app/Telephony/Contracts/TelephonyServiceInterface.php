<?php

namespace App\Telephony\Contracts;

interface TelephonyServiceInterface
{
    /**
     * Authenticate user with ZIWO and save session tokens locally.
     */
    public function authenticateAgent(int $userId, string $username, string $password): array;

    /**
     * Clear agent session and mark as offline.
     */
    public function disconnectAgent(int $userId): bool;

    /**
     * Start outbound click-to-call or dialpad call.
     */
    public function dialOutbound(int $userId, string $phoneNumber): array;

    /**
     * Put an active call on hold.
     */
    public function hold(int $userId, string $callId): array;

    /**
     * Resume a held call.
     */
    public function resume(int $userId, string $callId): array;

    /**
     * Mute microphone during a call.
     */
    public function mute(int $userId, string $callId): array;

    /**
     * Unmute microphone during a call.
     */
    public function unmute(int $userId, string $callId): array;

    /**
     * End active call.
     */
    public function hangup(int $userId, string $callId): array;

    /**
     * Transfer call to another extension/number.
    public function transfer(int $userId, string $callId, string $targetNumber, string $type = 'blind'): array;

    /**
     * Merge call or initiate a conference call.
     */
    public function conference(int $userId, string $callId, string $targetNumber, ?string $roomId = null, ?string $action = null): array;

    /**
     * Pause or resume active call recording.
     */
    public function toggleCallRecording(int $userId, string $callId, bool $pause): array;

    /**
     * Process ZIWO webhook call events.
     */
    public function processWebhookEvent(string $eventType, array $payload): void;

    /**
     * Get live and historical metrics for the admin dashboard.
     */
    public function getDashboardAnalytics(): array;
}
