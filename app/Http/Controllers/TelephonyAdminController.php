<?php

namespace App\Http\Controllers;

use App\Telephony\Contracts\TelephonyServiceInterface;
use App\Telephony\Contracts\TelephonyRepositoryInterface;
use App\Models\TelephonyCall;
use Illuminate\Http\Request;

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
}
