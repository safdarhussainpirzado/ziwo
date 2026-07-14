@extends('layouts.app')

@section('title', 'ZIWO Dashboard - NHMP 130')

@section('page-title', 'ZIWO Live Dashboard')

@section('content')

<style>
    /* =============================================
       ZIWO ADMIN DASHBOARD STYLES
       ============================================= */
    .ziwo-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%);
        border-radius: 1rem;
        padding: 1.5rem 2rem;
        color: white;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(79, 70, 229, 0.3);
    }
    .ziwo-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
        pointer-events: none;
    }
    .ziwo-header::after {
        content: '';
        position: absolute;
        bottom: -60%;
        right: 10%;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
        pointer-events: none;
    }

    /* KPI Cards */
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .kpi-card {
        background: white;
        border-radius: 0.875rem;
        padding: 1.25rem;
        border: 1px solid #f1f5f9;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
        overflow: hidden;
    }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 6px 24px rgba(0,0,0,0.1); }
    .kpi-card .kpi-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
    }
    .kpi-card .kpi-value { font-size: 1.75rem; font-weight: 800; color: #1e293b; line-height: 1; }
    .kpi-card .kpi-label { font-size: 0.72rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.3rem; }
    .kpi-card .kpi-change { font-size: 0.7rem; font-weight: 700; margin-top: 0.5rem; }
    .kpi-change.up { color: #10b981; }
    .kpi-change.down { color: #ef4444; }

    /* Section card */
    .dash-card {
        background: white;
        border-radius: 0.875rem;
        border: 1px solid #f1f5f9;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .dash-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .dash-card-title { font-size: 0.85rem; font-weight: 700; color: #1e293b; }
    .dash-card-body { padding: 1.25rem; }

    /* Agent / Queue status tables */
    .status-dot {
        width: 8px; height: 8px; border-radius: 50%;
        display: inline-block; margin-right: 6px;
    }
    .status-dot.available { background: #10b981; box-shadow: 0 0 6px #10b981; animation: pulse-glow 2s infinite; }
    .status-dot.busy { background: #f59e0b; box-shadow: 0 0 6px #f59e0b; }
    .status-dot.offline { background: #94a3b8; }
    .status-dot.speaking { background: #6366f1; box-shadow: 0 0 6px #6366f1; animation: pulse-glow 1.5s infinite; }
    .status-dot.ringing { background: #f97316; box-shadow: 0 0 6px #f97316; animation: pulse-glow 0.8s infinite; }

    @keyframes pulse-glow {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    .badge {
        display: inline-flex; align-items: center; padding: 2px 10px;
        border-radius: 999px; font-size: 0.7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.04em;
    }
    .badge-answered { background: #dcfce7; color: #166534; }
    .badge-missed { background: #fee2e2; color: #991b1b; }
    .badge-abandoned { background: #fef3c7; color: #92400e; }
    .badge-busy { background: #fce7f3; color: #9d174d; }
    .badge-noanswer { background: #e0e7ff; color: #3730a3; }
    .badge-speaking { background: #ede9fe; color: #5b21b6; }
    .badge-ringing { background: #fff7ed; color: #9a3412; }

    /* Auto refresh */
    .refresh-select {
        background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 0.5rem; padding: 0.35rem 0.75rem; font-size: 0.78rem; font-weight: 600;
        color: #475569; cursor: pointer;
    }

    /* Live pulse indicator */
    .live-badge {
        display: inline-flex; align-items: center; gap: 5px;
        background: rgba(16,185,129,0.12); color: #059669;
        border: 1px solid rgba(16,185,129,0.3);
        border-radius: 999px; padding: 3px 10px;
        font-size: 0.72rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.07em;
    }
    .live-badge .pulse-dot {
        width: 6px; height: 6px; background: #10b981; border-radius: 50%;
        animation: pulse-glow 1.5s infinite;
    }

    /* Charts row */
    .charts-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
    @media (max-width: 1200px) { .charts-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 800px) { .charts-grid { grid-template-columns: 1fr; } }

    /* Tables */
    .z-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .z-table thead tr { background: #f8fafc; }
    .z-table th { padding: 0.65rem 1rem; text-align: left; font-size: 0.7rem; font-weight: 700;
        color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; }
    .z-table td { padding: 0.65rem 1rem; color: #334155; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .z-table tbody tr:hover { background: #f8fafc; }
    .z-table tbody tr:last-child td { border-bottom: none; }

    /* Toolbar */
    .ziwo-toolbar {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .toolbar-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 0.45rem 1rem; border-radius: 0.6rem;
        font-size: 0.78rem; font-weight: 600; cursor: pointer;
        transition: all 0.2s; border: 1px solid transparent; text-decoration: none;
    }
    .toolbar-btn.primary { background: linear-gradient(135deg,#4f46e5,#7c3aed); color:white; box-shadow: 0 3px 10px rgba(79,70,229,0.3); }
    .toolbar-btn.primary:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(79,70,229,0.4); }
    .toolbar-btn.secondary { background: white; color: #475569; border-color: #e2e8f0; }
    .toolbar-btn.secondary:hover { background: #f8fafc; }
    .toolbar-btn.danger { background: #fee2e2; color: #991b1b; }
    .toolbar-btn.danger:hover { background: #fecaca; }

    /* Connected badge */
    .ziwo-connected {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(79,70,229,0.08);
        border: 1px solid rgba(79,70,229,0.2);
        border-radius: 999px; padding: 4px 12px;
        font-size: 0.75rem; font-weight: 700; color: #4f46e5;
    }
</style>

{{-- Header --}}
<div class="ziwo-header">
    <div style="position:relative;z-index:1;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
            <div>
                <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.4rem;">
                    <div style="background:rgba(255,255,255,0.2);border-radius:0.625rem;padding:0.5rem 0.75rem;">
                        <i class="fa-solid fa-tower-broadcast" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h1 style="font-size:1.3rem;font-weight:800;margin:0;letter-spacing:-0.02em;">ZIWO Live Dashboard</h1>
                        <p style="font-size:0.78rem;opacity:0.8;margin:0;">Real-time Contact Center Monitoring</p>
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                <span class="live-badge"><span class="pulse-dot"></span> Live</span>
                <span style="font-size:0.78rem;opacity:0.85;background:rgba(255,255,255,0.15);border-radius:0.5rem;padding:4px 12px;" id="clock-display"></span>
                <form method="POST" action="{{ route('ziwo.login') }}" style="margin:0;">
                    @csrf
                    @method('POST')
                    {{-- Log out of ZIWO session --}}
                </form>
                <a href="{{ route('ziwo.statistics') }}" style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.3);border-radius:0.5rem;padding:5px 14px;font-size:0.78rem;font-weight:600;text-decoration:none;">
                    <i class="fa-solid fa-chart-bar" style="margin-right:5px;"></i> Statistics
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Toolbar --}}
<div class="ziwo-toolbar">
    <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
        <span class="ziwo-connected">
            <i class="fa-solid fa-circle-check"></i>
            Connected as: <strong>{{ session('ziwo_admin_username', 'Admin') }}</strong>
        </span>
        <span style="font-size:0.78rem;color:#64748b;" id="last-refresh-label">Last refresh: just now</span>
    </div>
    <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
        <select class="refresh-select" id="auto-refresh-select" onchange="setAutoRefresh(this.value)">
            <option value="manual">Manual</option>
            <option value="30">Every 30s</option>
            <option value="60">Every 1 min</option>
            <option value="300">Every 5 min</option>
        </select>
        <button class="toolbar-btn primary" onclick="location.reload()">
            <i class="fa-solid fa-rotate-right"></i> Refresh
        </button>
        <a href="{{ route('ziwo.export', 'csv') }}?tab=general" class="toolbar-btn secondary">
            <i class="fa-solid fa-file-csv"></i> CSV
        </a>
        <a href="{{ route('ziwo.export', 'pdf') }}?tab=general" class="toolbar-btn secondary">
            <i class="fa-solid fa-file-pdf"></i> PDF
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fa-solid fa-phone"></i></div>
        <div class="kpi-value" id="kpi-total">{{ $kpis['totalCalls'] ?? 0 }}</div>
        <div class="kpi-label">Total Calls</div>
        <div class="kpi-change up"><i class="fa-solid fa-arrow-trend-up"></i> +12% vs yesterday</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#dcfce7;color:#16a34a;"><i class="fa-solid fa-phone-volume"></i></div>
        <div class="kpi-value" id="kpi-answered">{{ $kpis['answeredCalls'] ?? 0 }}</div>
        <div class="kpi-label">Answered</div>
        <div class="kpi-change up"><i class="fa-solid fa-arrow-trend-up"></i> 89.9% Answer Rate</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-phone-slash"></i></div>
        <div class="kpi-value" id="kpi-missed">{{ $kpis['missedCalls'] ?? 0 }}</div>
        <div class="kpi-label">Missed</div>
        <div class="kpi-change down"><i class="fa-solid fa-arrow-trend-down"></i> 3.8%</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-person-running"></i></div>
        <div class="kpi-value" id="kpi-abandoned">{{ $kpis['abandonedCalls'] ?? 0 }}</div>
        <div class="kpi-label">Abandoned</div>
        <div class="kpi-change down"><i class="fa-solid fa-arrow-trend-down"></i> 6.3%</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#dbeafe;color:#1d4ed8;"><i class="fa-solid fa-phone-arrow-down-left"></i></div>
        <div class="kpi-value" id="kpi-incoming">{{ $kpis['incomingCalls'] ?? 0 }}</div>
        <div class="kpi-label">Incoming</div>
        <div class="kpi-change up"><i class="fa-solid fa-arrow-up"></i> +8</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#f0fdf4;color:#15803d;"><i class="fa-solid fa-phone-arrow-up-right"></i></div>
        <div class="kpi-value" id="kpi-outgoing">{{ $kpis['outgoingCalls'] ?? 0 }}</div>
        <div class="kpi-label">Outgoing</div>
        <div class="kpi-change up"><i class="fa-solid fa-arrow-up"></i> +3</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#f5f3ff;color:#7c3aed;"><i class="fa-solid fa-clock"></i></div>
        <div class="kpi-value" id="kpi-att">{{ gmdate("i:s", $kpis['averageTalkTime'] ?? 0) }}</div>
        <div class="kpi-label">Avg Talk Time</div>
        <div class="kpi-change up"><i class="fa-solid fa-minus"></i> m:ss</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fff7ed;color:#ea580c;"><i class="fa-solid fa-hourglass-half"></i></div>
        <div class="kpi-value" id="kpi-awt">{{ gmdate("i:s", $kpis['averageWaitingTime'] ?? 0) }}</div>
        <div class="kpi-label">Avg Wait Time</div>
        <div class="kpi-change up"><i class="fa-solid fa-minus"></i> m:ss</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#e0e7ff;color:#4338ca;"><i class="fa-solid fa-headset"></i></div>
        <div class="kpi-value" id="kpi-aha">{{ gmdate("i:s", $kpis['averageHandleTime'] ?? 0) }}</div>
        <div class="kpi-label">Avg Handle Time</div>
        <div class="kpi-change up"><i class="fa-solid fa-minus"></i> m:ss</div>
    </div>
    <div class="kpi-card" style="border: 2px solid #7c3aed;">
        <div class="kpi-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fa-solid fa-circle-nodes"></i></div>
        <div class="kpi-value" id="kpi-active" style="color:#7c3aed;">{{ $kpis['currentActiveCalls'] ?? 0 }}</div>
        <div class="kpi-label">Active Calls Now</div>
        <div class="kpi-change" style="color:#7c3aed;"><i class="fa-solid fa-broadcast-tower"></i> Live</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#dcfce7;color:#15803d;"><i class="fa-solid fa-user-check"></i></div>
        <div class="kpi-value" id="kpi-agents-online">{{ $kpis['onlineAgents'] ?? 0 }}</div>
        <div class="kpi-label">Agents Online</div>
        <div class="kpi-change up"><i class="fa-solid fa-user"></i> of {{ ($kpis['onlineAgents'] ?? 0) + ($kpis['offlineAgents'] ?? 0) }} total</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#f0f9ff;color:#0284c7;"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="kpi-value" id="kpi-longest">{{ gmdate("i:s", $kpis['longestWaitingCall'] ?? 0) }}</div>
        <div class="kpi-label">Longest Wait</div>
        <div class="kpi-change down"><i class="fa-solid fa-minus"></i> m:ss</div>
    </div>
</div>

{{-- Charts Row --}}
<div class="charts-grid">
    {{-- Hourly Calls Line Chart --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <span class="dash-card-title"><i class="fa-solid fa-chart-line" style="color:#6366f1;margin-right:6px;"></i>Hourly Call Volume (Today)</span>
        </div>
        <div class="dash-card-body" style="padding-bottom:0.75rem;">
            <canvas id="hourlyChart" height="130"></canvas>
        </div>
    </div>
    {{-- Answer Rate Donut --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <span class="dash-card-title"><i class="fa-solid fa-chart-pie" style="color:#10b981;margin-right:6px;"></i>Answer Rate</span>
        </div>
        <div class="dash-card-body" style="display:flex;align-items:center;justify-content:center;flex-direction:column;">
            <canvas id="answerRateChart" height="150" width="150"></canvas>
            <div style="display:flex;gap:1rem;margin-top:0.75rem;flex-wrap:wrap;justify-content:center;">
                <span style="font-size:0.7rem;font-weight:700;color:#16a34a;"><span style="display:inline-block;width:8px;height:8px;background:#10b981;border-radius:50%;margin-right:3px;"></span>Answered</span>
                <span style="font-size:0.7rem;font-weight:700;color:#dc2626;"><span style="display:inline-block;width:8px;height:8px;background:#ef4444;border-radius:50%;margin-right:3px;"></span>Missed</span>
                <span style="font-size:0.7rem;font-weight:700;color:#d97706;"><span style="display:inline-block;width:8px;height:8px;background:#f59e0b;border-radius:50%;margin-right:3px;"></span>Abandoned</span>
            </div>
        </div>
    </div>
    {{-- Agent Availability --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <span class="dash-card-title"><i class="fa-solid fa-users" style="color:#8b5cf6;margin-right:6px;"></i>Agent Availability</span>
        </div>
        <div class="dash-card-body" style="display:flex;align-items:center;justify-content:center;flex-direction:column;">
            <canvas id="agentAvailChart" height="150" width="150"></canvas>
            <div style="display:flex;gap:1rem;margin-top:0.75rem;flex-wrap:wrap;justify-content:center;">
                <span style="font-size:0.7rem;font-weight:700;color:#10b981;"><span style="display:inline-block;width:8px;height:8px;background:#10b981;border-radius:50%;margin-right:3px;"></span>Available</span>
                <span style="font-size:0.7rem;font-weight:700;color:#f59e0b;"><span style="display:inline-block;width:8px;height:8px;background:#f59e0b;border-radius:50%;margin-right:3px;"></span>Busy</span>
                <span style="font-size:0.7rem;font-weight:700;color:#94a3b8;"><span style="display:inline-block;width:8px;height:8px;background:#94a3b8;border-radius:50%;margin-right:3px;"></span>Offline</span>
            </div>
        </div>
    </div>
</div>

{{-- Second charts row --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
    {{-- Daily calls bar --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <span class="dash-card-title"><i class="fa-solid fa-chart-bar" style="color:#f59e0b;margin-right:6px;"></i>Daily Calls (Last 7 Days)</span>
        </div>
        <div class="dash-card-body" style="padding-bottom:0.75rem;">
            <canvas id="dailyBarChart" height="140"></canvas>
        </div>
    </div>
    {{-- Call Direction Pie --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <span class="dash-card-title"><i class="fa-solid fa-arrows-left-right-to-line" style="color:#06b6d4;margin-right:6px;"></i>Call Direction</span>
        </div>
        <div class="dash-card-body" style="display:flex;align-items:center;justify-content:center;flex-direction:column;">
            <canvas id="callDirectionChart" height="140"></canvas>
        </div>
    </div>
</div>

{{-- Active Calls & Agent Status row --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
    {{-- Active Calls --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <span class="dash-card-title"><i class="fa-solid fa-circle-nodes" style="color:#7c3aed;margin-right:6px;"></i>Active Calls</span>
            <span class="live-badge"><span class="pulse-dot"></span> Live</span>
        </div>
        <div>
            <table class="z-table">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Caller</th>
                        <th>Queue</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($liveCalls as $call)
                    <tr>
                        <td style="font-weight:600;">{{ e($call['agent']) }}</td>
                        <td style="color:#6366f1;font-family:monospace;">{{ e($call['caller']) }}</td>
                        <td><span style="background:#ede9fe;color:#6d28d9;padding:2px 8px;border-radius:6px;font-size:0.72rem;font-weight:700;">{{ e($call['queue']) }}</span></td>
                        <td id="dur-{{ $loop->index }}" style="font-family:monospace;font-weight:600;" data-seconds="{{ $call['duration'] }}">{{ gmdate("i:s", $call['duration']) }}</td>
                        <td>
                            @php $st = strtolower($call['status']); @endphp
                            <span class="badge badge-{{ $st }}">
                                <span class="status-dot {{ $st }}"></span>{{ e($call['status']) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:2rem;">No active calls right now</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Agent Status --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <span class="dash-card-title"><i class="fa-solid fa-headset" style="color:#0ea5e9;margin-right:6px;"></i>Agent Status</span>
            <span style="font-size:0.72rem;color:#64748b;font-weight:600;">{{ count($agents) }} agents</span>
        </div>
        <div>
            <table class="z-table">
                <thead>
                    <tr><th>Agent</th><th>Ext</th><th>Duration</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($agents as $ag)
                    <tr>
                        <td>
                            <div style="font-weight:700;font-size:0.83rem;">{{ e($ag['agentName']) }}</div>
                            <div style="font-size:0.7rem;color:#94a3b8;">{{ e($ag['username']) }}</div>
                        </td>
                        <td style="font-family:monospace;font-weight:600;color:#6366f1;">{{ e($ag['extension']) }}</td>
                        <td style="font-family:monospace;font-size:0.78rem;">{{ $ag['status'] !== 'offline' ? gmdate("i:s", $ag['duration']) : '—' }}</td>
                        <td>
                            @php $ast = strtolower($ag['status']); @endphp
                            <span style="display:inline-flex;align-items:center;">
                                <span class="status-dot {{ $ast }}"></span>
                                <span style="font-size:0.78rem;font-weight:600;text-transform:capitalize;">{{ e($ag['status']) }}</span>
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:2rem;">No agents data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Queue Performance --}}
<div class="dash-card" style="margin-bottom:1.5rem;">
    <div class="dash-card-header">
        <span class="dash-card-title"><i class="fa-solid fa-layer-group" style="color:#f97316;margin-right:6px;"></i>Queue Performance</span>
    </div>
    <div>
        <table class="z-table">
            <thead>
                <tr>
                    <th>Queue</th>
                    <th>Waiting</th>
                    <th>Answered</th>
                    <th>Abandoned</th>
                    <th>Avg Queue Time</th>
                    <th>Longest Wait</th>
                    <th>Answer %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($queues as $q)
                @php
                    $total = ($q['answeredCalls'] ?? 0) + ($q['abandonedCalls'] ?? 0);
                    $answerPct = $total > 0 ? round(($q['answeredCalls'] / $total) * 100) : 0;
                @endphp
                <tr>
                    <td><span style="font-weight:700;font-size:0.85rem;">{{ e($q['queueName']) }}</span></td>
                    <td>
                        @if(($q['waitingCalls'] ?? 0) > 0)
                            <span style="color:#ef4444;font-weight:700;">{{ $q['waitingCalls'] }}</span>
                        @else
                            <span style="color:#10b981;font-weight:700;">0</span>
                        @endif
                    </td>
                    <td style="color:#16a34a;font-weight:600;">{{ $q['answeredCalls'] ?? 0 }}</td>
                    <td style="color:#dc2626;font-weight:600;">{{ $q['abandonedCalls'] ?? 0 }}</td>
                    <td style="font-family:monospace;">{{ gmdate("i:s", $q['averageQueueTime'] ?? 0) }}</td>
                    <td style="font-family:monospace;">{{ gmdate("i:s", $q['longestWait'] ?? 0) }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;background:#f1f5f9;border-radius:999px;height:6px;min-width:60px;">
                                <div style="width:{{ $answerPct }}%;background:linear-gradient(90deg,#10b981,#34d399);border-radius:999px;height:6px;"></div>
                            </div>
                            <span style="font-size:0.78rem;font-weight:700;color:#16a34a;">{{ $answerPct }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:2rem;">No queue data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Recent Calls --}}
<div class="dash-card" style="margin-bottom:1.5rem;">
    <div class="dash-card-header">
        <span class="dash-card-title"><i class="fa-solid fa-clock-rotate-left" style="color:#64748b;margin-right:6px;"></i>Recent Calls</span>
        <a href="{{ route('ziwo.statistics') }}" style="font-size:0.75rem;color:#6366f1;font-weight:600;text-decoration:none;">View all →</a>
    </div>
    <div>
        <table class="z-table">
            <thead>
                <tr><th>Date/Time</th><th>Caller</th><th>Agent</th><th>Queue</th><th>Duration</th><th>Result</th></tr>
            </thead>
            <tbody>
                @forelse($recentCalls as $call)
                <tr>
                    <td style="font-family:monospace;font-size:0.78rem;color:#64748b;">{{ e($call['createdAt']) }}</td>
                    <td style="font-family:monospace;font-weight:600;color:#6366f1;">{{ e($call['callerNumber']) }}</td>
                    <td style="font-weight:600;">{{ e($call['agentName']) }}</td>
                    <td><span style="background:#f1f5f9;padding:2px 8px;border-radius:6px;font-size:0.72rem;font-weight:700;">{{ e($call['queueName']) }}</span></td>
                    <td style="font-family:monospace;">{{ gmdate("i:s", $call['duration']) }}</td>
                    <td>
                        @php $res = strtolower($call['result']); @endphp
                        <span class="badge badge-{{ str_replace('-', '', $res) }}">{{ e($call['result']) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:2rem;">No recent call data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Scripts --}}
<script>
// Chart.js CDN loaded inline
(function loadChartjs() {
    if (window.Chart) return initCharts();
    var s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js';
    s.onload = initCharts;
    document.head.appendChild(s);
})();

function initCharts() {
    const data = @json($charts);

    // Hourly Line Chart
    new Chart(document.getElementById('hourlyChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: Array.from({length:24}, (_,i) => i.toString().padStart(2,'0')+':00'),
            datasets: [{
                label: 'Calls',
                data: data.hourlyCalls,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 2,
                pointHoverRadius: 5,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 9 }, maxTicksLimit: 12 } },
                y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 9 } } }
            }
        }
    });

    // Answer Rate Donut
    new Chart(document.getElementById('answerRateChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: data.answerRate.labels,
            datasets: [{ data: data.answerRate.data, backgroundColor: ['#10b981','#ef4444','#f59e0b'], borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
            responsive: false, cutout: '65%',
            plugins: { legend: { display: false }, tooltip: { bodyFont: { size: 11 } } }
        }
    });

    // Agent Availability Donut
    new Chart(document.getElementById('agentAvailChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: data.agentAvailability.labels,
            datasets: [{ data: data.agentAvailability.data, backgroundColor: ['#10b981','#f59e0b','#94a3b8'], borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
            responsive: false, cutout: '65%',
            plugins: { legend: { display: false }, tooltip: { bodyFont: { size: 11 } } }
        }
    });

    // Daily Bar Chart
    const days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    new Chart(document.getElementById('dailyBarChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: days,
            datasets: [{
                label: 'Calls',
                data: data.dailyCalls,
                backgroundColor: days.map((_, i) => i === 6 ? '#6366f1' : 'rgba(99,102,241,0.3)'),
                borderRadius: 6,
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 } } }
            }
        }
    });

    // Call Direction Pie
    new Chart(document.getElementById('callDirectionChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: data.callDirection.labels,
            datasets: [{ data: data.callDirection.data, backgroundColor: ['#6366f1','#10b981','#f59e0b'], borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { position: 'right', labels: { font: { size: 11 }, boxWidth: 12 } } }
        }
    });
}

// Clock
function updateClock() {
    const el = document.getElementById('clock-display');
    if (el) el.textContent = new Date().toLocaleTimeString('en-PK');
}
updateClock(); setInterval(updateClock, 1000);

// Live call duration counters
(function tickDurations() {
    const els = document.querySelectorAll('[data-seconds]');
    if (!els.length) return;
    setInterval(() => {
        els.forEach(el => {
            const secs = parseInt(el.getAttribute('data-seconds')) + 1;
            el.setAttribute('data-seconds', secs);
            const m = Math.floor(secs / 60).toString().padStart(2, '0');
            const s = (secs % 60).toString().padStart(2, '0');
            el.textContent = m + ':' + s;
        });
    }, 1000);
})();

// Auto-refresh
let refreshTimer = null;
function setAutoRefresh(val) {
    if (refreshTimer) clearInterval(refreshTimer);
    if (val !== 'manual') {
        const secs = parseInt(val);
        refreshTimer = setInterval(() => location.reload(), secs * 1000);
    }
}

// Last refresh label
(function updateLastRefresh() {
    const el = document.getElementById('last-refresh-label');
    let secs = 0;
    setInterval(() => {
        secs++;
        if (secs < 60) el.textContent = 'Last refresh: ' + secs + 's ago';
        else el.textContent = 'Last refresh: ' + Math.floor(secs/60) + 'm ago';
    }, 1000);
})();
</script>

@endsection
