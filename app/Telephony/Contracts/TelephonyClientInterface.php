<?php

namespace App\Telephony\Contracts;

interface TelephonyClientInterface
{
    /**
     * Authenticate agent with ZIWO APIs and retrieve access token.
     */
    public function login(string $username, string $password): array;

    /**
     * Trigger outbound click-to-call.
     */
    public function clickToCall(string $agentToken, string $customerNumber): array;

    /**
     * Hold active call.
     */
    public function holdCall(string $agentToken, string $callId): array;

    /**
     * Resume held call.
     */
    public function resumeCall(string $agentToken, string $callId): array;

    /**
     * Mute agent audio on active call.
     */
    public function muteCall(string $agentToken, string $callId): array;

    /**
     * Unmute agent audio on active call.
     */
    public function unmuteCall(string $agentToken, string $callId): array;

    /**
     * Hang up/terminate active call.
     */
    public function hangupCall(string $agentToken, string $callId): array;

    /**
     * Transfer active call to another agent or phone number.
     * Type can be 'blind' or 'warm' (attended).
     */
    public function transferCall(string $agentToken, string $callId, string $targetNumber, string $type = 'blind'): array;

    /**
     * Pause or resume call recording.
     */
    public function toggleRecording(string $agentToken, string $callId, bool $pause): array;

    /**
     * Get live status details of a specific call.
     */
    public function getCallStatus(string $agentToken, string $callId): array;

    /**
     * Retrieve current API health status.
     */
    public function getHealthStatus(): bool;
}
