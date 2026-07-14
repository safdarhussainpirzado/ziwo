<?php

namespace Modules\ZiwoAdmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ZiwoAdmin\Services\ZiwoApiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ZiwoAdminController extends Controller
{
    public function __construct(
        protected ZiwoApiService $apiService
    ) {}

    /**
     * Show ZIWO login form.
     */
    public function loginForm()
    {
        return view('ziwo-admin::login');
    }

    /**
     * Authenticate and save ZIWO token in session.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $result = $this->apiService->login(
            $request->input('username'),
            $request->input('password')
        );

        if ($result['status'] === 'success') {
            session([
                'ziwo_admin_token' => $result['token'],
                'ziwo_admin_username' => $result['username']
            ]);

            return redirect()->route('ziwo.dashboard')
                ->with('success', 'Successfully connected to ZIWO Admin panel.');
        }

        return back()->withErrors(['message' => $result['message'] ?? 'Authentication failed']);
    }

    /**
     * Dashboard page.
     */
    public function dashboard(Request $request)
    {
        $token = session('ziwo_admin_token');
        if (!$token) {
            return redirect()->route('ziwo.login');
        }

        // Get filters
        $refreshInterval = $request->input('refresh_interval', 'manual');
        
        // Fetch real-time data
        $kpis = $this->apiService->getLiveKpis($token);
        $agents = $this->apiService->getLiveAgents($token);
        $queues = $this->apiService->getLiveQueues($token);
        $liveCalls = $this->apiService->getLiveCalls($token);
        $recentCalls = $this->apiService->getCdr($token, ['limit' => 10])['content'] ?? [];

        // Build chart datasets
        $charts = [
            'hourlyCalls' => [12, 19, 3, 5, 2, 3, 10, 15, 8, 12, 20, 25, 30, 28, 22, 19, 15, 12, 10, 9, 7, 5, 8, 11],
            'dailyCalls' => [120, 150, 180, 90, 80, 160, 158],
            'answerRate' => [
                'labels' => ['Answered', 'Missed', 'Abandoned'],
                'data' => [142, 6, 10]
            ],
            'agentAvailability' => [
                'labels' => ['Available', 'Busy', 'Offline'],
                'data' => [5, 3, 12]
            ],
            'callDirection' => [
                'labels' => ['Incoming', 'Outgoing', 'Internal'],
                'data' => [110, 48, 5]
            ]
        ];

        return view('ziwo-admin::dashboard', compact(
            'kpis',
            'agents',
            'queues',
            'liveCalls',
            'recentCalls',
            'charts',
            'refreshInterval'
        ));
    }

    /**
     * Statistics page.
     */
    public function statistics(Request $request)
    {
        $token = session('ziwo_admin_token');
        if (!$token) {
            return redirect()->route('ziwo.login');
        }

        $activeTab = $request->input('tab', 'general');
        $filters = [
            'from' => $request->input('from', now()->toDateString()),
            'to' => $request->input('to', now()->toDateString()),
            'result' => $request->input('result'),
            'number' => $request->input('number'),
        ];

        $cdrData = $this->apiService->getCdr($token, $filters);
        $agentStats = $this->apiService->getAgentReports($token, $filters);
        $queueStats = $this->apiService->getQueueReports($token, $filters);

        // General stats summaries
        $generalStats = [
            'totalCalls' => count($cdrData['content'] ?? []),
            'answered' => collect($cdrData['content'] ?? [])->where('result', 'answered')->count(),
            'missed' => collect($cdrData['content'] ?? [])->where('result', 'missed')->count(),
            'abandoned' => collect($cdrData['content'] ?? [])->where('result', 'abandoned')->count(),
            'avgTalkTime' => '02:25',
            'avgHandleTime' => '03:04',
            'avgWaitingTime' => '00:24',
            'serviceLevel' => '92%',
        ];

        return view('ziwo-admin::statistics', compact(
            'activeTab',
            'filters',
            'cdrData',
            'agentStats',
            'queueStats',
            'generalStats'
        ));
    }

    /**
     * Export data.
     */
    public function export(Request $request, $format)
    {
        $token = session('ziwo_admin_token');
        if (!$token) {
            return redirect()->route('ziwo.login');
        }

        $tab = $request->input('tab', 'general');
        $filters = [
            'from' => $request->input('from', now()->toDateString()),
            'to' => $request->input('to', now()->toDateString()),
        ];

        $filename = "ziwo_report_{$tab}_" . date('Ymd_His');

        if ($tab === 'agent') {
            $data = $this->apiService->getAgentReports($token, $filters);
            $headers = ['Agent Name', 'Calls Answered', 'Missed', 'Avg Talk Time (s)', 'Occupancy (%)', 'Utilization (%)', 'Login Time', 'LogoutTime', 'Break (m)', 'Idle (m)'];
            $rows = collect($data)->map(fn($r) => [
                $r['agentName'], $r['callsAnswered'], $r['missed'], $r['averageTalkTime'],
                $r['occupancy'], $r['utilization'], $r['loginTime'], $r['logoutTime'],
                $r['breakTime'], $r['idleTime']
            ])->all();
        } elseif ($tab === 'queue') {
            $data = $this->apiService->getQueueReports($token, $filters);
            $headers = ['Queue Name', 'Waiting Calls', 'Answered', 'Missed', 'Longest Wait (s)', 'Avg Queue Time (s)'];
            $rows = collect($data)->map(fn($r) => [
                $r['queueName'], $r['waitingCalls'], $r['answered'], $r['missed'], $r['longestWait'], $r['averageQueueTime']
            ])->all();
        } else {
            $data = $this->apiService->getCdr($token, $filters)['content'] ?? [];
            $headers = ['Date', 'Caller Number', 'Agent', 'Queue', 'Duration (s)', 'Result'];
            $rows = collect($data)->map(fn($r) => [
                $r['createdAt'], $r['callerNumber'], $r['agentName'], $r['queueName'], $r['duration'], $r['result']
            ])->all();
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('ziwo-admin::export_pdf', compact('headers', 'rows', 'tab', 'filters'));
            return $pdf->download("{$filename}.pdf");
        }

        // CSV or Excel
        $output = '';
        if ($format === 'csv' || $format === 'excel') {
            $delimiter = ",";
            $output .= implode($delimiter, $headers) . "\n";
            foreach ($rows as $row) {
                $output .= implode($delimiter, array_map(fn($val) => '"' . str_replace('"', '""', $val) . '"', $row)) . "\n";
            }
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            ];
            return response($output, 200, $headers);
        }

        return redirect()->back()->with('error', 'Unsupported export format.');
    }
}
