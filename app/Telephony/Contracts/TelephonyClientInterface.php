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

    // ── Admin / Dashboard API methods ──

    /**
     * Get list of agents with statuses (online/offline/pause/ringing/speaking).
     * GET /agents
     */
    public function getAgentsList(string $adminToken, int $limit = 50, int $skip = 0): array;

    /**
     * Get agent detail with KPIs (total calls, inbound, outbound, missed,
     * avg talk time, avg ring time, satisfaction, occupancy).
     * GET /agents/{username}/stats
     */
    public function getAgentDetail(string $adminToken, string $username): array;

    /**
     * Get call history — paginated, filterable by agent/date/direction.
     * GET /calls/history
     */
    public function getCallHistory(string $adminToken, array $filters = []): array;

    /**
     * Fetch live call history for the authenticated agent.
     * GET /agents/channels/calls
     */
    public function getAgentCallHistory(string $agentToken, int $limit = 50, int $skip = 0): array;

    /**
     * Get wallboard live statistics (active calls, agents, queues).
     * GET /stats/live
     */
    public function getWallboardLive(string $adminToken): array;

    /**
     * Get queue list with current stats.
     * GET /queues
     */
    public function getQueues(string $adminToken): array;

    /**
     * Get recordings for a specific call.
     * GET /calls/{callId}/recording
     */
    public function getCallRecording(string $adminToken, string $callId): array;

    /**
     * Read the live agent presence from /profile -> liveInfo.status.
     * @return array{result?:bool, agent_status?:string, message?:string}
     */
    public function getAgentLiveStatus(string $agentToken): array;

    /**
     * Change agent presence via the ZIWO proxy.
     * PUT /agents/status with body { number: int, comment: string }.
     * Status number map (probed live):
     *   1 = Available, 2 = On Break, 3 = Meeting, 4 = Outgoing
     */
    public function setAgentStatus(string $agentToken, string $username, string $status): array;
}
