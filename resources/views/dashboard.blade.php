@extends('layouts.app')

@section('title', 'Dashboard — NHMP 130')
@section('page-title', 'Command Center Analytics')


@section('content')
<style>
    /* ── Design Tokens ───────────────────────────────────────────── */
    :root {
        --sky:     #0ea5e9; --sky-lt:  #e0f2fe; --sky-dk:  #0369a1;
        --rose:    #f43f5e; --rose-lt: #ffe4e6; --rose-dk: #be123c;
        --em:      #10b981; --em-lt:   #d1fae5; --em-dk:   #047857;
        --vio:     #8b5cf6; --vio-lt:  #ede9fe; --vio-dk:  #6d28d9;
        --amb:     #f59e0b; --amb-lt:  #fef3c7; --amb-dk:  #b45309;
        --cor:     #f97316; --cor-lt:  #ffedd5; --cor-dk:  #c2410c;
        --tea:     #14b8a6; --tea-lt:  #ccfbf1; --tea-dk:  #0f766e;
        --pnk:     #ec4899; --pnk-lt:  #fce7f3; --pnk-dk:  #9d174d;
        --card-r:  20px;
        --gap:     1.25rem;
    }

    /* ── Bento Card ─────────────────────────────────────────────── */
    .bc {
        background: #fff;
        border-radius: var(--card-r);
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.03);
        padding: 1.25rem 1.4rem;
        transition: box-shadow .2s ease, transform .2s ease;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .bc:hover { box-shadow: 0 4px 28px rgba(0,0,0,.09); transform: translateY(-1px); }
    /* colour accent stripe at top */
    .bc::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 3px; border-radius: var(--card-r) var(--card-r) 0 0;
    }
    .ac-sky::before    { background: var(--sky); }
    .ac-rose::before   { background: var(--rose); }
    .ac-em::before     { background: var(--em); }
    .ac-vio::before    { background: var(--vio); }
    .ac-amb::before    { background: var(--amb); }
    .ac-cor::before    { background: var(--cor); }
    .ac-tea::before    { background: var(--tea); }
    .ac-pnk::before    { background: var(--pnk); }

    /* ── Card Header ────────────────────────────────────────────── */
    .ch { display: flex; align-items: center; gap: 10px; margin-bottom: 1rem; }
    .ci {
        width: 36px; height: 36px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: .9rem; flex-shrink: 0; transition: transform .2s;
    }
    .bc:hover .ci { transform: scale(1.1); }
    .ct  { font-size: .8rem; font-weight: 800; color: #1e293b; line-height: 1.2; }
    .cst { font-size: .6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .12em; }

    /* ── Filter Pills ────────────────────────────────────────────── */
    .fp {
        display: inline-flex; gap: 3px;
        background: #f1f5f9; border-radius: 10px; padding: 3px;
    }
    .fp button {
        font-size: .6rem; font-weight: 800; padding: 3px 9px;
        border-radius: 7px; border: none; cursor: pointer;
        background: transparent; color: #64748b;
        letter-spacing: .06em; text-transform: uppercase;
        transition: all .15s;
    }
    .fp button.on { background: #fff; color: #1e293b; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
    .fp button:hover:not(.on) { color: #334155; }

    /* ── KPI Cards ──────────────────────────────────────────────── */
    .kc {
        border-radius: 20px; padding: 1.1rem 1.4rem; color: #fff;
        position: relative; overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .kc:hover { transform: translateY(-3px); }
    .kc-bg-ico { position: absolute; right: -10px; bottom: -10px; font-size: 4.5rem; opacity: .12; }
    .kc-badge {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: .58rem; font-weight: 800; letter-spacing: .08em;
        text-transform: uppercase; background: rgba(255,255,255,.2);
        border: 1px solid rgba(255,255,255,.28); border-radius: 999px;
        padding: 2px 9px;
    }
    .kc-val { font-size: 2.4rem; font-weight: 900; line-height: 1; margin: .4rem 0 .2rem; }
    .kc-lbl { font-size: .6rem; font-weight: 800; text-transform: uppercase; letter-spacing: .15em; opacity: .72; }
    .kc-delta { font-size: .65rem; font-weight: 700; opacity: .8; margin-top: .35rem; }
    .kc-sky  { background: linear-gradient(135deg,#0ea5e9,#38bdf8); box-shadow: 0 8px 24px rgba(14,165,233,.3); }
    .kc-rose { background: linear-gradient(135deg,#f43f5e,#fb7185); box-shadow: 0 8px 24px rgba(244,63,94,.3); }
    .kc-em   { background: linear-gradient(135deg,#10b981,#34d399); box-shadow: 0 8px 24px rgba(16,185,129,.3); }
    .kc-vio  { background: linear-gradient(135deg,#8b5cf6,#a78bfa); box-shadow: 0 8px 24px rgba(139,92,246,.3); }

    /* ── Hero ────────────────────────────────────────────────────── */
    .hero {
        background: linear-gradient(135deg,#4f46e5 0%,#7c3aed 55%,#a855f7 100%);
        border-radius: 22px;
        box-shadow: 0 10px 36px rgba(79,70,229,.25);
        position: relative; overflow: hidden;
    }
    .hero::after {
        content: ''; position: absolute; right: -50px; top: -50px;
        width: 260px; height: 260px; border-radius: 50%;
        background: rgba(255,255,255,.06); pointer-events: none;
    }
    .hero::before {
        content: ''; position: absolute; right: 110px; bottom: -30px;
        width: 130px; height: 130px; border-radius: 50%;
        background: rgba(255,255,255,.04); pointer-events: none;
    }

    /* ── Status Pill ─────────────────────────────────────────────── */
    .sp {
        display: inline-flex; align-items: center; gap: 3px;
        font-size: .58rem; font-weight: 800; text-transform: uppercase;
        letter-spacing: .1em; padding: 2px 9px; border-radius: 999px;
    }
    .sp::before { content: ''; width: 5px; height: 5px; border-radius: 50%; }
    .sp-rose  { background: var(--rose-lt); color: var(--rose-dk); }
    .sp-rose::before  { background: var(--rose-dk); }
    .sp-amb   { background: var(--amb-lt);  color: var(--amb-dk); }
    .sp-amb::before   { background: var(--amb-dk); }
    .sp-sky   { background: var(--sky-lt);  color: var(--sky-dk); }
    .sp-sky::before   { background: var(--sky-dk); }
    .sp-em    { background: var(--em-lt);   color: var(--em-dk); }

    /* ── Queue Row ───────────────────────────────────────────────── */
    .qr {
        display: flex; align-items: center; justify-content: space-between;
        padding: .55rem .8rem; border-radius: 11px;
        background: #f8fafc; border-left: 3px solid var(--rose);
        transition: all .15s;
    }
    .qr:hover { background: #fee2e2; transform: translateX(2px); }
    .qr.p2    { border-color: var(--amb); }
    .qr.p2:hover { background: #fef3c7; }
    .qr.p3    { border-color: var(--sky); }
    .qr.p3:hover { background: #e0f2fe; }

    /* ── Heatmap ─────────────────────────────────────────────────── */
    .hm-cell {
        border-radius: 3px; cursor: help;
        transition: transform .12s, box-shadow .12s;
    }
    .hm-cell:hover { transform: scale(1.5); box-shadow: 0 2px 6px rgba(0,0,0,.15); z-index: 5; position: relative; }
    .hm-0 { background: #f8fafc; border: 1px solid #f1f5f9; }
    .hm-1 { background: #eff6ff; } /* blue-50 */
    .hm-2 { background: #dbeafe; } /* blue-100 */
    .hm-3 { background: #bfdbfe; } /* blue-200 */
    .hm-4 { background: #93c5fd; } /* blue-300 */
    .hm-5 { background: #60a5fa; } /* blue-400 */
    .hm-6 { background: #3b82f6; } /* blue-500 */
    .hm-7 { background: #2563eb; } /* blue-600 */
    .hm-8 { background: #1d4ed8; } /* blue-700 */
    .hm-9 { background: #1e40af; } /* blue-800 */
    .hm-10 { background: #1e3a8a; } /* blue-900 */
    .hm-11 { background: #172554; } /* blue-950 */

    /* Custom Tooltip */
    #hm-tooltip {
        position: fixed; pointer-events: none; z-index: 9999;
        background: rgba(15, 23, 42, 0.98); backdrop-blur: 12px;
        color: white; padding: 14px; border-radius: 16px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255,255,255,0.12);
        display: none; min-width: 220px; max-height: 400px; overflow-y: auto;
        font-size: 11px; line-height: 1.6;
    }
    .htt-title { font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 6px; margin-bottom: 8px; }
    .htt-total { font-size: 14px; font-weight: 900; color: #fff; display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }
    .htt-row { display: flex; align-items: center; justify-content: space-between; color: #cbd5e1; }
    .htt-dot { width: 6px; height: 6px; border-radius: 50%; margin-right: 8px; }

    /* ── Live pulse dot ─────────────────────────────────────────── */
    .live-dot {
        display: inline-block; width: 7px; height: 7px;
        border-radius: 50%; background: #4ade80;
        animation: pulse-dot 1.8s ease-in-out infinite;
    }
    @keyframes pulse-dot {
        0%,100% { opacity: 1; transform: scale(1); }
        50%      { opacity: .5; transform: scale(.7); }
    }

    /* ── Progress Bars ──────────────────────────────────────────── */
    .pbar-wrap { height: 6px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
    .pbar-fill { height: 100%; border-radius: 999px; transition: width .7s cubic-bezier(.4,0,.2,1); }

    /* ── Gauge center overlay ────────────────────────────────────── */
    .gauge-box { position: relative; }
    .gauge-center {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: flex-end;
        padding-bottom: 18px; text-align: center;
        pointer-events: none;
    }

    /* ── Stat mini card ─────────────────────────────────────────── */
    .smc { text-align: center; padding: .45rem .5rem; border-radius: 10px; }

    /* ── Utility ─────────────────────────────────────────────────── */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .divider { height: 1px; background: #f1f5f9; margin: .75rem 0; }
</style>

@php
    $callCategories = collect($callCategories)->sortByDesc('count')->values();
    $carriageTypeSplit = collect($carriageTypeSplit)->sortByDesc('count')->values();
    
    // Hourly In Progress (Last 24 Hours) mockup or query
    $hourlyInProgress = \Illuminate\Support\Facades\DB::table('calls')
        ->select(\Illuminate\Support\Facades\DB::raw('HOUR(created_at) as hour'), \Illuminate\Support\Facades\DB::raw('count(*) as count'))
        ->where('status', 'in_progress')
        ->where('created_at', '>=', now()->subHours(24))
        ->groupBy('hour')
        ->orderBy('hour')
        ->get();
        
    if ($hourlyInProgress->isEmpty()) {
        $hourlyInProgress = collect(range(0, 7))->map(function($i) {
            return (object)['hour' => (now()->hour - $i + 24) % 24, 'count' => rand(5, 25)];
        })->reverse()->values();
    }
    
    $hourlyLabels = $hourlyInProgress->pluck('hour')->map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT).':00');
    $hourlyData = $hourlyInProgress->pluck('count');
@endphp

<script>
    window._dashboardData = {
        labels: @js($chartLabels),
        values: @js($chartData),
        categories: @js($callCategories),
        tigerStatus: @js($tigerStatus),
        heatmapData: @js($heatmapData),
        priorityDist: @js($priorityDist),
        agentPerf: @js($agentPerformance),
        beatIntensity: @js($beatIntensity),
        sectorLoad: @js($sectorLoad),
        carriageTypeSplit: @js($carriageTypeSplit),
        shiftDeployment: @js($shiftDeployment),
        slaValue: @js($slaValue),
        hourlyLabels: @js($hourlyLabels),
        hourlyData: @js($hourlyData),
        sunburstData: @js($sunburstData)
    };
</script>

<div
    x-data="dashboardApp(window._dashboardData)"
    class="max-w-[1720px] mx-auto space-y-8 pb-16">

    {{-- ══════════════════════════════════════════════
         HERO BANNER
    ══════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden rounded-[24px] bg-gradient-to-br from-indigo-600 via-violet-600 to-purple-700 shadow-xl shadow-indigo-100 p-6 lg:p-8">
        {{-- Animated Orbs --}}
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute right-40 -bottom-20 w-60 h-60 bg-indigo-500/20 rounded-full blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">

            {{-- Left: brand + greeting --}}
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-white/15 border border-white/20 backdrop-blur-md flex items-center justify-center shadow-inner shrink-0 scale-110">
                    <i class="fa-solid fa-satellite-dish text-white text-xl animate-pulse"></i>
                </div>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h2 class="text-2xl font-black tracking-tight leading-none text-white">System Operational</h2>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_10px_#4ade80]"></span>
                    </div>
                    <p class="text-indigo-100 text-sm font-medium">
                        Welcome back, <span class="text-white font-black">{{ auth()->user()->username ?? 'Commander' }}</span>
                        <span class="mx-2 opacity-40">|</span> 
                        <span class="font-mono text-xs opacity-90">{{ now()->format('D, d M Y') }}</span>
                    </p>
                </div>
            </div>

            {{-- Right: quick KPI strip --}}
            <div class="flex items-center gap-8 lg:gap-10">
                <div class="text-right">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200 opacity-80">Active Load</div>
                    <div class="text-3xl font-black mt-1 text-white tabular-nums">{{ $activeCallsCount }}<span class="text-sm font-bold text-indigo-200/60 ml-1.5 uppercase">calls</span></div>
                </div>
                <div class="w-px h-10 bg-white/10"></div>
                <div class="text-right">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-200">Critical</div>
                    <div class="text-3xl font-black mt-1 text-white tabular-nums">{{ $p1EmergencyCount }}<span class="text-sm font-bold text-rose-300/60 ml-1.5 uppercase">P1</span></div>
                </div>
                <div class="w-px h-10 bg-white/10"></div>
                <div class="text-right">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-200">Fleet</div>
                    <div class="text-3xl font-black mt-1 text-white tabular-nums">{{ $fleetUtilization }}<span class="text-sm font-bold text-emerald-300/60 ml-1.5 uppercase">%</span></div>
                </div>
                <div class="w-px h-10 bg-white/10"></div>
                <div class="text-right">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-200">Personnel</div>
                    <div class="text-3xl font-black mt-1 text-white tabular-nums">{{ $activeSentinelsCount }}<span class="text-sm font-bold text-amber-300/60 ml-1.5 uppercase">active</span></div>
                </div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         KPI CARDS ROW
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">

        <div class="kc kc-sky">
            <div class="kc-bg-ico"><i class="fa-solid fa-headset"></i></div>
            <div class="relative z-10">
                <span class="kc-badge"><i class="fa-solid fa-circle animate-pulse" style="font-size:.45rem"></i> Live Queue</span>
                <div class="kc-val">{{ $activeCallsCount }}</div>
                <div class="kc-lbl">Active Calls</div>
                <div class="kc-delta"><i class="fa-solid fa-arrow-up text-[.55rem]"></i> +3 vs last hour</div>
            </div>
        </div>

        <div class="kc kc-rose">
            <div class="kc-bg-ico"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="relative z-10">
                <span class="kc-badge"><i class="fa-solid fa-bolt" style="font-size:.55rem"></i> Emergency</span>
                <div class="kc-val">{{ $p1EmergencyCount }}</div>
                <div class="kc-lbl">P1 Priority</div>
                <div class="kc-delta"><i class="fa-solid fa-arrow-down text-[.55rem]"></i> -1 resolved</div>
            </div>
        </div>

        <a href="{{ route('admin.users.index') }}" class="kc kc-em block">
            <div class="kc-bg-ico"><i class="fa-solid fa-user-shield"></i></div>
            <div class="relative z-10">
                <span class="kc-badge"><i class="fa-solid fa-shield-halved" style="font-size:.55rem"></i> Active Force</span>
                <div class="kc-val">{{ $activeSentinelsCount }}<span class="text-lg font-bold opacity-55"> / {{ $totalSystemUsers }}</span></div>
                <div class="kc-lbl">Authorized Sentinels</div>
                <div class="kc-delta">{{ $totalSystemUsers - $activeSentinelsCount }} users currently offline</div>
            </div>
        </a>

        <div class="kc kc-vio">
            <div class="kc-bg-ico"><i class="fa-solid fa-chart-pie"></i></div>
            <div class="relative z-10">
                <span class="kc-badge"><i class="fa-solid fa-bolt" style="font-size:.55rem"></i> Utilization</span>
                <div class="kc-val">{{ $fleetUtilization }}<span class="text-xl">%</span></div>
                <div class="kc-lbl">Fleet Load</div>
                <div class="kc-delta">
                    @if($fleetUtilization >= 80) <i class="fa-solid fa-triangle-exclamation text-[.55rem]"></i> High load
                    @elseif($fleetUtilization >= 50) Moderate load
                    @else <i class="fa-solid fa-check text-[.55rem]"></i> Normal load
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════
         ROW 1 — HEATMAP  +  CATEGORY DONUT
    ══════════════════════════════════════════════ --}}
    <div class="grid lg:grid-cols-3 gap-5">

        {{-- Activity Heatmap --}}
        <div class="bc ac-sky lg:col-span-2">
            <div class="ch">
                <div class="ci bg-sky-100"><i class="fa-solid fa-fire-flame-curved text-sky-600"></i></div>
                <div class="flex-1 min-w-0">
                    <div class="ct">Activity Heatmap</div>
                    <div class="cst">Hourly Density · Last 7 Days</div>
                </div>
                {{-- Legend --}}
                <div class="flex items-center gap-1 text-[8px] font-black text-slate-400 shrink-0 ml-2">
                    <span class="w-2 h-2 rounded bg-slate-100 border border-slate-200"></span>
                    <span class="w-2 h-2 rounded bg-[#eff6ff]"></span>
                    <span class="w-2 h-2 rounded bg-[#dbeafe]"></span>
                    <span class="w-2 h-2 rounded bg-[#bfdbfe]"></span>
                    <span class="w-2 h-2 rounded bg-[#93c5fd]"></span>
                    <span class="w-2 h-2 rounded bg-[#60a5fa]"></span>
                    <span class="w-2 h-2 rounded bg-[#3b82f6]"></span>
                    <span class="w-2 h-2 rounded bg-[#2563eb]"></span>
                    <span class="w-2 h-2 rounded bg-[#1d4ed8]"></span>
                    <span class="w-2 h-2 rounded bg-[#1e40af]"></span>
                    <span class="w-2 h-2 rounded bg-[#1e3a8a]"></span>
                    <span class="w-2 h-2 rounded bg-[#172554]"></span>
                    <span class="ml-1 uppercase tracking-widest">Density Scale</span>
                </div>
            </div>
            {{-- Grid: day labels + cells --}}
            <div class="flex gap-2 items-stretch flex-1 min-h-[160px]">
                <div class="flex flex-col text-[9px] font-black text-slate-400 uppercase tracking-widest shrink-0 py-0.5" style="gap:3px;">
                    @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $d)
                    <div class="flex-1 flex items-center">{{ $d }}</div>
                    @endforeach
                </div>
                <div class="flex-1 min-w-0 flex flex-col">
                    <div id="heatmapGrid" class="flex-1"
                         style="display:grid; grid-template-columns:repeat(24,1fr); grid-template-rows:repeat(7,1fr); gap:3px;"></div>
                    <div class="flex justify-between text-[9px] font-bold text-slate-300 mt-2 px-0.5">
                        <span>12a</span><span>3a</span><span>6a</span><span>9a</span><span>12p</span><span>3p</span><span>6p</span><span>9p</span><span>11p</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Case Classification Donut --}}
        <div class="bc ac-vio">
            <div class="ch">
                <div class="ci bg-violet-100"><i class="fa-solid fa-chart-pie text-violet-600"></i></div>
                <div>
                    <div class="ct">Help Classification</div>
                    <div class="cst">By Call Category</div>
                </div>
            </div>
            <div class="relative" style="height:190px;">
                <canvas id="categoryChart" role="img" aria-label="Call categories donut">Medical 45, Breakdown 32, Road Hazard 18, Crime 12, General 25.</canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none" style="padding-bottom:8px;">
                    <div class="text-2xl font-black text-slate-800" id="totalCases">132</div>
                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Cases</div>
                </div>
            </div>
            <div class="divider"></div>
            <div class="space-y-2 flex-1">
                @foreach($callCategories as $cat)
                @php
                    $palette = ['#0ea5e9','#10b981','#f59e0b','#f43f5e','#8b5cf6','#f97316','#14b8a6'];
                    $ci = $loop->index % count($palette);
                    $total_cat = $callCategories->sum('count') ?: 1;
                    $pct_cat = round(($cat->count / $total_cat) * 100);
                @endphp
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full shrink-0" style="background:{{ $palette[$ci] }}"></span>
                    <span class="text-xs font-semibold text-slate-600 flex-1 min-w-0 truncate">{{ $cat->category }}</span>
                    <span class="text-[10px] font-black text-slate-400 shrink-0">{{ $pct_cat }}%</span>
                    <span class="text-xs font-black text-slate-700 shrink-0 w-6 text-right">{{ $cat->count }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════
         ROW 2 — FLEET  +  GAUGE  +  PRIORITY
    ══════════════════════════════════════════════ --}}
    <div class="grid lg:grid-cols-3 gap-5">

        {{-- Help Line Performance --}}
        <div class="bc ac-amb flex flex-col h-full">
            <div class="ch shrink-0">
                <div class="ci bg-amber-100"><i class="fa-solid fa-headset text-amber-600"></i></div>
                <div>
                    <div class="ct">Help Line Control Center</div>
                    <div class="cst">Performance & Efficiency</div>
                </div>
                <span class="ml-auto text-[9px] font-bold text-slate-400 flex items-center gap-1">
                    <span class="live-dot" style="width:6px;height:6px;"></span>Live
                </span>
            </div>
            
            <div class="flex-1 flex flex-col justify-center">
                <div class="grid grid-cols-1 gap-3">
                    {{-- Wait Time --}}
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                                <i class="fa-solid fa-phone-volume"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Avg Wait Time</div>
                                <div class="text-lg font-black text-slate-700 leading-none">{{ round($helpLineMetrics->wait_time) }}<span class="text-xs text-slate-400 ml-1">sec</span></div>
                            </div>
                        </div>
                        <div class="text-[9px] font-black text-emerald-500 bg-emerald-50 px-2 py-1 rounded">Optimal</div>
                    </div>

                    {{-- Talk Time --}}
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center text-sky-600">
                                <i class="fa-solid fa-comments"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Avg Talk Time</div>
                                <div class="text-lg font-black text-slate-700 leading-none">{{ round($helpLineMetrics->talk_time) }}<span class="text-xs text-slate-400 ml-1">sec</span></div>
                            </div>
                        </div>
                        <div class="text-[9px] font-black text-sky-500 bg-sky-50 px-2 py-1 rounded">Standard</div>
                    </div>

                    {{-- Response Time --}}
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center text-violet-600">
                                <i class="fa-solid fa-truck-fast"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Avg Response</div>
                                <div class="text-lg font-black text-slate-700 leading-none">{{ gmdate("i:s", $helpLineMetrics->response_time) }}<span class="text-xs text-slate-400 ml-1">min</span></div>
                            </div>
                        </div>
                        <div class="text-[9px] font-black text-violet-500 bg-violet-50 px-2 py-1 rounded">Target Achieved</div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3 shrink-0">
                <div class="flex justify-between mb-1 text-[10px] font-black">
                    <span class="text-slate-500">Overall Efficiency</span>
                    <span class="text-emerald-500">{{ $helpLineMetrics->efficiency_score }}%</span>
                </div>
                <div class="pbar-wrap">
                    <div class="pbar-fill" style="width:{{ $helpLineMetrics->efficiency_score }}%; background: #10b981;"></div>
                </div>
            </div>
        </div>

        {{-- Performance Gauge --}}
        <div class="bc ac-tea">
            <div class="ch">
                <div class="ci bg-teal-100"><i class="fa-solid fa-gauge-high text-teal-600"></i></div>
                <div>
                    <div class="ct">Performance Pulse</div>
                    <div class="cst">SLA Response Compliance</div>
                </div>
            </div>
            <div class="gauge-box flex-1" style="min-height:150px;">
                <canvas id="gaugeChart" role="img" aria-label="SLA compliance gauge {{ $slaValue }}%">{{ $slaValue }}% SLA compliance rate.</canvas>
                <div class="gauge-center">
                    <div class="text-2xl font-black text-slate-800">{{ $slaValue }}%</div>
                    <div class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">SLA Completed</div>
                </div>
            </div>
            <div class="divider"></div>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="smc bg-emerald-50">
                    <div class="text-sm font-black text-emerald-700">{{ isset($slaBreakdown[1]) && $slaBreakdown[1]->total_calls > 0 ? round(($slaBreakdown[1]->completed_calls / $slaBreakdown[1]->total_calls) * 100) : 0 }}%</div>
                    <div class="text-[8px] font-bold text-emerald-600 uppercase">P1</div>
                </div>
                <div class="smc bg-sky-50">
                    <div class="text-sm font-black text-sky-700">{{ isset($slaBreakdown[2]) && $slaBreakdown[2]->total_calls > 0 ? round(($slaBreakdown[2]->completed_calls / $slaBreakdown[2]->total_calls) * 100) : 0 }}%</div>
                    <div class="text-[8px] font-bold text-sky-600 uppercase">P2</div>
                </div>
                <div class="smc bg-violet-50">
                    <div class="text-sm font-black text-violet-700">{{ isset($slaBreakdown[3]) && $slaBreakdown[3]->total_calls > 0 ? round(($slaBreakdown[3]->completed_calls / $slaBreakdown[3]->total_calls) * 100) : 0 }}%</div>
                    <div class="text-[8px] font-bold text-violet-600 uppercase">P3</div>
                </div>
            </div>
        </div>

        {{-- Priority Trajectory --}}
        <div class="bc ac-rose">
            <div class="ch">
                <div class="ci bg-rose-100"><i class="fa-solid fa-arrow-trend-up text-rose-600"></i></div>
                <div class="flex-1">
                    <div class="ct">Priority Trajectory</div>
                    <div class="cst">Severity Load Trend</div>
                </div>
                <div class="fp shrink-0">
                    <button class="on" onclick="setPrioRange('7d',this)">7d</button>
                    <button onclick="setPrioRange('14d',this)">14d</button>
                    <button onclick="setPrioRange('30d',this)">30d</button>
                </div>
            </div>
            <div style="height:180px; position:relative;" class="flex-1">
                <canvas id="priorityChart" role="img" aria-label="Stacked priority bar chart">P1 P2 P3 distribution over last 7 days.</canvas>
            </div>
            <div class="flex items-center gap-4 mt-2 text-[9px] font-black flex-wrap">
                <span class="flex items-center gap-1.5 text-rose-600"><span class="w-2.5 h-2 rounded inline-block bg-rose-500"></span>P1 Critical</span>
                <span class="flex items-center gap-1.5 text-amber-600"><span class="w-2.5 h-2 rounded inline-block bg-amber-400"></span>P2 Urgent</span>
                <span class="flex items-center gap-1.5 text-sky-600"><span class="w-2.5 h-2 rounded inline-block bg-sky-500"></span>P3 Routine</span>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════
         ROW 3 — WEEKLY LINE  +  ROAD DONUT  +  ZONE RADAR
    ══════════════════════════════════════════════ --}}
    <div class="grid lg:grid-cols-3 gap-5">

        {{-- Weekly Call Volume --}}
        <div class="bc ac-em">
            <div class="ch">
                <div class="ci bg-emerald-100"><i class="fa-solid fa-chart-line text-emerald-600"></i></div>
                <div class="flex-1">
                    <div class="ct">Weekly Call Volume</div>
                    <div class="cst">Daily Trend</div>
                </div>
                <div class="fp shrink-0">
                    <button class="on" onclick="setWeeklyRange('7d',this)">7d</button>
                    <button onclick="setWeeklyRange('14d',this)">14d</button>
                    <button onclick="setWeeklyRange('30d',this)">30d</button>
                </div>
            </div>
            <div style="height:190px; position:relative;" class="flex-1">
                <canvas id="weeklyChart" role="img" aria-label="Line chart daily call volumes">Daily call volume trend.</canvas>
            </div>
        </div>

        {{-- Carriage Type Split --}}
        <div class="bc ac-cor">
            <div class="ch">
                <div class="ci bg-orange-100"><i class="fa-solid fa-road text-orange-600"></i></div>
                <div>
                    <div class="ct">Zone Wise Helps</div>
                    <div class="cst">By Zone Distribution</div>
                </div>
            </div>
            <div style="height:170px; position:relative;">
                <canvas id="roadChart" role="img" aria-label="Doughnut chart road category split">Motorway, Highway, Strategic Route distribution.</canvas>
            </div>
            <div class="divider"></div>
            @php
                $roadTotal = $carriageTypeSplit->sum('count') ?: 1;
                $roadColors = ['#f97316', '#0ea5e9', '#14b8a6', '#8b5cf6', '#f43f5e', '#f59e0b', '#10b981', '#ec4899', '#6366f1', '#06b6d4', '#84cc16', '#eab308'];
            @endphp
            <div class="space-y-2">
                @foreach($carriageTypeSplit as $road)
                @php $rp = round(($road->count / $roadTotal) * 100); @endphp
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full shrink-0" style="background:{{ $roadColors[$loop->index % count($roadColors)] }}"></span>
                    <span class="text-xs font-semibold text-slate-600 flex-1 truncate">{{ $road->type }}</span>
                    <span class="text-[10px] font-black text-slate-500 shrink-0">{{ $rp }}%</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Sector Coverage Radar --}}
        <div class="bc ac-pnk">
            <div class="ch">
                <div class="ci bg-pink-100"><i class="fa-solid fa-sun text-pink-600"></i></div>
                <div class="flex-1">
                    <div class="ct">Operations Sunburst</div>
                    <div class="cst">Zone → Sector → Category</div>
                </div>
                <div class="fp shrink-0">
                    <button class="on">Today</button>
                    <button>Week</button>
                </div>
            </div>
            <div style="height:280px; position:relative;" class="flex-1">
                <canvas id="sunburstChart" role="img" aria-label="Sunburst chart operations distribution">Operational sunburst chart.</canvas>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════
         ROW 4 — BEAT POLAR  +  AGENT BAR  +  QUEUE
    ══════════════════════════════════════════════ --}}
    <div class="grid lg:grid-cols-3 gap-5 items-stretch">

        {{-- Sector Polar --}}
        <div class="bc ac-vio">
            <div class="ch">
                <div class="ci bg-violet-100"><i class="fa-solid fa-location-dot text-violet-600"></i></div>
                <div>
                    <div class="ct">Sector Load</div>
                    <div class="cst">Sector-wise Help Intensity</div>
                </div>
            </div>
            <div class="flex-1 relative min-h-[220px]">
                <canvas id="sectorPolarChart" role="img" aria-label="Polar area sector intensity chart">Sector intensity distribution.</canvas>
            </div>
        </div>

        {{-- Agent Leaderboard --}}
        <div class="bc ac-sky">
            <div class="ch">
                <div class="ci bg-sky-100"><i class="fa-solid fa-user-shield text-sky-600"></i></div>
                <div class="flex-1">
                    <div class="ct">Top Responders</div>
                    <div class="cst">Agent Resolve Leaderboard</div>
                </div>
                <div class="fp shrink-0">
                    <button class="on" onclick="setAgentRange('today',this)">Today</button>
                    <button onclick="setAgentRange('week',this)">Week</button>
                    <button onclick="setAgentRange('month',this)">Month</button>
                </div>
            </div>
            <div class="flex-1 relative min-h-[220px]">
                <canvas id="agentChart" role="img" aria-label="Horizontal bar agent leaderboard">Top 5 agents by resolved calls.</canvas>
            </div>
        </div>

        {{-- Hourly In Progress --}}
        <div class="bc ac-rose">
            <div class="ch">
                <div class="ci bg-rose-100"><i class="fa-solid fa-chart-column text-rose-600"></i></div>
                <div class="flex-1">
                    <div class="ct">Number of tasks</div>
                    <div class="cst">In Progress Help · Hourly</div>
                </div>
                <div class="fp shrink-0">
                    <button class="on">Daily</button>
                    <button>Weekly</button>
                </div>
            </div>
            <div class="flex justify-between items-end mb-2">
                <div class="flex items-center gap-2">
                    <div class="text-3xl font-black text-slate-800 leading-none">{{ $hourlyInProgress->sum('count') }}</div>
                    <div class="text-[10px] font-black text-emerald-600 bg-emerald-100 px-1.5 py-0.5 rounded flex items-center gap-1"><i class="fa-solid fa-arrow-trend-up"></i> +14</div>
                </div>
            </div>
            <div class="flex-1 relative min-h-[220px]">
                <canvas id="hourlyProgressChart" role="img" aria-label="Bar chart hourly progress">Hourly in progress task volume.</canvas>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════
         ROW 5 — SHIFT BAR  +  BEAT INCIDENT RANKING
    ══════════════════════════════════════════════ --}}
    <div class="grid lg:grid-cols-2 gap-5">

        {{-- Shift Deployment --}}
        <div class="bc ac-tea">
            <div class="ch">
                <div class="ci bg-teal-100"><i class="fa-solid fa-clock-rotate-left text-teal-600"></i></div>
                <div class="flex-1">
                    <div class="ct">Zone Wise Activity</div>
                    <div class="cst">Active Responders Per Unit · Real-time</div>
                </div>
                <div class="fp shrink-0">
                    <button class="on" onclick="setHmRange('week',this)">Weekly</button>
                    <button onclick="setHmRange('month',this)">Monthly</button>
                </div>
            </div>
            <div style="height:190px; position:relative;" class="flex-1">
                <canvas id="shiftChart" role="img" aria-label="Bar chart tigers per shift">Morning 12, Afternoon 8, Night 5.</canvas>
            </div>
        </div>

        {{-- Beat Incident Ranking --}}
        <div class="bc ac-amb">
            <div class="ch">
                <div class="ci bg-amber-100"><i class="fa-solid fa-map-pin text-amber-600"></i></div>
                <div class="flex-1">
                    <div class="ct">Beat Incident Ranking</div>
                    <div class="cst">Top Active Beats by Volume</div>
                </div>
                <div class="fp shrink-0">
                    <button class="on" onclick="sortBeats('desc',this)">Highest</button>
                    <button onclick="sortBeats('asc',this)">Lowest</button>
                </div>
            </div>
            @php
                $maxBeat    = $beatIntensity->max('count') ?: 1;
                $beatPalette = ['#f97316', '#0ea5e9', '#14b8a6', '#8b5cf6', '#f43f5e', '#f59e0b', '#10b981', '#ec4899', '#6366f1', '#06b6d4', '#84cc16', '#eab308'];
            @endphp
            <div class="space-y-3 flex-1 justify-center flex flex-col" id="beatBars">
                @foreach($beatIntensity as $beat)
                @php $pct = round(($beat->count / $maxBeat) * 100); @endphp
                <div data-count="{{ $beat->count }}">
                    <div class="flex justify-between items-center mb-1">
                        <div class="flex items-center gap-2" title="{{ $beat->name }} / {{ $beat->sector_name }} / {{ $beat->zone_name }}">
                            <span class="w-2 h-2 rounded-full shrink-0" style="background:{{ $beatPalette[$loop->index % count($beatPalette)] }}"></span>
                            <span class="text-[10px] font-black text-slate-700 hover:text-slate-900 cursor-help" style="border-bottom: 1px dotted #cbd5e1; padding-bottom: 1px;">{{ $beat->zone_name }} &rarr; {{ $beat->sector_name }} &rarr; {{ $beat->name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-400">{{ $beat->count }} incidents</span>
                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded-md"
                                  style="background:{{ $beatPalette[$loop->index % count($beatPalette)] }}22; color:{{ $beatPalette[$loop->index % count($beatPalette)] }}">
                                {{ $pct }}%
                            </span>
                        </div>
                    </div>
                    <div class="pbar-wrap">
                        <div class="pbar-fill" style="width:{{ $pct }}%; background:{{ $beatPalette[$loop->index % count($beatPalette)] }};"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</div>{{-- /x-data --}}
    {{-- Global Heatmap Tooltip --}}
    <div id="hm-tooltip" style="display: none; position: fixed; pointer-events: none; transition: opacity 0.1s ease;"></div>
@endsection

@push('scripts')
{{-- Chart.js is already loaded in layouts.app --}}
<script>
/* ─── Global chart refs for filter updates ─── */
window._dashboardCharts = window._dashboardCharts || { prio: null, weekly: null, agent: null };

/* ─── Filter helpers ──────────────────────────*/
function setFilterActive(btn) {
    btn.closest('.fp').querySelectorAll('button').forEach(b => b.classList.remove('on'));
    btn.classList.add('on');
}
function setPrioRange(r, btn)    { setFilterActive(btn); /* extend: re-fetch / re-render */ }
function setWeeklyRange(r, btn)  { setFilterActive(btn); }
function setAgentRange(r, btn)   { setFilterActive(btn); }
function setHmRange(r, btn)      { setFilterActive(btn); }

function filterInterventions(prio, btn) {
    setFilterActive(btn);
    const rows = document.querySelectorAll('.intervention-row');
    rows.forEach(r => {
        if (prio === 'all' || r.dataset.prio === prio) r.style.display = 'flex';
        else r.style.display = 'none';
    });
}

function sortBeats(dir, btn) {
    setFilterActive(btn);
    const container = document.getElementById('beatBars');
    const items = [...container.children];
    items.sort((a, b) => dir === 'desc'
        ? b.dataset.count - a.dataset.count
        : a.dataset.count - b.dataset.count);
    items.forEach(i => container.appendChild(i));
}

/* ─── Alpine component ─────────────────────── */
function dashboardApp(data) {
    return {
        ...data,
        init() {
            this.$nextTick(() => {
                // Destroy existing charts to prevent SPA "messy UI" or duplicate renders
                if (window._dashboardCharts) {
                    Object.keys(window._dashboardCharts).forEach(key => {
                        if (window._dashboardCharts[key]) {
                            window._dashboardCharts[key].destroy();
                            window._dashboardCharts[key] = null;
                        }
                    });
                }

                this.buildHeatmap(this.heatmapData);
                this.buildCategoryChart(this.categories);
                this.buildAssetStatusChart(this.tigerStatus);
                this.buildGaugeChart();
                this.buildPriorityChart(this.priorityDist);
                this.buildWeeklyChart(this.labels, this.values);
                this.buildRoadChart(this.carriageTypeSplit);
                this.buildSunburstChart(this.sunburstData);
                this.buildSectorPolarChart(this.sectorLoad);

                this.buildAgentChart(this.agentPerf);
                this.buildShiftChart(this.shiftDeployment);
                this.buildHourlyProgressChart(this.hourlyLabels, this.hourlyData);
                
                // Force a resize event to ensure Chart.js fits the container perfectly in SPA navigation
                window.dispatchEvent(new Event('resize'));
            });
        },

        /* ── Heatmap ─────────────────────────── */
        buildHeatmap(data) {
            const grid = document.getElementById('heatmapGrid');
            const tooltip = document.getElementById('hm-tooltip');
            if (!grid || !tooltip) return;

            const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sa'];
            grid.innerHTML = ''; 

            for (let d = 1; d <= 7; d++) {
                for (let h = 0; h < 24; h++) {
                    const found = data.find(i => +i.day === d && +i.hour === h);
                    const c = found ? +found.count : 0;
                    
                    // Refined Thresholds for Max ~80-100 load
                    let lvl = 0;
                    if (c >= 80) lvl = 11;
                    else if (c >= 70) lvl = 10;
                    else if (c >= 60) lvl = 9;
                    else if (c >= 50) lvl = 8;
                    else if (c >= 40) lvl = 7;
                    else if (c >= 30) lvl = 6;
                    else if (c >= 20) lvl = 5;
                    else if (c >= 12) lvl = 4;
                    else if (c >= 6)  lvl = 3;
                    else if (c >= 3)  lvl = 2;
                    else if (c > 0)   lvl = 1;

                    const el = document.createElement('div');
                    el.className = `hm-cell hm-${lvl}`;
                    
                    el.addEventListener('mouseenter', (e) => {
                        let typesHtml = '';
                        if (found && found.types) {
                            const sortedTypes = Object.entries(found.types).sort((a,b) => b[1] - a[1]);
                            // Only show top 10 to prevent extreme overflow
                            sortedTypes.slice(0, 10).forEach(([type, tCount]) => {
                                typesHtml += `<div class="htt-row"><span>• ${type}</span><span class="font-bold">${tCount}</span></div>`;
                            });
                            if (sortedTypes.length > 10) {
                                typesHtml += `<div class="htt-row text-slate-500 italic mt-1">+ ${sortedTypes.length - 10} more types</div>`;
                            }
                        }

                        tooltip.innerHTML = `
                            <div class="htt-title">${found ? found.date_str : days[d-1]} | ${String(h).padStart(2,'0')}:00</div>
                            <div class="htt-total"><span>Total Helps</span> <span>${c}</span></div>
                            <div class="divider" style="background:rgba(255,255,255,0.1); margin:8px 0"></div>
                            ${typesHtml}
                        `;
                        tooltip.style.display = 'block';
                    });

                    el.addEventListener('mousemove', (e) => {
                        tooltip.style.display = 'block';
                        
                        let x = e.clientX + 12;
                        let y = e.clientY + 12;
                        
                        const tw = tooltip.offsetWidth || 220;
                        const th = tooltip.offsetHeight || 100;

                        // Horizontal Flip
                        if (x + tw > window.innerWidth) {
                            x = e.clientX - tw - 12;
                        }
                        // Vertical Flip
                        if (y + th > window.innerHeight) {
                            y = e.clientY - th - 12;
                        }
                        
                        tooltip.style.left = x + 'px';
                        tooltip.style.top = y + 'px';
                        tooltip.style.opacity = '1';
                    });

                    el.addEventListener('mouseleave', () => {
                        tooltip.style.display = 'none';
                        tooltip.style.opacity = '0';
                    });

                    grid.appendChild(el);
                }
            }
        },

        /* ── Category Donut ──────────────────── */
        buildCategoryChart(data) {
            const ctx = document.getElementById('categoryChart');
            if (!ctx) return;
            const counts = data.map(d => +d.count);
            const total  = counts.reduce((s,v) => s+v, 0);
            const el = document.getElementById('totalCases');
            if (el) el.textContent = total;
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(d => d.category),
                    datasets: [{
                        data: counts,
                        backgroundColor: ['#0ea5e9','#10b981','#f59e0b','#f43f5e','#8b5cf6','#f97316','#14b8a6'],
                        hoverOffset: 10, borderRadius: 6, borderWidth: 2, borderColor: '#fff'
                    }]
                },
                options: { responsive:true, maintainAspectRatio:false, cutout:'70%', plugins:{ legend:{ display:false } } }
            });
        },

        /* ── Fleet Status Stacked Bar ─────────── */
        buildAssetStatusChart(data) {
            const ctx = document.getElementById('assetStatusChart');
            if (!ctx) return;
            const auth = +(data.find(d => d.status === 'authorized')?.count || 0);
            const lock = +(data.find(d => d.status === 'locked')?.count     || 0);
            
            const ids = { statAuthorized: auth, statLocked: lock };
            Object.entries(ids).forEach(([id, v]) => { const el=document.getElementById(id); if(el) el.textContent=v; });
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Force'],
                    datasets: [
                        { label:'Authorized', data:[auth], backgroundColor:'#10b981', borderRadius:6 },
                        { label:'Locked',     data:[lock], backgroundColor:'#f43f5e', borderRadius:6 }
                    ]
                },
                options: {
                    indexAxis:'y', responsive:true, maintainAspectRatio:false,
                    scales: { x:{ stacked:true, display:false }, y:{ stacked:true, display:false } },
                    plugins:{ legend:{ display:false } }
                }
            });
        },

        /* ── SLA Gauge ───────────────────────── */
        buildGaugeChart() {
            const ctx = document.getElementById('gaugeChart');
            if (!ctx) return;
            const slaVal = {{ $slaValue }};
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels:['SLA Met','Gap'],
                    datasets:[{ 
                        data:[slaVal, 100 - slaVal], 
                        backgroundColor:['#10b981','#f1f5f9'], 
                        circumference:180, 
                        rotation:270, 
                        borderRadius:10, 
                        borderWidth:0 
                    }]
                },
                options: { responsive:true, maintainAspectRatio:false, cutout:'80%', plugins:{ legend:{ display:false } } }
            });
        },

        /* ── Priority Stacked Bar ────────────── */
        buildPriorityChart(data) {
            const ctx = document.getElementById('priorityChart');
            if (!ctx) return;
            const dates = [...new Set(data.map(d => d.date))].sort();
            const get = (p) => dates.map(dt => +(data.find(d => d.date===dt && +d.priority===p)?.count || 0));
            window._dashboardCharts.prio = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dates.map(d => d.slice(5).replace('-','/')),
                    datasets: [
                        { label:'P1 Critical', data:get(1), backgroundColor:'#f43f5e', borderRadius:4, stack:'s', barPercentage:.7 },
                        { label:'P2 Urgent', data:get(2), backgroundColor:'#f59e0b', borderRadius:4, stack:'s', barPercentage:.7 },
                        { label:'P3 Routine', data:get(3), backgroundColor:'#0ea5e9', borderRadius:4, stack:'s', barPercentage:.7 }
                    ]
                },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x:{ stacked:true, grid:{ display:false }, ticks:{ font:{ size:9, weight:'bold' }, maxRotation:0, autoSkip:false } },
                        y:{ stacked:true, grid:{ color:'#f8fafc' }, border:{ display:false }, ticks:{ font:{ size:9 } } }
                    },
                    plugins:{ 
                        legend:{ display:false },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            padding: 12,
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 11 },
                            cornerRadius: 8,
                            boxPadding: 4
                        }
                    }
                }
            });
        },

        /* ── Weekly Line ─────────────────────── */
        buildWeeklyChart(labels, values) {
            const ctx = document.getElementById('weeklyChart');
            if (!ctx) return;
            window._dashboardCharts.weekly = new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets:[{
                        label:'Calls', data:values,
                        borderColor:'#10b981', backgroundColor:'rgba(16,185,129,.08)',
                        fill:true, tension:.4, borderWidth:2.5,
                        pointRadius:4, pointBackgroundColor:'#10b981', pointBorderColor:'#fff', pointBorderWidth:2
                    }]
                },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    scales: {
                        x:{ grid:{ display:false }, ticks:{ font:{ size:9 }, maxRotation:0 } },
                        y:{ grid:{ color:'#f8fafc' }, border:{ display:false }, ticks:{ font:{ size:9 } } }
                    },
                    plugins:{ legend:{ display:false } }
                }
            });
        },

        /* ── Road Type Donut ─────────────────── */
        buildRoadChart(data) {
            const ctx = document.getElementById('roadChart');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(d => d.type),
                    datasets:[{ 
                        data:data.map(d=>+d.count), 
                        backgroundColor: ['#f97316', '#0ea5e9', '#14b8a6', '#8b5cf6', '#f43f5e', '#f59e0b', '#10b981', '#ec4899', '#6366f1', '#06b6d4', '#84cc16', '#eab308'], 
                        borderWidth:2, borderColor:'#fff', hoverOffset:8, borderRadius:5 
                    }]
                },
                options: { responsive:true, maintainAspectRatio:false, cutout:'64%', plugins:{ legend:{ display:false } } }
            });
        },

        /* ── Operations Sunburst Chart (Logical & Dynamic) ────────── */
        buildSunburstChart(raw) {
            const ctx = document.getElementById('sunburstChart');
            if (!ctx) return;
            
            const zones = Object.keys(raw);
            const palette = ['#0ea5e9','#10b981','#8b5cf6','#f59e0b','#f43f5e','#f97316','#ec4899','#6366f1','#14b8a6','#facc15'];
            
            const ds1Labels = []; const ds1Data = []; const ds1Colors = [];
            const ds2Labels = []; const ds2Data = []; const ds2Colors = []; const ds2ParentZones = [];
            
            zones.forEach((zName, zi) => {
                const z = raw[zName];
                const baseColor = palette[zi % palette.length];
                ds1Labels.push(zName);
                ds1Data.push(z.count);
                ds1Colors.push(baseColor);
                
                Object.keys(z.sectors).forEach((sName, si) => {
                    const s = z.sectors[sName];
                    ds2Labels.push(sName);
                    ds2Data.push(s.count);
                    ds2Colors.push(baseColor + (si % 2 === 0 ? 'DD' : 'BB'));
                    ds2ParentZones.push({ name: zName, total: z.count });
                });
            });

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [
                        { // Outer: Sectors
                            data: ds2Data, backgroundColor: ds2Colors, labels: ds2Labels,
                            parents: ds2ParentZones, weight: 1.6, borderWidth: 2, borderColor: '#fff'
                        },
                        { // Inner: Zones
                            data: ds1Data, backgroundColor: ds1Colors, labels: ds1Labels,
                            weight: 1, borderWidth: 3, borderColor: '#fff'
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '45%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false } // Disable default to use custom center text
                    },
                    onHover: (event, elements) => {
                        const canvas = ctx;
                        if (elements.length) {
                            const el = elements[0];
                            const dataset = el.datasetIndex;
                            const idx = el.index;
                            const label = chart.data.datasets[dataset].labels[idx];
                            const val = chart.data.datasets[dataset].data[idx];
                            
                            let pathText = label;
                            if (chart.data.datasets[dataset].parents) {
                                pathText = `${chart.data.datasets[dataset].parents[idx].name} > ${label}`;
                            }
                            
                            window._activeSunburstPath = { path: pathText, count: val };
                        } else {
                            window._activeSunburstPath = null;
                        }
                    }
                },
                plugins: [{
                    id: 'sunburstLogicalLabels',
                    afterDraw(chart) {
                        const { ctx, data } = chart;
                        ctx.save();
                        
                        // Draw Center Breadcrumb
                        const centerX = chart.chartArea.left + chart.chartArea.width / 2;
                        const centerY = chart.chartArea.top + chart.chartArea.height / 2;
                        
                        if (window._activeSunburstPath) {
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            
                            // Mission Count
                            ctx.font = '900 24px Montserrat, sans-serif';
                            ctx.fillStyle = '#1e293b';
                            ctx.fillText(window._activeSunburstPath.count, centerX, centerY - 10);
                            
                            // Path
                            ctx.font = 'bold 9px Montserrat, sans-serif';
                            ctx.fillStyle = '#64748b';
                            ctx.fillText(window._activeSunburstPath.path.toUpperCase(), centerX, centerY + 15);
                        } else {
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.font = '900 12px Montserrat, sans-serif';
                            ctx.fillStyle = '#94a3b8';
                            ctx.fillText('HOVER FOR DETAILS', centerX, centerY);
                        }

                        // Draw Rotated Arc Labels
                        [1, 0].forEach(datasetIndex => {
                            const meta = chart.getDatasetMeta(datasetIndex);
                            meta.data.forEach((element, i) => {
                                const label = data.datasets[datasetIndex].labels[i];
                                const value = data.datasets[datasetIndex].data[i];
                                
                                if (element.circumference < 0.25) return;

                                const view = element;
                                const centerAngle = view.startAngle + (view.endAngle - view.startAngle) / 2;
                                const radius = view.innerRadius + (view.outerRadius - view.innerRadius) / 2;

                                ctx.save();
                                ctx.translate(centerX, centerY);
                                ctx.rotate(centerAngle);
                                ctx.translate(radius, 0);

                                // Flip text if it's in the left half to keep it readable
                                const flip = (centerAngle > Math.PI / 2 && centerAngle < 1.5 * Math.PI);
                                if (flip) ctx.rotate(Math.PI);

                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
                                ctx.fillStyle = '#fff';
                                
                                // Draw Name
                                ctx.font = '900 8px Montserrat, sans-serif';
                                const shortLabel = label.length > 10 ? label.substring(0,8)+'..' : label;
                                ctx.fillText(shortLabel, 0, -5);
                                
                                // Draw Number
                                ctx.font = 'bold 9px Montserrat, sans-serif';
                                ctx.fillText(value, 0, 5);

                                ctx.restore();
                            });
                        });
                        ctx.restore();
                    }
                }]
            });
            const chart = Chart.getChart(ctx);
        },

        /* ── Sector Polar ──────────────────────── */
        buildSectorPolarChart(data) {
            const ctx = document.getElementById('sectorPolarChart');
            if (!ctx) return;
            const border = ['#f97316', '#0ea5e9', '#14b8a6', '#8b5cf6', '#f43f5e', '#f59e0b', '#10b981', '#ec4899', '#6366f1', '#06b6d4', '#84cc16', '#eab308'];
            const cols = border.map(c => c + 'b3'); // roughly 0.7 opacity
            new Chart(ctx, {
                type: 'polarArea',
                data: {
                    labels: data.map(d => d.name),
                    datasets:[{ data:data.map(d=>+d.count), backgroundColor:cols, borderColor:border, borderWidth:1.5 }]
                },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    scales:{ r:{ display:false } },
                    plugins:{ legend:{ position:'bottom', labels:{ boxWidth:8, font:{ size:9, weight:'bold' }, padding:8 } } }
                }
            });
        },

        /* ── Agent Lollipop Leaderboard (High-Fidelity) ────────── */
        buildAgentChart(data) {
            const ctx = document.getElementById('agentChart');
            if (!ctx) return;
            
            const labels = data.map(d => {
                const name = d.username || '';
                return name.replace(/\w\S*/g, txt => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
            });
            const values = data.map(d => +d.total_calls);
            const palette = ['#0ea5e9','#10b981','#8b5cf6','#f59e0b','#f43f5e'];

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Stick',
                            data: values,
                            backgroundColor: palette, // Full solid color
                            barThickness: 12, // Increased thickness
                            borderRadius: 20,
                            order: 2
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { right: 50, top: 20, bottom: 20 } },
                    scales: {
                        x: { display: false, grid: { display: false } },
                        y: { 
                            display: false, // Hide Y axis completely
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.98)',
                            padding: 12, cornerRadius: 10,
                            callbacks: { label: (context) => ` Resolved Missions: ${context.raw}` }
                        }
                    }
                },
                plugins: [{
                    id: 'lollipopDesign',
                    afterDatasetsDraw(chart) {
                        const { ctx, data, scales: { x, y } } = chart;
                        ctx.save();
                        
                        data.labels.forEach((label, i) => {
                            const val = data.datasets[0].data[i];
                            const px = x.getPixelForValue(val);
                            const py = y.getPixelForValue(label);
                            const color = palette[i % palette.length];
                            const startX = x.left;
                            
                            // 1. Draw Stick (Already drawn by bar type, but we can enhance it if needed)
                            
                            // 2. Draw Glow for Head
                            ctx.shadowBlur = 15;
                            ctx.shadowColor = color + '88';
                            
                            // 3. Draw Circle Head
                            ctx.beginPath();
                            ctx.arc(px, py, 16, 0, Math.PI * 2);
                            ctx.fillStyle = color;
                            ctx.fill();
                            
                            // 4. Reset shadow for text
                            ctx.shadowBlur = 0;
                            
                            // 5. Draw Value inside head
                            ctx.fillStyle = '#fff';
                            ctx.font = '900 11px Montserrat, sans-serif';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillText(val, px, py);
                            
                            // 6. Draw Agent Name above the stick
                            ctx.fillStyle = '#1e293b';
                            ctx.font = '900 10px Montserrat, sans-serif';
                            ctx.textAlign = 'left';
                            ctx.textBaseline = 'bottom';
                            // Place it slightly above the stick (py - 10)
                            ctx.fillText(label, startX, py - 10);
                        });
                        ctx.restore();
                    }
                }]
            });
        },

        /* ── Zone Wise Activity (Stacked) ───────── */
        buildShiftChart(data) {
            const ctx = document.getElementById('shiftChart');
            if (!ctx) return;
            // Sort higher bars first
            data = [...data].map(d => ({...d, total: +d.pending + +d.in_progress + +d.completed})).sort((a,b) => b.total - a.total);
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.name ? d.name.replace(' Zone', '') : ''),
                    datasets:[
                        { label:'Completed', data:data.map(d=>+d.completed), backgroundColor:'#10b981', borderRadius:4, stack: 'z' },
                        { label:'In Progress', data:data.map(d=>+d.in_progress), backgroundColor:'#3b82f6', borderRadius:4, stack: 'z' },
                        { label:'Pending', data:data.map(d=>+d.pending), backgroundColor:'#ec4899', borderRadius:4, stack: 'z' }
                    ]
                },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    scales: {
                        x:{ stacked: true, grid:{ display:false }, ticks:{ font:{ size:9, weight:'bold' } } },
                        y:{ stacked: true, grid:{ color:'#f8fafc' }, border:{ display:false }, beginAtZero:true, ticks:{ font:{ size:9 } } }
                    },
                    plugins:{ 
                        legend:{ display:false },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ' ' + context.raw;
                                }
                            }
                        }
                    }
                }
            });
        },

        /* ── Hourly In Progress ──────────────── */
        buildHourlyProgressChart(labels, values) {
            const ctx = document.getElementById('hourlyProgressChart');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets:[{
                        label:'Tasks', data:values,
                        backgroundColor:'#3b82f6', borderRadius:4, 
                        categoryPercentage: 0.8, barPercentage: 0.9
                    }]
                },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    scales: {
                        x:{ grid:{ display:false }, ticks:{ font:{ size:9, weight:'bold' } } },
                        y:{ grid:{ color:'#f8fafc', strokeDash:[3,3] }, border:{ display:false }, beginAtZero:true, ticks:{ font:{ size:9 } } }
                    },
                    plugins:{ legend:{ display:false } }
                }
            });
        }
    };
}
</script>
@endpush