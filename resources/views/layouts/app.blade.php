<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="appLayout">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'NHMP 130 CRM'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    {{-- Redundant CDN links removed; now bundled via Vite in resources/css/app.css --}}


    <!-- Compiled Assets (Tailwind, Alpine, etc.) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Chart.js now bundled via Vite in resources/js/app.js --}}


    <!-- Notification & Routing JS -->
    <script defer src="{{ asset('js/notification.js') }}"></script>
    <script defer src="{{ asset('js/bento-bridge.js') }}?v={{ time() }}"></script>
    
    {{-- Hardened Session Security --}}
    <script>
        (function() {
            "use strict";
            const LOGOUT_KEY = 'nhmp_session_logout';
            const CHECK_INT = 15000; // Increased frequency (15s)
            
            // 1. Cross-Tab Sync: Trigger a reload if another tab logs out
            window.addEventListener('storage', (e) => {
                if (e.key === LOGOUT_KEY && e.newValue) {
                    localStorage.removeItem(LOGOUT_KEY);
                    // Slight delay to allow server-side logout to finalize
                    setTimeout(() => { window.location.reload(); }, 500);
                }
            });

            // 2. bfcache Protection
            window.addEventListener('pageshow', (e) => {
                if (e.persisted) window.location.reload();
            });

            // 3. Heartbeat Polling: Safety net for silent session expiry
            let isSyncing = false;
            function heartbeat() {
                if (isSyncing) return;
                fetch('/auth/heartbeat', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => { 
                    if (r.status === 401 || r.status === 419) {
                        isSyncing = true;
                        window.location.href = '/login';
                    }
                })
                .catch(() => {});
            }
            setInterval(heartbeat, 60000); // Optimized to 60s check
            heartbeat(); // Initial check

            // 4. Inactivity Auto-Logout (15 Minutes)
            const IDLE_LIMIT = 30 * 60 * 1000; // 30 mins
            let idleTimer;

            const resetIdleTimer = () => {
                clearTimeout(idleTimer);
                idleTimer = setTimeout(() => {
                    console.log('SessionGuard: Inactivity limit reached.');
                    window.SessionGuard.triggerGlobalLogout();
                    window.location.href = '/logout';
                }, IDLE_LIMIT);
            };

            ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(name => {
                document.addEventListener(name, resetIdleTimer, true);
            });
            resetIdleTimer();

            // Global trigger
            window.SessionGuard = {
                triggerGlobalLogout: function() {
                    localStorage.setItem(LOGOUT_KEY, Date.now().toString());
                }
            };
        })();
    </script>

    @stack('styles')

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Navy colors */
        .text-navy-900 {
            color: #0a1c2f !important;
        }

        .bg-navy-900 {
            background-color: #0a1c2f !important;
        }

        .border-navy-900 {
            border-color: #0a1c2f !important;
        }

        .from-navy-900 {
            --tw-gradient-from: #0a1c2f;
        }

        .shadow-navy-900\/30 {
            --tw-shadow-color: rgba(10, 28, 47, 0.3);
        }

        /* ============================================
           SIDEBAR SYSTEM
        ============================================ */
        .sidebar-wrap {
            width: 260px;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: visible;
            /* Allow toggle tab to overflow */
            flex-shrink: 0;
            position: relative;
        }

        .sidebar-wrap.collapsed {
            width: 68px;
        }

        /* Clip nav to sidebar width while keeping toggle visible */
        .sidebar-inner {
            width: 100%;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Premium Floating Toggle Tab */
        .sidebar-toggle-tab {
            position: absolute;
            right: -20px;
            top: 50%;
            margin-top: -20px;
            z-index: 60;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ffffff, #f1f5f9);
            border: 3px solid #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            color: #64748b;
        }

        .sidebar-toggle-tab:hover {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-color: #3b82f6;
            color: white;
            transform: translate(2px, -2px) !important;
            box-shadow: -4px 4px 0px #1e40af, 0 10px 15px -3px rgba(37, 99, 235, 0.4);
        }

        .sidebar-toggle-tab:active {
            transform: translate(0px, 0px) !important;
            box-shadow: 0 2px 5px rgba(37, 99, 235, 0.3);
        }

        .sidebar-toggle-tab i {
            font-size: 0.85rem;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .sidebar-wrap.collapsed .sidebar-toggle-tab i {
            transform: rotate(180deg) scale(1.1);
        }

        /* Mobile: slide in/out */
        @media (max-width: 1023px) {
            .sidebar-wrap {
                position: fixed;
                inset-y: 0;
                left: 0;
                z-index: 50;
                width: 260px !important;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .sidebar-wrap.mobile-open {
                transform: translateX(0);
            }
        }

        /* Nav item base */
        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.625rem 0.75rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 0.8rem;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            position: relative;
            text-decoration: none;
            gap: 0.75rem;
            -webkit-tap-highlight-color: transparent;
            -webkit-user-select: none;
            user-select: none;
            outline: none;
        }

        .nav-item:focus, .nav-item:active {
            outline: none;
        }

        .nav-item:hover {
            background-color: #f1f5f9;
            color: #3b82f6;
        }

        .nav-item.active {
            background-color: #2563eb !important;
            color: white !important;
            box-shadow: -4px 4px 0px #1e40af, 0 10px 15px -3px rgba(37, 99, 235, 0.4) !important;
            transform: translate(3px, -3px) !important;
            font-weight: 950 !important;
            position: relative;
            z-index: 10;
        }

        .nav-item.active * {
            color: white !important;
        }

        /* Active item hover */
        .nav-item.active:hover {
            background-color: #1d4ed8 !important;
            transform: translate(1px, -1px) !important;
            box-shadow: -2px 2px 0px #1e40af, 0 10px 15px -3px rgba(37, 99, 235, 0.4) !important;
        }

        .nav-item:hover .nav-icon {
            color: #2563eb;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
            color: #94a3b8;
            transition: color 0.2s;
        }

        .nav-label {
            transition: opacity 0.2s ease, transform 0.2s ease;
            opacity: 1;
            transform: translateX(0);
            overflow: hidden;
        }

        .collapsed .nav-label {
            opacity: 0;
            transform: translateX(-8px);
            pointer-events: none;
            width: 0;
        }

        /* Section headings */
        .nav-section {
            font-size: 0.6rem;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #cbd5e1;
            padding: 0.875rem 0.875rem 0.375rem;
            white-space: nowrap;
            transition: opacity 0.2s ease;
        }

        .collapsed .nav-section {
            opacity: 0;
            height: 0;
            padding: 0;
            overflow: hidden;
        }

        .collapsed .nav-section-divider {
            display: block !important;
        }

        .nav-section-divider {
            display: none;
            height: 1px;
            background: #e2e8f0;
            margin: 0.5rem 0.75rem;
        }

        /* Tooltip */
        .nav-tooltip {
            position: absolute;
            left: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%);
            background: #0f172a;
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s ease, transform 0.15s ease;
            transform: translateY(-50%) translateX(-4px);
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .nav-tooltip::before {
            content: '';
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-right-color: #0f172a;
        }

        .collapsed .nav-item:hover .nav-tooltip {
            opacity: 1;
            transform: translateY(-50%) translateX(0);
        }

        /* Search box */
        .sidebar-search {
            transition: opacity 0.2s ease;
        }

        .collapsed .sidebar-search {
            opacity: 0;
            height: 0;
            overflow: hidden;
            padding: 0;
            margin: 0;
        }

        /* User footer */
        .sidebar-user-label {
            transition: opacity 0.2s ease, width 0.2s ease;
            overflow: hidden;
        }

        .collapsed .sidebar-user-label {
            opacity: 0;
            width: 0;
        }

        /* Toggle button */
        .sidebar-toggle-btn {
            width: 28px;
            height: 28px;
            border-radius: 0.5rem;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease, transform 0.3s ease;
            flex-shrink: 0;
        }

        .sidebar-toggle-btn:hover {
            background: #e2e8f0;
            color: #2563eb;
        }

        .sidebar-toggle-btn.rotated {
            transform: rotate(180deg);
        }

        .collapsed .sidebar-toggle-btn {
            margin: 0 auto;
        }

        /* Brand area */
        .sidebar-brand-text {
            transition: opacity 0.2s ease, width 0.2s ease;
            overflow: hidden;
            white-space: nowrap;
        }

        .collapsed .sidebar-brand-text {
            opacity: 0;
            width: 0;
        }

        /* Primary badge pill */
        .nav-badge {
            margin-left: auto;
            font-size: 0.55rem;
            font-weight: 900;
            padding: 0.1rem 0.4rem;
            border-radius: 9999px;
            transition: opacity 0.2s ease;
        }

        .collapsed .nav-badge {
            opacity: 0;
        }

        /* Density / table utilities */
        .condensed-table td,
        .condensed-table th {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
            font-size: 0.7rem !important;
        }

        .spacious-table td,
        .spacious-table th {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
            font-size: 0.75rem !important;
        }

        .rose-reset-btn {
            background-color: #e11d48 !important;
            color: white !important;
            border: 1px solid #e11d48 !important;
        }

        .rose-reset-btn:hover {
            background-color: #be123c !important;
        }
        .card-3d-active {
            transform: translate(2px, -2px) !important;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        }
        .card-3d-active.amber { box-shadow: -4px 4px 0px #b45309, 0 10px 15px -3px rgba(245, 158, 11, 0.4) !important; }
        .card-3d-active.blue  { box-shadow: -4px 4px 0px #1e40af, 0 10px 15px -3px rgba(37, 99, 235, 0.4) !important; }
        .card-3d-active.indigo { box-shadow: -4px 4px 0px #4338ca, 0 10px 15px -3px rgba(79, 70, 229, 0.4) !important; }
        .card-3d-active.purple { box-shadow: -4px 4px 0px #7e22ce, 0 10px 15px -3px rgba(147, 51, 234, 0.4) !important; }
        .card-3d-active.emerald { box-shadow: -4px 4px 0px #047857, 0 10px 15px -3px rgba(16, 185, 129, 0.4) !important; }
        .card-3d-active.rose { box-shadow: -4px 4px 0px #be123c, 0 10px 15px -3px rgba(225, 29, 72, 0.4) !important; }
        .card-3d-active.sky { box-shadow: -4px 4px 0px #0369a1, 0 10px 15px -3px rgba(14, 165, 233, 0.4) !important; }
        .card-3d-active.slate { box-shadow: -4px 4px 0px #334155, 0 10px 15px -3px rgba(71, 85, 105, 0.4) !important; }
    </style>
</head>

<body x-data="heartbeatManager" class="font-sans antialiased text-slate-800 bg-[#f4f7fb] selection:bg-blue-500 selection:text-white">

    <div class="flex h-screen overflow-hidden">
        {{-- Global SPA Loader --}}
        <div x-show="navigating" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[10000] bg-white/60 backdrop-blur-md flex flex-col items-center justify-center gap-4"
             x-cloak>
            <div class="relative">
                <div class="w-14 h-14 border-[3px] border-blue-100 border-t-blue-600 rounded-full animate-spin"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fa-solid fa-shield-halved text-blue-600 text-xs animate-pulse"></i>
                </div>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-[10px] font-black text-blue-700 uppercase tracking-[0.3em] animate-pulse">NHMP 130 Helpline</span>
                <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Syncing Emergency Operations Center...</span>
            </div>
        </div>

        <!-- Mobile overlay -->
        <div x-show="isMobile && mobileOpen" x-transition:enter="transition-opacity duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="mobileOpen = false"
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 lg:hidden" x-cloak>
        </div>

        <!-- ============================
             SIDEBAR
        ============================ -->
        <aside class="sidebar-wrap h-screen bg-white border-r border-slate-100 shadow-[2px_0_20px_rgba(0,0,0,0.04)]"
            :class="{
                'collapsed': !isMobile && !expanded,
                'mobile-open': isMobile && mobileOpen
            }">

            <!-- Floating toggle tab (always visible) -->
            <button class="sidebar-toggle-tab hidden lg:flex group" @click="expanded = !expanded"
                :title="expanded ? 'Collapse Sidebar' : 'Expand Sidebar'">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <div class="sidebar-inner">

                <!-- Brand Header -->
                <div class="flex items-center px-4 h-[64px] border-b border-slate-100 shrink-0 gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-[0_4px_10px_rgba(37,99,235,0.3)] shrink-0 mx-auto">
                        <i class="fa-solid fa-shield-halved text-sm"></i>
                    </div>
                    <div class="flex flex-col sidebar-brand-text flex-1 min-w-0">
                        <span class="font-black text-base tracking-tight text-navy-900 leading-none">NHMP<span
                                class="text-blue-600">130</span></span>
                        <span class="text-[8px] font-black text-slate-400 tracking-[0.12em] uppercase mt-0.5">Control
                            Grid</span>
                    </div>
                    <!-- Mobile close -->
                    <button @click="mobileOpen = false"
                        class="lg:hidden w-7 h-7 flex items-center justify-center text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                        <i class="fa-solid fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-0.5 no-scrollbar">

                    @php 
                        $cur = Route::currentRouteName();
                        $st  = request('status');
                    @endphp

                    <!-- Dashboard -->
                    @can('dashboard.view')
                        <a href="{{ route('dashboard') }}"
                            class="nav-item {{ $cur === 'dashboard' ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span>
                            <span class="nav-label">Dashboard</span>
                            <span class="nav-tooltip">Dashboard</span>
                        </a>
                    @endcan

                    <!-- CALL CENTER -->
                    <div class="nav-section">Call Center</div>
                    <div class="nav-section-divider"></div>

                    @can('calls.create')
                        <a href="{{ route('calls.create') }}"
                            class="nav-item {{ $cur === 'calls.create' ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fa-solid fa-plus-circle"></i></span>
                            <span class="nav-label">Add Call</span>
                            <span class="nav-tooltip">Add Call</span>
                        </a>
                    @endcan

                    @if (Auth::user()->hasPermission('calls.view'))
                        <a href="{{ route('calls.pending') }}"
                            class="nav-item {{ $cur === 'calls.pending' || ($cur === 'calls.index' && $st === 'pending') ? 'active' : '' }}">
                            <span class="nav-icon text-rose-500"><i class="fa-solid fa-bell-concierge"></i></span>
                            <span class="nav-label">Pending Helps</span>
                            <span class="nav-badge bg-rose-100 text-rose-600 animate-pulse" 
                                  x-show="pendingCount > 0" 
                                  x-text="pendingCount" 
                                  x-transition>!</span>
                            <span class="nav-tooltip">Pending Helps</span>
                        </a>

                        <a href="{{ route('calls.inprogress') }}"
                            class="nav-item {{ $cur === 'calls.inprogress' || ($cur === 'calls.index' && $st === 'in_progress') ? 'active' : '' }}">
                            <span class="nav-icon text-amber-500"><i class="fa-solid fa-bolt-lightning"></i></span>
                            <span class="nav-label">In-Process Helps</span>
                            <span class="nav-tooltip">In-Process Helps</span>
                        </a>

                        <a href="{{ route('calls.index') }}"
                            class="nav-item {{ $cur === 'calls.index' && !in_array($st, ['pending', 'in_progress']) ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fa-solid fa-list-ul"></i></span>
                            <span class="nav-label">All Helps Details</span>
                            <span class="nav-tooltip">All Helps Details</span>
                        </a>
                    @endif

                    @canany(['geography.offices.view', 'geography.carriageways.view', 'users.view'])
                        <!-- OPERATIONS -->
                        <div class="nav-section">Operations</div>
                        <div class="nav-section-divider"></div>

                        @can('users.view')
                            <a href="{{ route('admin.users.index') }}"
                                class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fa-solid fa-user-gear"></i></span>
                                <span class="nav-label">User Management</span>
                                <span class="nav-tooltip">Users</span>
                            </a>
                        @endcan

                        <a href="{{ route('admin.offices.index') }}"
                            class="nav-item {{ request()->routeIs('admin.offices.*') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fa-solid fa-earth-asia"></i></span>
                            <span class="nav-label">Operational Units</span>
                            <span class="nav-tooltip">Field offices</span>
                        </a>

                        <a href="{{ route('admin.carriageways.index') }}"
                            class="nav-item {{ request()->routeIs('admin.carriageways.*') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fa-solid fa-road"></i></span>
                            <span class="nav-label">Highway Registry</span>
                            <span class="nav-tooltip">Highways</span>
                        </a>

                        <a href="{{ route('admin.geospatial-markers.index') }}"
                            class="nav-item {{ request()->routeIs('admin.geospatial-markers.*') ? 'active' : '' }}">
                            <span class="nav-icon text-emerald-500"><i class="fa-solid fa-location-dot"></i></span>
                            <span class="nav-label">Geospatial Markers</span>
                            <span class="nav-tooltip">Map Markers</span>
                        </a>
                    @endcanany

                    @can('reports.view')
                        <!-- REPORTS -->
                        <div class="nav-section">Analytical Reports</div>
                        <div class="nav-section-divider"></div>

                        <a href="{{ route('reports.call-type-summary') }}"
                            class="nav-item {{ request()->routeIs('reports.call-type-summary') ? 'active' : '' }}">
                            <span class="nav-icon text-blue-500"><i class="fa-solid fa-list-check"></i></span>
                            <span class="nav-label">Call Type Summary</span>
                            <span class="nav-tooltip">Categorical</span>
                        </a>

                        <a href="{{ route('reports.agent-wise') }}"
                            class="nav-item {{ request()->routeIs('reports.agent-wise') ? 'active' : '' }}">
                            <span class="nav-icon text-emerald-500"><i class="fa-solid fa-headset"></i></span>
                            <span class="nav-label">Agent Performance</span>
                            <span class="nav-tooltip">Personnel</span>
                        </a>

                        <a href="{{ route('reports.junk-calls-frequency') }}"
                            class="nav-item {{ request()->routeIs('reports.junk-calls-frequency') ? 'active' : '' }}">
                            <span class="nav-icon text-slate-500"><i class="fa-solid fa-ban"></i></span>
                            <span class="nav-label">Junk Calls Frequency</span>
                            <span class="nav-tooltip">Junk/Silent</span>
                        </a>

                        <!-- <a href="{{ route('reports.beat-wise') }}"
                            class="nav-item {{ request()->routeIs('reports.beat-wise') ? 'active' : '' }}">
                            <span class="nav-icon text-indigo-500"><i class="fa-solid fa-map-location-dot"></i></span>
                            <span class="nav-label">Beat Analysis</span>
                            <span class="nav-tooltip">Geospatial</span>
                        </a>
                        <a href="{{ route('reports.sla-compliance') }}"
                            class="nav-item {{ request()->routeIs('reports.sla-compliance') ? 'active' : '' }}">
                            <span class="nav-icon text-rose-500"><i class="fa-solid fa-stopwatch"></i></span>
                            <span class="nav-label">SLA Monitoring</span>
                            <span class="nav-tooltip">Compliance</span>
                        </a>
                        <a href="{{ route('reports.max-response-time') }}"
                            class="nav-item {{ request()->routeIs('reports.max-response-time') ? 'active' : '' }}">
                            <span class="nav-icon text-amber-500"><i class="fa-solid fa-gauge-high"></i></span>
                            <span class="nav-label">Response Benchmarks</span>
                            <span class="nav-tooltip">Outliers</span>
                        </a>
                        <a href="{{ route('reports.predictive-analysis') }}"
                            class="nav-item {{ request()->routeIs('reports.predictive-analysis') ? 'active' : '' }}">
                            <span class="nav-icon text-blue-600"><i class="fa-solid fa-brain"></i></span>
                            <span class="nav-label">Predictive Load</span>
                            <span class="nav-tooltip">Forecasting</span>
                        </a>
                        <a href="{{ route('reports.category-analysis') }}"
                            class="nav-item {{ request()->routeIs('reports.category-analysis') ? 'active' : '' }}">
                            <span class="nav-icon text-teal-500"><i class="fa-solid fa-layer-group"></i></span>
                            <span class="nav-label">Primary vs Secondary</span>
                            <span class="nav-tooltip">Structural</span>
                        </a> -->
                    @endcan

                    @if (Auth::user()->hasPermission('roles.view') ||
                            Auth::user()->hasPermission('system.settings.view'))
                        <!-- ADMINISTRATIVE -->
                        <div class="nav-section">Administrative</div>
                        <div class="nav-section-divider"></div>

                        @can('roles.view')
                            <a href="{{ route('admin.roles.index') }}"
                                class="nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fa-solid fa-fingerprint"></i></span>
                                <span class="nav-label">Roles Management</span>
                                <span class="nav-tooltip">Roles</span>
                            </a>

                            <a href="{{ route('admin.permissions.index') }}"
                                class="nav-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fa-solid fa-key"></i></span>
                                <span class="nav-label">Permission Management</span>
                                <span class="nav-tooltip">Permissions</span>
                            </a>

                            <a href="{{ route('admin.audit.index') }}"
                                class="nav-item {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fa-solid fa-fingerprint"></i></span>
                                <span class="nav-label">Security Audit Logs</span>
                                <span class="nav-tooltip">Security Audit Logs</span>
                            </a>
                        @endcan

                        @can('system.settings.view')
                            <a href="{{ route('admin.settings.index') }}"
                                class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fa-solid fa-sliders"></i></span>
                                <span class="nav-label">System Settings</span>
                                <span class="nav-tooltip">Settings</span>
                            </a>
                        @endcan
                    @endif

                </nav>

                <!-- User Profile Footer -->
                <div class="border-t border-slate-100 p-3 shrink-0">
                    <div class="flex items-center gap-3 px-1">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 flex-1 min-w-0 hover:bg-slate-50 p-1 rounded-xl transition-colors group">
                            <div
                                class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-sm shrink-0 shadow-sm group-hover:scale-110 transition-transform">
                                {{ strtoupper(substr(Auth::user()->username ?? 'U', 0, 1)) }}
                            </div>
                            <div class="flex flex-col min-w-0 sidebar-user-label flex-1">
                                <span
                                    class="text-xs font-black text-navy-900 tracking-tight truncate">{{ strtoupper(str_replace('_', ' ', Auth::user()->username ?? 'nhmp_admin')) }}</span>
                                <span class="text-[8px] font-black text-blue-500 uppercase tracking-widest mt-0.5">{{ Auth::user()?->getRoleTitle() ?? 'System Commander' }}</span>
                            </div>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" data-no-pjax
                            class="sidebar-user-label shrink-0">
                            @csrf
                            <button type="submit"
                                onclick="if(window.SessionGuard) window.SessionGuard.triggerGlobalLogout();"
                                class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-all flex items-center justify-center"
                                title="Logout">
                                <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div><!-- /sidebar-inner -->
        </aside>

        <!-- ============================
             MAIN CONTENT AREA
        ============================ -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">

            <header
                class="h-[64px] bg-white px-5 flex items-center justify-between shrink-0 sticky top-0 z-30 border-b border-slate-100 shadow-[0_1px_0_rgba(0,0,0,0.04)]">
                <div class="flex items-center gap-4">
                    <!-- Mobile hamburger -->
                    <button @click="mobileOpen = true"
                        class="lg:hidden w-9 h-9 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-blue-600 transition-colors flex items-center justify-center">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h2 class="text-base font-black text-navy-900 tracking-tight">@yield('page-title', 'Dashboard')</h2>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden lg:block text-right">
                        <div
                            class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-0.5">
                            System Time</div>
                        <div id="live-time" class="text-[10px] font-mono font-bold text-navy-900 leading-none"></div>
                    </div>
                    <div class="h-6 w-px bg-slate-200"></div>

                    {{-- Voice Activation Protocol --}}
                    <button @click="toggleVoice()" 
                        class="w-10 h-10 rounded-2xl border-2 transition-all flex items-center justify-center relative group"
                        :class="voiceEnabled ? 'bg-emerald-50 border-emerald-100 text-emerald-600 shadow-lg shadow-emerald-500/10' : 'bg-rose-50 border-rose-100 text-rose-500'"
                        :title="voiceEnabled ? 'Mute Voice Alerts' : 'Enable Voice Alerts'">
                        <i class="fa-solid" :class="voiceEnabled ? 'fa-volume-high' : 'fa-volume-xmark'"></i>
                        <template x-if="voiceEnabled">
                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full animate-pulse"></span>
                        </template>
                    </button>
                    <div class="hidden md:flex flex-col items-end leading-none">
                        <span
                            class="text-[11px] font-black text-navy-900 uppercase tracking-tight">{{ strtoupper(str_replace('_', ' ', Auth::user()->username ?? 'nhmp_admin')) }}</span>
                        <span class="text-[7px] font-black text-blue-600 uppercase tracking-widest mt-0.5">{{ Auth::user()?->getRoleTitle() ?? 'System Commander' }}</span>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-white">
                <div class="p-4">
                    @yield('content')
                </div>
            </main>
        </div>

    </div>

    <script>
        function updateLiveTime() {
            const now = new Date();
            const el = document.getElementById('live-time');
            if (el) el.innerHTML = now.toLocaleDateString('en-US', {
                weekday: 'short',
                month: 'short',
                day: 'numeric'
            }) + ' | ' + now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        setInterval(updateLiveTime, 1000);
        updateLiveTime();
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('appLayout', () => ({
                expanded: window.innerWidth >= 1024,
                isMobile: window.innerWidth < 1024,
                mobileOpen: false,
                navigating: false,
                init() {
                    window.addEventListener('resize', () => {
                        const wasMobile = this.isMobile;
                        this.isMobile = window.innerWidth < 1024;
                        if (!this.isMobile) {
                            this.mobileOpen = false;
                            if (wasMobile) this.expanded = true;
                        }
                    });

                    window.addEventListener('bento:page-loading', () => {
                        this.navigating = true;
                    });
                    
                    window.addEventListener('bento:page-loaded', () => {
                        this.navigating = false;
                    });

                    // Optimistic UI for instant 3D active state on click
                    const navItems = document.querySelectorAll('.nav-item');
                    navItems.forEach(item => {
                        item.addEventListener('click', function() {
                            navItems.forEach(n => n.classList.remove('active'));
                            this.classList.add('active');
                        });
                    });
                }
            }));

            // Operational Intelligence: Heartbeat for Real-Time Trace Notifications
            Alpine.data('heartbeatManager', () => ({
                lastCheck: new Date().toISOString(),
                interval: null,
                activeUtterance: null, // Essential: Prevent garbage collection during speech
                pollFrequency: 20000, // 20 Seconds
                allowedRoles: ['beat_operator', 'sector_admin', 'zone_admin', 'agent_supervisor', 'super_admin'],
                userRole: '{{ auth()->user()->role?->name }}',
                pendingCount: 0,
                voiceEnabled: localStorage.getItem('nhmp_voice_enabled') === 'true',

                init() {
                    // Efficiency Protocol: Only specific regional roles initiate the heartbeat
                    if (!this.allowedRoles.includes(this.userRole)) {
                        return;
                    }

                    // Slight delay to allow system stabilization before first poll
                    setTimeout(() => {
                        this.poll();
                        this.interval = setInterval(() => this.poll(), this.pollFrequency);
                    }, 5000);
                },

                toggleVoice() {
                    this.voiceEnabled = !this.voiceEnabled;
                    localStorage.setItem('nhmp_voice_enabled', this.voiceEnabled);
                    
                    if (this.voiceEnabled) {
                        // Protocol Activation: Browser requires user activation to 'unlock' audio
                        const unlock = new SpeechSynthesisUtterance('Voice alerts activated.');
                        unlock.volume = 0.5;
                        unlock.rate = 1.0;
                        window.speechSynthesis.cancel(); // Clear any stalled speech
                        window.speechSynthesis.speak(unlock);
                        
                        if (window.showSuccess) window.showSuccess('Voice alerts activated.', 'Voice Protocol Enabled');
                    } else {
                        window.speechSynthesis.cancel();
                        if (window.showInfo) window.showInfo('Voice alerts disabled.', 'Voice Protocol Disabled');
                    }
                },

                async poll() {
                    try {
                        const response = await fetch(`/api/notifications/poll?since=${encodeURIComponent(this.lastCheck)}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        if (response.status === 403) {
                            console.warn('Heartbeat: Access denied (403). Polling suspended.');
                            if (this.interval) clearInterval(this.interval);
                            return;
                        }

                        const data = await response.json();

                        if (data.new_calls && data.new_calls.length > 0) {
                            data.new_calls.forEach(call => {
                                this.dispatchAlert(call);
                            });
                        }
                        
                        if (typeof data.pending_count !== 'undefined') {
                            this.pendingCount = data.pending_count;
                        }
                        
                        if (data.timestamp) {
                            this.lastCheck = data.timestamp;
                        }
                    } catch (e) {
                        console.error('Heartbeat sync failed:', e);
                    }
                },

                dispatchAlert(call) {
                    const category = call.call_type ? call.call_type.name : 'Unknown';
                    const isReminder = call.last_reminder_at && (!call.created_at || new Date(call.last_reminder_at) > new Date(call.created_at));
                    
                    const title = isReminder 
                        ? `🚨 REPEAT REMINDER: ${call.call_number}`
                        : `New Help Acquisition: ${call.call_number}`;
                    
                    const message = isReminder
                        ? `${category}: URGENT REMINDER for ${call.caller_name || 'Anonymous'}. Trace is still pending.`
                        : `${category}: ${call.caller_name || 'Anonymous Entity'} reported.`;

                    // UI Alert Notification (Premium System)
                    if (window.showWarning) {
                        window.showWarning(message, title);
                    } else if (window.Notification && window.Notification.warning) {
                        window.Notification.warning(message, title);
                    }

                    // Voice Alarm (Advanced Protocol)
                    if ('speechSynthesis' in window && this.voiceEnabled) {
                        try {
                            const voiceText = isReminder
                                ? `Attention. Urgent reminder. ${category} help acquisition is still pending in the command grid.`
                                : `Attention. New ${category} help acquisition recorded in the command grid.`;
                            
                            const utterance = new SpeechSynthesisUtterance(voiceText);
                            this.activeUtterance = utterance; // Store reference
                            utterance.rate = 1.0; // Standard rate for less delay
                            utterance.pitch = 1.0;
                            
                            // Essential: Clear queue and speak immediately
                            window.speechSynthesis.cancel(); 
                            setTimeout(() => {
                                window.speechSynthesis.speak(utterance);
                            }, 50); // Microsurgical delay for engine reset
                        } catch (err) {
                            console.warn('Voice alarm suppressed by browser security policy.');
                        }
                    }
                }
            }));
        });
    </script>

    @stack('scripts')
</body>

</html>
