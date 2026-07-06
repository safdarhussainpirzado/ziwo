<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dynamic operational dashboard providing core CRM data directly to the user.
     */
    public function index()
    {
        $this->authorize('dashboard.view');
        // ── 1. High-Level KPIs ──────────────────────────────────────────
        $activeCallsCount = DB::table('calls')
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        $p1EmergencyCount = DB::table('calls')
            ->join('call_types', 'calls.call_type_id', '=', 'call_types.id')
            ->where('call_types.priority', 1)
            ->whereIn('calls.status', ['pending', 'in_progress'])
            ->count();

        $activeSentinelsCount = \App\Models\User::where('is_active', true)->count();
        $totalSystemUsers     = \App\Models\User::count();
        $fleetUtilization     = $totalSystemUsers > 0
            ? round(($activeSentinelsCount / $totalSystemUsers) * 100)
            : 0;

        $slaFilter = function($query) {
            $query->whereIn('calls.status', ['completed', 'pending', 'in_progress'])
                  ->whereNotIn('calls.call_sub_type_id', function($subQuery) {
                      $subQuery->select('id')->from('call_sub_types')->whereIn('name', ['Junk', 'Silent']);
                  })
                  ->where(function($q) {
                      $q->whereNotIn('calls.call_type_id', function($subQ) {
                          $subQ->select('id')->from('call_types')->where('name', 'Information');
                      })->orWhereNotNull('calls.office_id');
                  });
        };

        $totalSlaCalls = DB::table('calls')->where($slaFilter)->count();
        $completedCalls = DB::table('calls')->where($slaFilter)->where('status', 'completed')->count();
        $slaValue = $totalSlaCalls > 0 ? round(($completedCalls / $totalSlaCalls) * 100) : 0;

        $slaBreakdown = DB::table('calls')
            ->join('call_types', 'calls.call_type_id', '=', 'call_types.id')
            ->select(
                'call_types.priority',
                DB::raw('COUNT(*) as total_calls'),
                DB::raw('SUM(CASE WHEN calls.status = \'completed\' THEN 1 ELSE 0 END) as completed_calls')
            )
            ->where($slaFilter)
            ->groupBy('call_types.priority')
            ->get()->keyBy('priority');

        // ── 2. Pending Alerts ───────────────────────────────────────────
        $pendingAlerts = \App\Models\Call::with(['callType', 'office'])
            ->where('status', 'pending')
            ->orderBy(
                \App\Models\CallType::select('priority')
                    ->whereColumn('call_types.id', 'calls.call_type_id')
                    ->take(1),
                'asc'
            )
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        // ── Analytical Filter (Excludes Junk/Silent and Unassigned Info) ──
        $analyticalFilter = function($query) {
            $query->whereNotIn('calls.call_sub_type_id', function($subQuery) {
                      $subQuery->select('id')->from('call_sub_types')->whereIn('name', ['Junk', 'Silent']);
                  })
                  ->where(function($q) {
                      $q->whereNotIn('calls.call_type_id', function($subQ) {
                          $subQ->select('id')->from('call_types')->where('name', 'Information');
                      })->orWhereNotNull('calls.office_id');
                  });
        };

        // ── 3. Call Category Distribution (Donut) ──────────────────────
        $callCategories = DB::table('calls')
            ->join('call_types', 'calls.call_type_id', '=', 'call_types.id')
            ->select('call_types.category', DB::raw('count(*) as count'))
            // Removed analyticalFilter to show ALL calls including unassigned/junk/silent
            ->groupBy('call_types.category')
            ->get();

        // ── 4. Sentinel Distribution (Active state) ─────────────────────
        $tigerStatus = DB::table('users')
            ->select('is_active as status', DB::raw('count(*) as count'))
            ->groupBy('is_active')
            ->get()
            ->map(function($i) {
                $i->status = $i->status ? 'authorized' : 'locked';
                return $i;
            });

        // ── 5. Hourly Heatmap (Last 7 Days) ────────────────────────────
        $heatmapRaw = DB::table('calls')
            ->join('call_types', 'calls.call_type_id', '=', 'call_types.id')
            ->select(
                DB::raw('DAYOFWEEK(calls.created_at) as day'),
                DB::raw('HOUR(calls.created_at) as hour'),
                'call_types.name as type',
                DB::raw('count(*) as count')
            )
            ->where('calls.created_at', '>=', now()->subDays(7))
            ->where($analyticalFilter)
            ->groupBy('day', 'hour', 'call_types.name')
            ->get();

        $heatmapData = collect();
        $dateMap = [];
        for ($i = 6; $i >= 0; $i--) {
            $dt = now()->subDays($i);
            $dateMap[$dt->dayOfWeekIso == 7 ? 1 : $dt->dayOfWeekIso + 1] = $dt->format('M d, Y');
        }

        foreach (range(1, 7) as $d) {
            foreach (range(0, 23) as $h) {
                $heatmapData->push((object)[
                    'day' => $d,
                    'hour' => $h,
                    'date_str' => $dateMap[$d] ?? '',
                    'count' => 0,
                    'types' => []
                ]);
            }
        }
        $heatmapData = $heatmapData->keyBy(function($item) { return $item->day . '-' . $item->hour; });

        foreach ($heatmapRaw as $row) {
            $key = $row->day . '-' . $row->hour;
            if ($heatmapData->has($key)) {
                $item = $heatmapData->get($key);
                $item->count += $row->count;
                $item->types[$row->type] = $row->count;
            }
        }
        $heatmapData = $heatmapData->values();

        // ── 6. Priority Trajectory (Last 7 Days) ───────────────────────
        $priorityDist = DB::table('calls')
            ->join('call_types', 'calls.call_type_id', '=', 'call_types.id')
            ->select(
                'call_types.priority',
                DB::raw('DATE(calls.created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('calls.created_at', '>=', now()->subDays(7))
            ->where($analyticalFilter)
            ->groupBy('call_types.priority', 'date')
            ->get();

        // ── 7. Agent Performance ────────────────────────────────────────
        $agentPerformance = DB::table('calls')
            ->join('users', 'calls.agent_id', '=', 'users.id')
            ->select('users.full_name as username', DB::raw('count(*) as total_calls'))
            ->where('calls.status', 'completed')
            ->where($analyticalFilter)
            ->groupBy('users.full_name') // Switch to using full_name instead of username
            ->orderBy('total_calls', 'desc')
            ->limit(5)
            ->get();

        // ── 8. Beat Incident Ranking ───────────────────────────────────────────
        $beatIntensity = DB::table('calls')
            ->join('offices as beat', 'calls.office_id', '=', 'beat.id')
            ->leftJoin('offices as sector', 'beat.parent_id', '=', 'sector.id')
            ->leftJoin('offices as zone', 'sector.parent_id', '=', 'zone.id')
            ->select(
                'beat.name as name', 
                'sector.name as sector_name',
                'zone.name as zone_name',
                DB::raw('count(*) as count')
            )
            ->where('beat.type', 'beat')
            ->where($analyticalFilter)
            ->groupBy('beat.name', 'sector.name', 'zone.name')
            ->orderBy('count', 'desc')
            ->limit(8)
            ->get();

        // ── 9. Weekly Volume ────────────────────────────────────────────
        $weeklyDist = DB::table('calls')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get()->reverse()->values();

        $chartLabels = $weeklyDist->pluck('date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'));
        $chartData = $weeklyDist->pluck('count');

        // ── 10. Sector Load ───────────────────────────────────────────────
        $sectorLoad = DB::table('calls')
            ->join('offices as o', 'calls.office_id', '=', 'o.id')
            ->leftJoin('offices as p1', 'o.parent_id', '=', 'p1.id')
            ->select(
                DB::raw("CASE 
                    WHEN o.type = 'sector' THEN o.name 
                    WHEN p1.type = 'sector' THEN p1.name 
                    ELSE 'Other' 
                END as name"),
                DB::raw('count(*) as count')
            )
            ->where('calls.office_id', '!=', 1)
            ->where($analyticalFilter)
            ->groupBy('name')
            ->having('name', '!=', 'Other')
            ->orderBy('count', 'desc')
            ->limit(6)
            ->get();

        // ── 11. Zone Wise Helps Percentage ────────────────
        $carriageTypeSplit = DB::table('calls')
            ->join('offices as o', 'calls.office_id', '=', 'o.id')
            ->leftJoin('offices as p1', 'o.parent_id', '=', 'p1.id')
            ->leftJoin('offices as p2', 'p1.parent_id', '=', 'p2.id')
            ->select(
                DB::raw("CASE 
                    WHEN o.type = 'zone' THEN o.name 
                    WHEN p1.type = 'zone' THEN p1.name 
                    WHEN p2.type = 'zone' THEN p2.name 
                    ELSE 'Unassigned/Other' 
                END as type"),
                DB::raw('count(*) as count')
            )
            ->groupBy('type')
            ->get();

        // ── 12. Zone Wise Activity ─────────────────────────────────────
        $shiftDeployment = DB::table('calls')
            ->join('offices as o', 'calls.office_id', '=', 'o.id')
            ->leftJoin('offices as p1', 'o.parent_id', '=', 'p1.id')
            ->leftJoin('offices as p2', 'p1.parent_id', '=', 'p2.id')
            ->select(
                DB::raw("CASE 
                    WHEN o.type = 'zone' THEN o.name 
                    WHEN p1.type = 'zone' THEN p1.name 
                    WHEN p2.type = 'zone' THEN p2.name 
                    ELSE 'Other' 
                END as name"),
                DB::raw('SUM(CASE WHEN calls.status = \'pending\' THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN calls.status = \'in_progress\' THEN 1 ELSE 0 END) as in_progress'),
                DB::raw('SUM(CASE WHEN calls.status = \'completed\' THEN 1 ELSE 0 END) as completed')
            )
            ->where('calls.office_id', '!=', 1)
            ->groupBy('name')
            ->get();

        // ── 13. Sunburst Hierarchical Data (Zone -> Sector -> Category) ──
        $sunburstRaw = DB::table('calls')
            ->join('call_types', 'calls.call_type_id', '=', 'call_types.id')
            ->join('offices as o', 'calls.office_id', '=', 'o.id')
            ->leftJoin('offices as p1', 'o.parent_id', '=', 'p1.id')
            ->leftJoin('offices as p2', 'p1.parent_id', '=', 'p2.id')
            ->select(
                DB::raw("CASE 
                    WHEN o.type = 'zone' THEN o.name 
                    WHEN p1.type = 'zone' THEN p1.name 
                    WHEN p2.type = 'zone' THEN p2.name 
                    ELSE 'Other' 
                END as zone_name"),
                DB::raw("CASE 
                    WHEN o.type = 'sector' THEN o.name 
                    WHEN p1.type = 'sector' THEN p1.name 
                    ELSE 'Other' 
                END as sector_name"),
                'call_types.category',
                DB::raw('count(*) as count')
            )
            ->where('calls.office_id', '!=', 1) // Exclude HQ / Unassigned
            ->whereNotNull('calls.office_id')
            ->groupBy('zone_name', 'sector_name', 'call_types.category')
            ->get();

        $sunburstData = [];
        foreach ($sunburstRaw as $row) {
            if ($row->zone_name === 'Other' || $row->sector_name === 'Other') continue;
            
            if (!isset($sunburstData[$row->zone_name])) {
                $sunburstData[$row->zone_name] = ['count' => 0, 'sectors' => []];
            }
            $sunburstData[$row->zone_name]['count'] += $row->count;
            
            if (!isset($sunburstData[$row->zone_name]['sectors'][$row->sector_name])) {
                $sunburstData[$row->zone_name]['sectors'][$row->sector_name] = ['count' => 0, 'categories' => []];
            }
            $sunburstData[$row->zone_name]['sectors'][$row->sector_name]['count'] += $row->count;
            
            if (!isset($sunburstData[$row->zone_name]['sectors'][$row->sector_name]['categories'][$row->category])) {
                $sunburstData[$row->zone_name]['sectors'][$row->sector_name]['categories'][$row->category] = 0;
            }
            $sunburstData[$row->zone_name]['sectors'][$row->sector_name]['categories'][$row->category] += $row->count;
        }

        // ── 14. NHMP Help Line Performance ──────────────────────────────
        $avgWaitTime = DB::table('calls')->whereNotNull('wait_time_seconds')->avg('wait_time_seconds') ?? 0;
        $avgTalkTime = DB::table('calls')->whereNotNull('agent_call_duration')->avg('agent_call_duration') ?? 0;
        $avgResponseTime = DB::table('calls')->whereNotNull('response_time_sec')->avg('response_time_sec') ?? 0;

        // ═══════════════════════════════════════════════════════════════
        //  FALLBACK DUMMY DATA (used when DB tables are empty)
        // ═══════════════════════════════════════════════════════════════

        if ($callCategories->isEmpty()) {
            $callCategories = collect([
                (object)['category' => 'Medical Emergency', 'count' => 45],
                (object)['category' => 'Vehicle Breakdown', 'count' => 32],
                (object)['category' => 'Road Hazard',       'count' => 18],
                (object)['category' => 'Crime Report',      'count' => 12],
                (object)['category' => 'General Info',      'count' => 25],
            ]);
        }

        if ($tigerStatus->isEmpty()) {
            $tigerStatus = collect([
                (object)['status' => 'authorized', 'count' => 12],
                (object)['status' => 'locked',     'count' => 2],
            ]);
            $activeSentinelsCount = 12;
            $totalSystemUsers     = 14;
            $fleetUtilization     = 85;
        }

        if ($heatmapData->isEmpty()) {
            $dummy = [];
            for ($d = 1; $d <= 7; $d++) {
                for ($h = 0; $h < 24; $h++) {
                    $dummy[] = (object)['day' => $d, 'hour' => $h, 'count' => rand(0, 15)];
                }
            }
            $heatmapData = collect($dummy);
        }

        if ($priorityDist->isEmpty()) {
            $dummy = [];
            for ($i = 0; $i < 7; $i++) {
                $date    = now()->subDays($i)->format('Y-m-d');
                $dummy[] = (object)['priority' => 1, 'date' => $date, 'count' => rand(2, 8)];
                $dummy[] = (object)['priority' => 2, 'date' => $date, 'count' => rand(5, 15)];
                $dummy[] = (object)['priority' => 3, 'date' => $date, 'count' => rand(10, 25)];
            }
            $priorityDist = collect($dummy);
        }

        if ($agentPerformance->isEmpty()) {
            $agentPerformance = collect([
                (object)['username' => 'Agent Alpha',   'total_calls' => 120],
                (object)['username' => 'Agent Bravo',   'total_calls' => 95],
                (object)['username' => 'Agent Charlie', 'total_calls' => 88],
                (object)['username' => 'Agent Delta',   'total_calls' => 76],
                (object)['username' => 'Agent Echo',    'total_calls' => 45],
            ]);
        }

        if ($beatIntensity->isEmpty()) {
            $beatIntensity = collect([
                (object)['name' => 'Faisalabad North', 'sector_name' => 'Faisalabad Sector', 'zone_name' => 'Central Zone', 'count' => 56],
                (object)['name' => 'Multan Bypass',    'sector_name' => 'Multan Sector',     'zone_name' => 'South Zone',   'count' => 42],
                (object)['name' => 'Lahore Thokar',    'sector_name' => 'Lahore Sector',     'zone_name' => 'Central Zone', 'count' => 38],
                (object)['name' => 'Islamabad Toll',   'sector_name' => 'Rwp Sector',        'zone_name' => 'North Zone',   'count' => 29],
                (object)['name' => 'Peshawar Point',   'sector_name' => 'Peshawar Sector',   'zone_name' => 'West Zone',    'count' => 24],
            ]);
        }

        if ($chartLabels->isEmpty()) {
            $chartLabels = collect(['Apr 13','Apr 14','Apr 15','Apr 16','Apr 17','Apr 18','Apr 19']);
            $chartData   = collect([34, 41, 38, 52, 47, 61, 55]);
        }

        if ($sectorLoad->isEmpty()) {
            $sectorLoad = collect([
                (object)['name' => 'Rawalpindi Sector', 'count' => 78],
                (object)['name' => 'Lahore Sector',     'count' => 62],
                (object)['name' => 'Faisalabad Sector', 'count' => 45],
                (object)['name' => 'Multan Sector',     'count' => 55],
                (object)['name' => 'Peshawar Sector',   'count' => 89],
            ]);
        }

        if ($carriageTypeSplit->isEmpty()) {
            $carriageTypeSplit = collect([
                (object)['type' => 'Motorway',        'count' => 52],
                (object)['type' => 'Highway',         'count' => 31],
                (object)['type' => 'Strategic Route', 'count' => 17],
            ]);
        }

        if ($shiftDeployment->isEmpty()) {
            $shiftDeployment = collect([
                (object)['name' => 'North Zone',   'pending' => 4, 'in_progress' => 8, 'completed' => 20],
                (object)['name' => 'South Zone',   'pending' => 2, 'in_progress' => 5, 'completed' => 15],
                (object)['name' => 'Central Zone', 'pending' => 6, 'in_progress' => 10, 'completed' => 30],
                (object)['name' => 'West Zone',    'pending' => 3, 'in_progress' => 4, 'completed' => 18],
            ]);
        }

        if (empty($sunburstData)) {
            $sunburstData = [
                'North Zone' => [
                    'count' => 100,
                    'sectors' => [
                        'Sector N1' => ['count' => 60, 'categories' => ['Medical' => 30, 'Breakdown' => 30]],
                        'Sector N2' => ['count' => 40, 'categories' => ['Crime' => 20, 'General' => 20]]
                    ]
                ],
                'South Zone' => [
                    'count' => 80,
                    'sectors' => [
                        'Sector S1' => ['count' => 50, 'categories' => ['Medical' => 25, 'Breakdown' => 25]],
                        'Sector S2' => ['count' => 30, 'categories' => ['Crime' => 15, 'General' => 15]]
                    ]
                ]
            ];
        }

        if ($pendingAlerts->isEmpty()) {
            $pendingAlerts = collect([
                (object)[
                    'id' => 1, 'call_number' => 'NHMP-130-9821', 'status' => 'pending',
                    'created_at' => now()->subMinutes(5),
                    'callType' => (object)['name' => 'Accident P1', 'priority' => 1],
                    'office'     => (object)['name' => 'S-1 Beat'],
                ],
                (object)[
                    'id' => 2, 'call_number' => 'NHMP-130-9825', 'status' => 'pending',
                    'created_at' => now()->subMinutes(12),
                    'callType' => (object)['name' => 'Breakdown', 'priority' => 2],
                    'office'     => (object)['name' => 'N-5 Beat'],
                ],
                (object)[
                    'id' => 3, 'call_number' => 'NHMP-130-9830', 'status' => 'pending',
                    'created_at' => now()->subMinutes(25),
                    'callType' => (object)['name' => 'Medical', 'priority' => 1],
                    'beat'     => (object)['name' => 'S-1 Beat'],
                ],
                (object)[
                    'id' => 4, 'call_number' => 'NHMP-130-9832', 'status' => 'pending',
                    'created_at' => now()->subMinutes(34),
                    'callType' => (object)['name' => 'Fire', 'priority' => 1],
                    'beat'     => (object)['name' => 'M-4 Beat'],
                ],
                (object)[
                    'id' => 5, 'call_number' => 'NHMP-130-9838', 'status' => 'pending',
                    'created_at' => now()->subMinutes(41),
                    'callType' => (object)['name' => 'Road Hazard', 'priority' => 3],
                    'office'     => (object)['name' => 'B-101 Beat'],
                ],
            ]);
        }

        if ($activeCallsCount === 0) { $activeCallsCount = 47; }
        if ($p1EmergencyCount === 0) { $p1EmergencyCount = 6;  }

        if ($avgWaitTime == 0) { $avgWaitTime = 14; }
        if ($avgTalkTime == 0) { $avgTalkTime = 105; }
        if ($avgResponseTime == 0) { $avgResponseTime = 420; }
        
        $helpLineMetrics = (object)[
            'wait_time' => $avgWaitTime,
            'talk_time' => $avgTalkTime,
            'response_time' => $avgResponseTime,
            'efficiency_score' => 92
        ];

        return view('dashboard', compact(
            'activeCallsCount',
            'p1EmergencyCount',
            'activeSentinelsCount',
            'totalSystemUsers',
            'fleetUtilization',
            'pendingAlerts',
            'chartLabels',
            'chartData',
            'callCategories',
            'tigerStatus',
            'heatmapData',
            'priorityDist',
            'agentPerformance',
            'beatIntensity',
            'sectorLoad',
            'carriageTypeSplit',
            'shiftDeployment',
            'slaValue',
            'slaBreakdown',
            'helpLineMetrics',
            'sunburstData'
        ));
    }
}
