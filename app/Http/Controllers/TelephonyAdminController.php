<?php

namespace App\Http\Controllers;

use App\Telephony\Contracts\TelephonyServiceInterface;
use App\Telephony\Contracts\TelephonyRepositoryInterface;
use App\Models\TelephonyCall;
use App\Models\TelephonyAgentConfig;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TelephonyAdminController extends Controller
{
    public function __construct(
        protected TelephonyServiceInterface $service,
        protected TelephonyRepositoryInterface $repository
    ) {}

    /**
     * Display the Telephony Admin Dashboard.
     */
    public function index()
    {
        // Enforce permissions
        if (!auth()->user()->hasPermission('super_admin') && !auth()->user()->hasPermission('dashboard.view')) {
            abort(403, 'Unauthorized access to telephony administrative panel.');
        }

        $analytics = $this->service->getDashboardAnalytics();
        
        // Fetch call logs with pagination for the table view
        $callLogs = TelephonyCall::with('agent:id,full_name,username')
            ->latest()
            ->paginate(15);

        $webhookLogs = $this->repository->getWebhookLogs(15);

        return view('telephony.dashboard', compact('analytics', 'callLogs', 'webhookLogs'));
    }

    /**
     * Endpoint to fetch live polling updates for live dashboard stats.
     */
    public function getLiveStats()
    {
        if (!auth()->user()->hasPermission('super_admin') && !auth()->user()->hasPermission('dashboard.view')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $analytics = $this->service->getDashboardAnalytics();
        return response()->json([
            'status' => 'success',
            'live_agents' => $analytics['live_agents'],
            'active_calls_count' => $analytics['active_calls_count'],
            'total_calls' => $analytics['total_calls'] ?? 0,
            'completed_calls' => $analytics['completed_calls'] ?? 0,
            'missed_calls' => $analytics['missed_calls'] ?? 0,
            'sla_percentage' => $analytics['sla_percentage'] ?? 100,
        ]);
    }

    /**
     * GET /ziwo/dashboard — Render the Ziwo-style dashboard page.
     */
    public function dashboard()
    {
        $analytics = $this->service->getDashboardAnalytics();
        $callLogs = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        $webhookLogs = collect([]);
        return view('telephony.dashboard', compact('analytics', 'callLogs', 'webhookLogs'));
    }

    /**
     * GET /ziwo/statistics — Render the statistics page with agent KPIs, call history & queues.
     */
    public function statistics()
    {
        $agentsResponse = $this->service->adminGetAgents();
        $callHistoryData = $this->service->adminGetCallHistory(['limit' => 50]);
        $queuesResponse = $this->service->adminGetQueues();

        $agents = $agentsResponse['agents'] ?? [];
        $callHistory = $callHistoryData['calls'] ?? [];
        $queues = $queuesResponse['queues'] ?? [];

        // Aggregate stats
        $totalCalls = $callHistoryData['total'] ?? count($callHistory);
        $totalDuration = collect($callHistory)->sum('duration_sec');
        $sla = 100; // placeholder; refine when real SLA data available

        return view('telephony.statistics', compact(
            'agents', 'callHistory', 'queues', 'totalCalls', 'totalDuration', 'sla'
        ));
    }

    // ── Admin Dashboard Proxy API (reads from Aswat/Ziwo proxy) ──

    /**
     * GET /mgmt/telephony/agents — List agents with statuses.
     */
    public function getAgents()
    {
        if (!auth()->user()->hasPermission('super_admin') && !auth()->user()->hasPermission('dashboard.view')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($this->service->adminGetAgents());
    }

    /**
     * GET /mgmt/telephony/agents/{username} — Agent detail with KPIs.
     */
    public function getAgentDetail(string $username)
    {
        if (!auth()->user()->hasPermission('super_admin') && !auth()->user()->hasPermission('dashboard.view')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($this->service->adminGetAgentDetail($username));
    }

    /**
     * GET /mgmt/telephony/calls/history — Call history (paginated, filterable).
     */
    public function getCallHistory(Request $request)
    {
        if (!auth()->user()->hasPermission('super_admin') && !auth()->user()->hasPermission('dashboard.view')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($this->service->adminGetCallHistory($request->only([
            'agent', 'direction', 'status', 'date_from', 'date_to', 'page', 'per_page', 'limit', 'skip',
        ])));
    }

    /**
     * GET /mgmt/telephony/wallboard — Live wallboard stats.
     */
    public function getWallboard()
    {
        if (!auth()->user()->hasPermission('super_admin') && !auth()->user()->hasPermission('dashboard.view')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($this->service->adminGetWallboardLive());
    }

    /**
     * GET /mgmt/telephony/queues — Queue stats.
     */
    public function getQueues()
    {
        if (!auth()->user()->hasPermission('super_admin') && !auth()->user()->hasPermission('dashboard.view')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($this->service->adminGetQueues());
    }

    /**
     * GET /mgmt/telephony/recordings/{callId} — Call recording metadata.
     */
    public function getCallRecording(string $callId)
    {
        if (!auth()->user()->hasPermission('super_admin') && !auth()->user()->hasPermission('dashboard.view')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($this->service->adminGetCallRecording($callId));
    }
}
