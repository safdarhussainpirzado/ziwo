@extends('layouts.app')

@section('title', 'ZIWO Statistics - NHMP 130')

@section('page-title', 'ZIWO Statistics & Reports')

@section('content')

<style>
    /* ZIWO Statistics Styles */
    .ziwo-header {
        background: linear-gradient(135deg, #1e1b4b 0%, #3730a3 50%, #4f46e5 100%);
        border-radius: 1rem; padding: 1.5rem 2rem; color: white;
        margin-bottom: 1.5rem; position: relative; overflow: hidden;
        box-shadow: 0 10px 40px rgba(67, 56, 202, 0.35);
    }
    .ziwo-header::before {
        content:''; position:absolute; top:-50%; right:-10%;
        width:300px; height:300px; border-radius:50%;
        background:rgba(255,255,255,0.05); pointer-events:none;
    }

    /* Tabs */
    .stat-tabs {
        display: flex; gap: 0; background: white;
        border-radius: 0.875rem; padding: 0.35rem;
        border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        flex-wrap: wrap; margin-bottom: 1.25rem;
    }
    .stat-tab {
        padding: 0.5rem 1rem; border-radius: 0.625rem;
        font-size: 0.78rem; font-weight: 600; color: #64748b;
        cursor: pointer; transition: all 0.18s; text-decoration: none;
        border: none; background: none; white-space: nowrap;
    }
    .stat-tab:hover { background: #f1f5f9; color: #334155; }
    .stat-tab.active {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: white; box-shadow: 0 3px 10px rgba(79,70,229,0.3);
    }

    /* Filter bar */
    .filter-bar {
        background: white; border-radius: 0.875rem; padding: 1rem 1.25rem;
        border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        display: flex; align-items: center; gap: 0.75rem;
        flex-wrap: wrap; margin-bottom: 1.25rem;
    }
    .filter-label { font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
    .filter-input, .filter-select {
        border: 1px solid #e2e8f0; border-radius: 0.5rem;
        padding: 0.4rem 0.75rem; font-size: 0.8rem; color: #334155;
        background: #f8fafc; outline: none; transition: border-color 0.15s;
    }
    .filter-input:focus, .filter-select:focus { border-color: #6366f1; background: white; }

    /* Summary cards for general tab */
    .summary-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0.875rem; margin-bottom: 1.25rem; }
    .summary-card {
        background: white; border-radius: 0.75rem; padding: 1rem 1.25rem;
        border: 1px solid #f1f5f9; box-shadow: 0 1px 6px rgba(0,0,0,0.04);
        text-align: center;
    }
    .summary-card .s-val { font-size: 1.6rem; font-weight: 800; color: #1e293b; }
    .summary-card .s-lbl { font-size: 0.68rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem; }

    /* Stat tables */
    .dash-card { background: white; border-radius: 0.875rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 12px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.25rem; }
    .dash-card-header { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid #f1f5f9; }
    .dash-card-title { font-size:0.85rem; font-weight:700; color:#1e293b; }

    .z-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .z-table thead tr { background: #f8fafc; }
    .z-table th { padding: 0.65rem 1rem; text-align: left; font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; }
    .z-table td { padding: 0.65rem 1rem; color: #334155; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .z-table tbody tr:hover { background: #f8fafc; }
    .z-table tbody tr:last-child td { border-bottom: none; }
    .z-table tfoot td { padding: 0.65rem 1rem; border-top: 2px solid #e2e8f0; font-weight: 700; color: #1e293b; background: #f8fafc; }

    /* Ranking row */
    .rank-badge {
        width: 24px; height: 24px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.72rem; font-weight: 800;
    }
    .rank-1 { background: linear-gradient(135deg,#f59e0b,#fbbf24); color: white; }
    .rank-2 { background: linear-gradient(135deg,#94a3b8,#cbd5e1); color: white; }
    .rank-3 { background: linear-gradient(135deg,#f97316,#fb923c); color: white; }
    .rank-other { background: #f1f5f9; color: #475569; }

    .badge { display:inline-flex; align-items:center; padding:2px 10px; border-radius:999px; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; }
    .badge-answered { background:#dcfce7; color:#166534; }
    .badge-missed { background:#fee2e2; color:#991b1b; }
    .badge-abandoned { background:#fef3c7; color:#92400e; }
    .badge-busy { background:#fce7f3; color:#9d174d; }
    .badge-noanswer { background:#e0e7ff; color:#3730a3; }

    /* Progress bar utility */
    .pbar { height:6px; background:#f1f5f9; border-radius:999px; overflow:hidden; }
    .pbar-fill { height:100%; border-radius:999px; }

    /* Export bar */
    .export-bar {
        display:flex; align-items:center; gap:0.5rem; margin-bottom:1.25rem; flex-wrap:wrap;
    }
    .export-btn {
        display:inline-flex; align-items:center; gap:5px;
        padding:0.4rem 0.875rem; border-radius:0.5rem;
        font-size:0.75rem; font-weight:600; cursor:pointer;
        transition:all 0.18s; text-decoration:none; border: 1px solid transparent;
    }
    .export-btn.csv { background:#dcfce7; color:#166534; border-color:#bbf7d0; }
    .export-btn.csv:hover { background:#bbf7d0; }
    .export-btn.pdf { background:#fee2e2; color:#991b1b; border-color:#fecaca; }
    .export-btn.pdf:hover { background:#fecaca; }
    .export-btn.excel { background:#dbeafe; color:#1e40af; border-color:#bfdbfe; }
    .export-btn.excel:hover { background:#bfdbfe; }
</style>

{{-- Header --}}
<div class="ziwo-header">
    <div style="position:relative;z-index:1;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="background:rgba(255,255,255,0.15);border-radius:0.625rem;padding:0.5rem 0.75rem;">
                    <i class="fa-solid fa-chart-bar" style="font-size:1.1rem;"></i>
                </div>
                <div>
                    <h1 style="font-size:1.3rem;font-weight:800;margin:0;letter-spacing:-0.02em;">ZIWO Statistics & Reports</h1>
                    <p style="font-size:0.78rem;opacity:0.8;margin:0;">Comprehensive contact center analytics</p>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <span style="font-size:0.78rem;opacity:0.85;background:rgba(255,255,255,0.12);border-radius:0.5rem;padding:4px 12px;">
                    {{ session('ziwo_admin_username', 'Admin') }}
                </span>
                <a href="{{ route('ziwo.dashboard') }}" style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.3);border-radius:0.5rem;padding:5px 14px;font-size:0.78rem;font-weight:600;text-decoration:none;">
                    <i class="fa-solid fa-tower-broadcast" style="margin-right:5px;"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Filter Form --}}
<form method="GET" action="{{ route('ziwo.statistics') }}" id="filter-form">
    <input type="hidden" name="tab" id="active-tab-input" value="{{ $activeTab }}">

    <div class="filter-bar">
        <span class="filter-label"><i class="fa-solid fa-filter"></i> Filters</span>
        <div>
            <span class="filter-label">From</span><br>
            <input type="date" name="from" class="filter-input" value="{{ $filters['from'] }}">
        </div>
        <div>
            <span class="filter-label">To</span><br>
            <input type="date" name="to" class="filter-input" value="{{ $filters['to'] }}">
        </div>
        <div>
            <span class="filter-label">Result</span><br>
            <select name="result" class="filter-select">
                <option value="">All Results</option>
                <option value="answered" {{ ($filters['result'] ?? '') === 'answered' ? 'selected' : '' }}>Answered</option>
                <option value="missed" {{ ($filters['result'] ?? '') === 'missed' ? 'selected' : '' }}>Missed</option>
                <option value="abandoned" {{ ($filters['result'] ?? '') === 'abandoned' ? 'selected' : '' }}>Abandoned</option>
                <option value="busy" {{ ($filters['result'] ?? '') === 'busy' ? 'selected' : '' }}>Busy</option>
                <option value="no-answer" {{ ($filters['result'] ?? '') === 'no-answer' ? 'selected' : '' }}>No Answer</option>
            </select>
        </div>
        <div>
            <span class="filter-label">Number</span><br>
            <input type="text" name="number" class="filter-input" placeholder="Search number..." value="{{ $filters['number'] ?? '' }}">
        </div>
        <button type="submit" style="align-self:flex-end;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:white;border:none;border-radius:0.5rem;padding:0.42rem 1.125rem;font-size:0.8rem;font-weight:700;cursor:pointer;box-shadow:0 3px 10px rgba(79,70,229,0.3);">
            <i class="fa-solid fa-magnifying-glass"></i> Apply
        </button>
        <a href="{{ route('ziwo.statistics') }}" style="align-self:flex-end;background:#f1f5f9;color:#475569;border-radius:0.5rem;padding:0.42rem 0.875rem;font-size:0.8rem;font-weight:600;text-decoration:none;">
            <i class="fa-solid fa-rotate-left"></i> Reset
        </a>

        {{-- Quick Ranges --}}
        <div style="margin-left:auto;display:flex;gap:0.4rem;align-self:flex-end;flex-wrap:wrap;">
            <button type="button" onclick="setRange('today')" class="export-btn excel">Today</button>
            <button type="button" onclick="setRange('yesterday')" class="export-btn excel">Yesterday</button>
            <button type="button" onclick="setRange('7days')" class="export-btn excel">Last 7D</button>
            <button type="button" onclick="setRange('30days')" class="export-btn excel">Last 30D</button>
        </div>
    </div>
</form>

{{-- Export Bar --}}
<div class="export-bar">
    <span style="font-size:0.78rem;font-weight:700;color:#64748b;margin-right:0.25rem;">Export:</span>
    <a href="{{ route('ziwo.export', 'csv') }}?tab={{ $activeTab }}&from={{ $filters['from'] }}&to={{ $filters['to'] }}" class="export-btn csv">
        <i class="fa-solid fa-file-csv"></i> CSV
    </a>
    <a href="{{ route('ziwo.export', 'pdf') }}?tab={{ $activeTab }}&from={{ $filters['from'] }}&to={{ $filters['to'] }}" class="export-btn pdf">
        <i class="fa-solid fa-file-pdf"></i> PDF
    </a>
</div>

{{-- Tabs --}}
<div class="stat-tabs">
    @foreach(['general' => 'General', 'cdr' => 'CDR / Calls', 'agent' => 'Agent Stats', 'queue' => 'Queue Stats', 'top-agents' => 'Top Agents'] as $tabKey => $tabLabel)
    <a href="{{ route('ziwo.statistics', array_merge(request()->query(), ['tab' => $tabKey])) }}"
       class="stat-tab {{ $activeTab === $tabKey ? 'active' : '' }}">
        {{ $tabLabel }}
    </a>
    @endforeach
</div>

{{-- ============================================================
     TAB: GENERAL
     ============================================================ --}}
@if($activeTab === 'general')

<div class="summary-grid">
    <div class="summary-card">
        <div class="s-val" style="color:#6366f1;">{{ $generalStats['totalCalls'] }}</div>
        <div class="s-lbl">Total Calls</div>
    </div>
    <div class="summary-card">
        <div class="s-val" style="color:#10b981;">{{ $generalStats['answered'] }}</div>
        <div class="s-lbl">Answered</div>
    </div>
    <div class="summary-card">
        <div class="s-val" style="color:#ef4444;">{{ $generalStats['missed'] }}</div>
        <div class="s-lbl">Missed</div>
    </div>
    <div class="summary-card">
        <div class="s-val" style="color:#f59e0b;">{{ $generalStats['abandoned'] }}</div>
        <div class="s-lbl">Abandoned</div>
    </div>
    <div class="summary-card">
        <div class="s-val" style="color:#6366f1;">{{ $generalStats['avgTalkTime'] }}</div>
        <div class="s-lbl">Avg Talk Time</div>
    </div>
    <div class="summary-card">
        <div class="s-val" style="color:#8b5cf6;">{{ $generalStats['avgHandleTime'] }}</div>
        <div class="s-lbl">Avg Handle</div>
    </div>
    <div class="summary-card">
        <div class="s-val" style="color:#0ea5e9;">{{ $generalStats['avgWaitingTime'] }}</div>
        <div class="s-lbl">Avg Wait</div>
    </div>
    <div class="summary-card">
        <div class="s-val" style="color:#059669;">{{ $generalStats['serviceLevel'] }}</div>
        <div class="s-lbl">Service Level</div>
    </div>
</div>

{{-- Trend line chart --}}
<div class="dash-card">
    <div class="dash-card-header">
        <span class="dash-card-title"><i class="fa-solid fa-chart-line" style="color:#6366f1;margin-right:6px;"></i>Call Volume Trend</span>
    </div>
    <div style="padding:1.25rem;">
        <canvas id="trendChart" height="100"></canvas>
    </div>
</div>

{{-- Peak Hours Heatmap --}}
<div class="dash-card">
    <div class="dash-card-header">
        <span class="dash-card-title"><i class="fa-solid fa-fire" style="color:#f97316;margin-right:6px;"></i>Peak Hours Distribution</span>
    </div>
    <div style="padding:1.25rem;">
        <div id="heatmap-container" style="overflow-x:auto;"></div>
    </div>
</div>

@endif

{{-- ============================================================
     TAB: CDR / CALLS
     ============================================================ --}}
@if($activeTab === 'cdr')

<div class="dash-card">
    <div class="dash-card-header">
        <span class="dash-card-title"><i class="fa-solid fa-list" style="color:#64748b;margin-right:6px;"></i>
            Call Detail Records ({{ $cdrData['info']['total'] ?? count($cdrData['content'] ?? []) }} records)
        </span>
    </div>
    <div>
        <table class="z-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date/Time</th>
                    <th>Caller Number</th>
                    <th>Agent</th>
                    <th>Queue</th>
                    <th>Duration</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cdrData['content'] ?? [] as $i => $call)
                <tr>
                    <td style="color:#94a3b8;font-size:0.75rem;">{{ $i + 1 }}</td>
                    <td style="font-family:monospace;font-size:0.78rem;color:#64748b;">{{ e($call['createdAt']) }}</td>
                    <td style="font-family:monospace;font-weight:600;color:#6366f1;">{{ e($call['callerNumber']) }}</td>
                    <td style="font-weight:600;">{{ e($call['agentName']) }}</td>
                    <td><span style="background:#f1f5f9;padding:2px 8px;border-radius:6px;font-size:0.72rem;font-weight:700;">{{ e($call['queueName']) }}</span></td>
                    <td style="font-family:monospace;">{{ gmdate("i:s", $call['duration']) }}</td>
                    <td>
                        @php $res = strtolower(str_replace('-','',$call['result'])); @endphp
                        <span class="badge badge-{{ $res }}">{{ e($call['result']) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:2.5rem;">No CDR records found for the selected filters</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif

{{-- ============================================================
     TAB: AGENT STATS
     ============================================================ --}}
@if($activeTab === 'agent')

<div class="dash-card">
    <div class="dash-card-header">
        <span class="dash-card-title"><i class="fa-solid fa-headset" style="color:#0ea5e9;margin-right:6px;"></i>Agent Performance Report</span>
    </div>
    <div>
        <table class="z-table">
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Calls Answered</th>
                    <th>Missed</th>
                    <th>Avg Talk Time</th>
                    <th>Occupancy</th>
                    <th>Utilization</th>
                    <th>Login</th>
                    <th>Logout</th>
                    <th>Break (m)</th>
                    <th>Idle (m)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agentStats as $ag)
                <tr>
                    <td style="font-weight:700;">{{ e($ag['agentName']) }}</td>
                    <td style="color:#16a34a;font-weight:700;">{{ $ag['callsAnswered'] }}</td>
                    <td style="color:#dc2626;font-weight:600;">{{ $ag['missed'] }}</td>
                    <td style="font-family:monospace;">{{ gmdate("i:s", $ag['averageTalkTime']) }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="pbar" style="width:70px;">
                                <div class="pbar-fill" style="width:{{ $ag['occupancy'] }}%;background:linear-gradient(90deg,#6366f1,#8b5cf6);"></div>
                            </div>
                            <span style="font-size:0.78rem;font-weight:700;color:#6366f1;">{{ $ag['occupancy'] }}%</span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="pbar" style="width:70px;">
                                <div class="pbar-fill" style="width:{{ $ag['utilization'] }}%;background:linear-gradient(90deg,#10b981,#34d399);"></div>
                            </div>
                            <span style="font-size:0.78rem;font-weight:700;color:#059669;">{{ $ag['utilization'] }}%</span>
                        </div>
                    </td>
                    <td style="font-family:monospace;font-size:0.78rem;">{{ e($ag['loginTime']) }}</td>
                    <td style="font-family:monospace;font-size:0.78rem;">{{ e($ag['logoutTime']) }}</td>
                    <td style="text-align:center;">{{ $ag['breakTime'] }}</td>
                    <td style="text-align:center;color:#94a3b8;">{{ $ag['idleTime'] }}</td>
                </tr>
                @empty
                <tr><td colspan="10" style="text-align:center;color:#94a3b8;padding:2.5rem;">No agent data for selected period</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif

{{-- ============================================================
     TAB: QUEUE STATS
     ============================================================ --}}
@if($activeTab === 'queue')

<div class="dash-card">
    <div class="dash-card-header">
        <span class="dash-card-title"><i class="fa-solid fa-layer-group" style="color:#f97316;margin-right:6px;"></i>Queue Performance Report</span>
    </div>
    <div>
        <table class="z-table">
            <thead>
                <tr>
                    <th>Queue</th>
                    <th>Waiting</th>
                    <th>Answered</th>
                    <th>Missed/Abandoned</th>
                    <th>Longest Wait</th>
                    <th>Avg Queue Time</th>
                    <th>Answer Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse($queueStats as $q)
                @php
                    $total = ($q['answered'] ?? 0) + ($q['missed'] ?? 0);
                    $pct = $total > 0 ? round(($q['answered'] / $total) * 100) : 0;
                @endphp
                <tr>
                    <td style="font-weight:700;">{{ e($q['queueName']) }}</td>
                    <td>
                        <span style="color: {{ ($q['waitingCalls'] ?? 0) > 0 ? '#ef4444' : '#10b981' }};font-weight:700;">{{ $q['waitingCalls'] ?? 0 }}</span>
                    </td>
                    <td style="color:#16a34a;font-weight:700;">{{ $q['answered'] ?? 0 }}</td>
                    <td style="color:#dc2626;font-weight:600;">{{ $q['missed'] ?? 0 }}</td>
                    <td style="font-family:monospace;">{{ gmdate("i:s", $q['longestWait'] ?? 0) }}</td>
                    <td style="font-family:monospace;">{{ gmdate("i:s", $q['averageQueueTime'] ?? 0) }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="pbar" style="width:80px;">
                                <div class="pbar-fill" style="width:{{ $pct }}%;background:linear-gradient(90deg,#10b981,#34d399);"></div>
                            </div>
                            <span style="font-size:0.78rem;font-weight:700;color:#059669;">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:2.5rem;">No queue data for selected period</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif

{{-- ============================================================
     TAB: TOP AGENTS
     ============================================================ --}}
@if($activeTab === 'top-agents')

<div class="dash-card">
    <div class="dash-card-header">
        <span class="dash-card-title"><i class="fa-solid fa-trophy" style="color:#f59e0b;margin-right:6px;"></i>Top Agents Ranking (by Calls Answered)</span>
    </div>
    <div>
        <table class="z-table">
            <thead>
                <tr><th>#</th><th>Agent</th><th>Answered</th><th>Missed</th><th>Avg Talk</th><th>Occupancy</th><th>Utilization</th><th>Score</th></tr>
            </thead>
            <tbody>
                @php
                    $sorted = collect($agentStats)->sortByDesc('callsAnswered')->values();
                @endphp
                @forelse($sorted as $i => $ag)
                <tr style="{{ $i < 3 ? 'background: linear-gradient(90deg, rgba(251,191,36,0.04), transparent);' : '' }}">
                    <td>
                        <span class="rank-badge rank-{{ $i < 3 ? ($i+1) : 'other' }}">{{ $i + 1 }}</span>
                    </td>
                    <td>
                        <div style="font-weight:800;font-size:0.85rem;{{ $i === 0 ? 'color:#b45309;' : '' }}">{{ e($ag['agentName']) }}</div>
                        @if($i === 0) <div style="font-size:0.68rem;color:#f59e0b;font-weight:700;">🏆 Top Performer</div> @endif
                    </td>
                    <td style="font-size:1.1rem;font-weight:800;color:#16a34a;">{{ $ag['callsAnswered'] }}</td>
                    <td style="color:#dc2626;font-weight:600;">{{ $ag['missed'] }}</td>
                    <td style="font-family:monospace;">{{ gmdate("i:s", $ag['averageTalkTime']) }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <div class="pbar" style="width:60px;"><div class="pbar-fill" style="width:{{ $ag['occupancy'] }}%;background:#6366f1;"></div></div>
                            <span style="font-size:0.75rem;font-weight:700;color:#6366f1;">{{ $ag['occupancy'] }}%</span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <div class="pbar" style="width:60px;"><div class="pbar-fill" style="width:{{ $ag['utilization'] }}%;background:#10b981;"></div></div>
                            <span style="font-size:0.75rem;font-weight:700;color:#059669;">{{ $ag['utilization'] }}%</span>
                        </div>
                    </td>
                    <td>
                        @php $score = round(($ag['callsAnswered'] * 2 + $ag['occupancy'] + $ag['utilization']) / 4); @endphp
                        <span style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:white;border-radius:999px;padding:2px 12px;font-size:0.78rem;font-weight:800;">{{ $score }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:2.5rem;">No agent data available</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Top agents bar chart --}}
<div class="dash-card">
    <div class="dash-card-header">
        <span class="dash-card-title"><i class="fa-solid fa-chart-bar" style="color:#4f46e5;margin-right:6px;"></i>Agent Calls Comparison</span>
    </div>
    <div style="padding:1.25rem;">
        <canvas id="agentBarChart" height="80"></canvas>
    </div>
</div>

@endif

<script>
(function loadChartjs() {
    if (window.Chart) return initStatCharts();
    var s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js';
    s.onload = initStatCharts;
    document.head.appendChild(s);
})();

function initStatCharts() {
    const tab = '{{ $activeTab }}';

    if (tab === 'general') {
        // Trend chart
        new Chart(document.getElementById('trendChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
                datasets: [
                    { label:'Total Calls', data:[120,150,180,90,80,160,158], borderColor:'#6366f1', backgroundColor:'rgba(99,102,241,0.1)', fill:true, tension:0.4, borderWidth:2 },
                    { label:'Answered', data:[110,140,170,82,76,148,142], borderColor:'#10b981', backgroundColor:'rgba(16,185,129,0.08)', fill:true, tension:0.4, borderWidth:2 },
                    { label:'Missed', data:[5,6,5,4,2,8,6], borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,0.08)', fill:true, tension:0.4, borderWidth:2 },
                ]
            },
            options: {
                responsive:true, maintainAspectRatio:true,
                plugins: { legend: { position:'top', labels:{ font:{size:11}, boxWidth:12 } } },
                scales: {
                    x: { grid:{display:false}, ticks:{font:{size:10}} },
                    y: { grid:{color:'rgba(0,0,0,0.04)'}, ticks:{font:{size:10}} }
                }
            }
        });

        // Heatmap (custom)
        buildHeatmap();
    }

    if (tab === 'top-agents') {
        const agentData = @json(collect($agentStats)->sortByDesc('callsAnswered')->values()->toArray());
        new Chart(document.getElementById('agentBarChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: agentData.map(a => a.agentName),
                datasets: [
                    { label:'Answered', data:agentData.map(a=>a.callsAnswered), backgroundColor:'rgba(99,102,241,0.85)', borderRadius:6 },
                    { label:'Missed', data:agentData.map(a=>a.missed), backgroundColor:'rgba(239,68,68,0.7)', borderRadius:6 },
                ]
            },
            options: {
                responsive:true, maintainAspectRatio:true,
                plugins: { legend:{ position:'top', labels:{ font:{size:11}, boxWidth:12 } } },
                scales: {
                    x: { grid:{display:false}, ticks:{font:{size:10}} },
                    y: { grid:{color:'rgba(0,0,0,0.04)'}, ticks:{font:{size:10}} }
                }
            }
        });
    }
}

function buildHeatmap() {
    const container = document.getElementById('heatmap-container');
    if (!container) return;
    const hours = Array.from({length:24}, (_,i) => i.toString().padStart(2,'0')+':00');
    const days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    // Simulated heatmap data
    const data = days.map(() => hours.map(() => Math.floor(Math.random() * 30)));
    const maxVal = Math.max(...data.flat());

    let html = '<table style="border-collapse:separate;border-spacing:3px;font-size:0.68rem;">';
    html += '<tr><th style="padding:3px;min-width:38px;"></th>';
    hours.forEach(h => { html += `<th style="padding:3px 1px;color:#64748b;font-weight:600;min-width:28px;text-align:center;">${h.substring(0,2)}</th>`; });
    html += '</tr>';
    days.forEach((day, di) => {
        html += `<tr><td style="padding:3px 6px 3px 0;font-weight:700;color:#334155;white-space:nowrap;">${day}</td>`;
        hours.forEach((_, hi) => {
            const val = data[di][hi];
            const intensity = maxVal > 0 ? val / maxVal : 0;
            const r = Math.round(79 + intensity * (99 - 79));
            const g = Math.round(70 + intensity * (20 - 70));
            const b = Math.round(229 + intensity * (60 - 229));
            const alpha = 0.08 + intensity * 0.85;
            html += `<td title="${day} ${hours[hi]}: ${val} calls" style="width:28px;height:24px;border-radius:4px;background:rgba(${r},${g},${b},${alpha});text-align:center;color:${intensity > 0.5 ? 'white' : '#64748b'};font-weight:600;cursor:default;">${val > 0 ? val : ''}</td>`;
        });
        html += '</tr>';
    });
    html += '</table>';
    container.innerHTML = html;
}

// Quick date range helpers
function setRange(range) {
    const fromEl = document.querySelector('input[name="from"]');
    const toEl = document.querySelector('input[name="to"]');
    const today = new Date();
    const fmt = d => d.toISOString().split('T')[0];
    if (range === 'today') {
        fromEl.value = fmt(today); toEl.value = fmt(today);
    } else if (range === 'yesterday') {
        const y = new Date(today); y.setDate(y.getDate() - 1);
        fromEl.value = fmt(y); toEl.value = fmt(y);
    } else if (range === '7days') {
        const s = new Date(today); s.setDate(s.getDate() - 6);
        fromEl.value = fmt(s); toEl.value = fmt(today);
    } else if (range === '30days') {
        const s = new Date(today); s.setDate(s.getDate() - 29);
        fromEl.value = fmt(s); toEl.value = fmt(today);
    }
    document.getElementById('filter-form').submit();
}
</script>

@endsection
