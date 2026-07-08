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
    public function transferCall(string $agentToken, string $callId, string $targetNumber, string $type = 'blind'): array;

    /**
     * Merge call or initiate a conference call with another target number.
     */
    public function conferenceCall(string $agentToken, string $callId, string $targetNumber): array;

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

    /**
     * Look up a CRM customer record by phone number.
     * Uses the ZIWO CRM API: GET /agent/crm/customers?phone={phone}
     */
    public function lookupCrmCustomer(string $agentToken, string $phone): array;
}
