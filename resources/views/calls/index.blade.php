@extends('layouts.app')

@section('title', 'Help Management - NHMP 130')

@section('page-title', 'Help Management')

@section('content')

<script>
    window._indexData = {!! json_encode([
        'items' => $calls->items(),
        'last_page' => $calls->lastPage(),
        'total' => $calls->total(),
        'stats' => $stats,
        'zones' => $zones,
        'sectors' => $sectors,
        'beats' => $beats,
        'callTypes' => $callTypes,
        'callSubTypes' => $callSubTypes,
        'agents' => $agents,
        'status' => request('status', 'all'),
        'userRole' => auth()->user()->role?->name,
        'userScope' => auth()->user()->activeScopes->map(fn($s) => [
            'type' => $s->office?->type ?? 'national',
            'id' => $s->office_id
        ])->first(),
        'permissions' => [
            'create' => auth()->user()->hasPermission('calls.create'),
            'calls' => [
                'manage_status' => auth()->user()->hasPermission('calls.manage_status'),
                'update' => auth()->user()->hasPermission('calls.update'),
                'delete' => auth()->user()->hasPermission('calls.delete'),
            ],
            'reports' => [
                'view' => auth()->user()->hasPermission('reports.view'),
            ]
        ]
    ]) !!};
</script>

<style>
    .condensed-table td, .condensed-table th {
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }
    .custom-scrollbar::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<div x-data="callManager(window._indexData)" 
     class="space-y-6 relative mt-0 max-w-[1700px] mx-auto" 
     x-cloak>
        <!-- Floating Sidebar Toggle -->
        <button @click="showSidebar = true" 
            x-show="!showSidebar"
            x-transition:enter="transition ease-out duration-500 delay-100"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            class="fixed bottom-20 right-0 z-40 bg-gradient-to-b from-blue-600 to-blue-900 text-white p-2.5 py-6 rounded-l-2xl shadow-[0_0_30px_-5px_rgba(30,58,138,0.4)] hover:shadow-[-5px_0_40px_-5px_rgba(30,58,138,0.7)] hover:pr-4 transition-all duration-300 flex flex-col items-center gap-4 cursor-pointer" title="Open Intelligence Filters">
            <i class="fas fa-sliders-h drop-shadow-lg text-sm"></i>
            <span style="writing-mode: vertical-rl;" class="text-[9px] font-black uppercase tracking-[0.3em] rotate-180 drop-shadow-md text-blue-50">Help Filters</span>
        </button>



        <!-- STATS CARDS -->
        <!-- STATUS STATS -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-6">
            <div @click="filterStatus = 'all'; filterType = ''; triggerFetch()" :class="filterStatus === 'all' && !filterType ? 'card-3d-active blue' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer group">
                <div class="absolute -top-4 left-6 h-12 w-12 grid place-items-center rounded-xl bg-blue-600 shadow-[0_8px_16px_rgba(37,99,235,0.2)] z-10">
                    <i class="fa-solid fa-layer-group text-sm text-white"></i>
                </div>
                <div class="p-4 text-right pt-2 relative">
                    <p class="text-[9px] font-black tracking-widest text-slate-400 uppercase">Total Directory</p>
                    <div class="flex items-baseline justify-end gap-2 mt-1">
                        <h4 class="text-3xl font-extrabold text-blue-900" x-text="stats.total"></h4>
                    </div>
                </div>
            </div>

            <div @click="filterStatus = 'pending'; filterType = ''; triggerFetch()" :class="filterStatus === 'pending' ? 'card-3d-active rose' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer group">
                <div class="absolute -top-4 left-6 h-12 w-12 grid place-items-center rounded-xl bg-rose-600 shadow-[0_8px_16px_rgba(225,29,72,0.2)] z-10">
                    <i class="fa-solid fa-hourglass-start text-sm text-white"></i>
                </div>
                <div class="p-4 text-right pt-2 relative">
                    <p class="text-[9px] font-black tracking-widest text-rose-500 uppercase">Pending Ops</p>
                    <div class="flex items-center justify-end gap-2 mt-1">
                        <span class="text-[10px] font-black text-white bg-rose-600 px-2 py-0.5 rounded-full shadow-sm" x-text="stats.pending_percent + '%'"></span>
                        <h4 class="text-3xl font-extrabold text-blue-900" x-text="stats.pending"></h4>
                    </div>
                </div>
            </div>

            <div @click="filterStatus = 'in_progress'; filterType = ''; triggerFetch()" :class="filterStatus === 'in_progress' ? 'card-3d-active amber' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer group">
                <div class="absolute -top-4 left-6 h-12 w-12 grid place-items-center rounded-xl bg-amber-500 shadow-[0_8px_16px_rgba(245,158,11,0.2)] z-10">
                    <i class="fa-solid fa-person-running text-sm text-white"></i>
                </div>
                <div class="p-4 text-right pt-2 relative">
                    <p class="text-[9px] font-black tracking-widest text-amber-500 uppercase">Active Field</p>
                    <div class="flex items-center justify-end gap-2 mt-1">
                        <span class="text-[10px] font-black text-white bg-amber-500 px-2 py-0.5 rounded-full shadow-sm" x-text="stats.in_progress_percent + '%'"></span>
                        <h4 class="text-3xl font-extrabold text-blue-900" x-text="stats.in_progress"></h4>
                    </div>
                </div>
            </div>

            <div @click="filterStatus = 'completed'; filterType = ''; triggerFetch()" :class="filterStatus === 'completed' ? 'card-3d-active emerald' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer group">
                <div class="absolute -top-4 left-6 h-12 w-12 grid place-items-center rounded-xl bg-emerald-600 shadow-[0_8px_16px_rgba(16,185,129,0.2)] z-10">
                    <i class="fa-solid fa-check-double text-sm text-white"></i>
                </div>
                <div class="p-4 text-right pt-2 relative">
                    <p class="text-[9px] font-black tracking-widest text-emerald-500 uppercase">Resolved Units</p>
                    <div class="flex items-center justify-end gap-2 mt-1">
                        <span class="text-[10px] font-black text-white bg-emerald-600 px-2 py-0.5 rounded-full shadow-sm" x-text="stats.completed_percent + '%'"></span>
                        <h4 class="text-3xl font-extrabold text-blue-900" x-text="stats.completed"></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- CATEGORY FILTERS (LOGICAL COLORS) -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 mt-6">
            <template x-for="cat in stats.categories" :key="cat.id">
                <div @click="filterType = cat.id; filterStatus = 'all'; triggerFetch()" 
                     :class="filterType == cat.id ? 'card-3d-active ' + (
                        cat.name.toLowerCase().includes('emergency') ? 'rose' : 
                        cat.name.toLowerCase().includes('complaint') ? 'amber' : 
                        cat.name.toLowerCase().includes('information') ? 'sky' : 
                        cat.name.toLowerCase().includes('crime') ? 'indigo' : 
                        cat.name.toLowerCase().includes('junk') ? 'slate' : 'blue'
                     ) : 'hover:border-blue-200'"
                     class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-[0_10px_30px_rgba(0,0,0,0.02)] transition-all duration-300 cursor-pointer relative group overflow-hidden">
                    <div class="flex justify-between items-start mb-4 relative z-10">
                        <div class="h-10 w-10 rounded-xl flex items-center justify-center text-white shadow-lg"
                             :class="
                                cat.name.toLowerCase().includes('emergency') ? 'bg-rose-600 shadow-rose-200' : 
                                cat.name.toLowerCase().includes('complaint') ? 'bg-amber-500 shadow-amber-200' : 
                                cat.name.toLowerCase().includes('information') ? 'bg-sky-500 shadow-sky-200' : 
                                cat.name.toLowerCase().includes('crime') ? 'bg-indigo-900 shadow-indigo-200' : 
                                cat.name.toLowerCase().includes('junk') ? 'bg-slate-500 shadow-slate-200' : 'bg-blue-600 shadow-blue-200'
                             ">
                            <i :class="'fas ' + cat.icon + ' text-xs'"></i>
                        </div>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-lg text-white" 
                              :class="
                                cat.name.toLowerCase().includes('emergency') ? 'bg-rose-600' : 
                                cat.name.toLowerCase().includes('complaint') ? 'bg-amber-500' : 
                                cat.name.toLowerCase().includes('information') ? 'bg-sky-500' : 
                                cat.name.toLowerCase().includes('crime') ? 'bg-indigo-900' : 
                                cat.name.toLowerCase().includes('junk') ? 'bg-slate-500' : 'bg-blue-600'
                              "
                              x-text="cat.percent + '%'"></span>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest truncate mb-1" x-text="cat.name"></p>
                    <h5 class="text-3xl font-black text-blue-900" x-text="cat.count"></h5>
                </div>
            </template>
        </div>

        <!-- MAIN CONTROL PANEL -->
        <div class="grid lg:grid-cols-12 gap-8 relative pb-20">
            <div class="transition-all duration-500" :class="showSidebar ? 'lg:col-span-9' : 'lg:col-span-12'">
                <div class="bg-white rounded-[2rem] shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-gray-100 overflow-hidden flex flex-col relative z-20 mt-8">


                    <!-- Grid View -->
                    <div x-show="viewMode === 'grid'" x-transition class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                            <template x-for="item in pagedItems" :key="item.id">
                                <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8 hover:-translate-y-2 transition-all duration-300 group relative overflow-hidden flex flex-col justify-between">
                                    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                                        <i class="fa-solid fa-headset text-9xl text-blue-900"></i>
                                    </div>
                                    
                                    <div>
                                        <div class="flex items-start justify-between mb-8 relative z-10">
                                            <div class="w-14 h-14 rounded-2xl bg-slate-50 border-2 border-slate-100 flex items-center justify-center text-blue-900 shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-all duration-500">
                                                <i class="fa-solid fa-satellite-dish text-xl"></i>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase mb-1">Status</div>
                                                <div x-html="getStatusBadge(item.status)"></div>
                                            </div>
                                        </div>

                                        <div class="relative z-10">
                                            <h4 class="text-2xl font-extrabold text-blue-900 tracking-tighter leading-none mb-1 group-hover:text-blue-600 transition-colors" x-text="formatTitleCase(item.caller_name)"></h4>
                                            <span class="text-xs font-black text-blue-900 uppercase" x-text="item.caller_number"></span>
                                            <div class="flex items-center gap-2 mb-6">
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest" x-text="'Time: ' + formatTime(item.created_at)"></span>
                                                <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                                                <span class="text-[9px] font-black text-blue-500 uppercase tracking-widest" x-text="'Date: ' + formatDate(item.created_at)"></span>
                                            </div>

                                            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 mb-8">
                                                <div class="space-y-4">
                                                    <div class="flex flex-col">
                                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Category</span>
                                                        <span class="text-xs font-black text-blue-900 uppercase" x-text="item.call_type?.name || 'UNCATEGORIZED'"></span>
                                                        <span class="text-[10px] font-bold text-slate-400 italic" x-text="item.call_sub_type?.name || 'GENERAL RESOLUTION'"></span>
                                                    </div>
                                                    <div class="flex flex-col pt-4 border-t border-slate-200/50">
                                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Beat Assigned: </span>
                                                        <div class="flex items-center gap-2 text-[10px] font-bold text-blue-900">
                                                            <i class="fa-solid fa-map-pin text-blue-400"></i>
                                                            <span x-text="getLocationHierarchy(item)"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-5 border-t border-slate-100 space-y-3 relative z-10">
                                        <!-- Asset row -->
                                        <div class="flex items-center justify-between">
                                            <div x-html="getPriorityBadge(item.priority)"></div>
                                        </div>

                                        <!-- Action Grid -->
                                        <div class="grid grid-cols-4 gap-2">
                                            <!-- View Details -->
                                            <button @click="openDetailModal(item)" title="View Intelligence" class="w-9 h-9 bg-blue-500 border border-blue-600 text-white hover:bg-blue-600 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                <i class="fas fa-eye text-xs"></i>
                                            </button>

                                            <!-- Contextual Action -->
                                            <template x-if="item.status.toLowerCase() === 'pending' && (item.beat_id || item.office_id) && permissions.calls.manage_status">
                                                <button @click="openTransitionModal(item, 'in_progress')" title="Initialize Mission" class="w-9 h-9 rounded-xl bg-amber-500 border border-amber-600 text-white hover:bg-amber-600 active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
                                                    <i class="fa-solid fa-bolt text-xs"></i>
                                                </button>
                                            </template>
                                            <template x-if="item.status.toLowerCase() === 'in_progress' && (item.beat_id || item.office_id) && permissions.calls.manage_status">
                                                <button @click="openTransitionModal(item, 'completed')" title="Resolve Acquisition" class="w-9 h-9 rounded-xl bg-emerald-600 border border-emerald-700 text-white hover:bg-emerald-600 active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
                                                    <i class="fa-solid fa-check text-xs"></i>
                                                </button>
                                            </template>

                                            <!-- Agent/Supervisor Reminder -->
                                            <template x-if="['agent', 'agent_supervisor', 'super_admin'].includes(userRole) && ['pending', 'in_progress'].includes(item.status) && item.office_id">
                                                <button @click="openReminderModal(item)" title="Send Reminder" class="w-9 h-9 bg-emerald-500 border border-emerald-600 text-white hover:bg-emerald-600 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                    <i class="fas fa-bell text-xs"></i>
                                                </button>
                                            </template>

                                            <!-- Locked State -->
                                            <template x-if="['completed', 'forwarded', 'junk'].includes(item.status)">
                                                <div class="w-9 h-9 bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center rounded-xl opacity-50 cursor-not-allowed mx-auto aspect-square shrink-0">
                                                    <i class="fas fa-lock text-xs"></i>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Table View -->
                    <div x-show="viewMode === 'table'" x-transition class="overflow-x-auto shadow-inner bg-white rounded-3xl border border-slate-100 custom-scrollbar">
                        <table class="w-full text-left min-w-[1400px] border-collapse" :class="density === 'condensed' ? 'condensed-table' : 'spacious-table'">
                            <thead class="bg-blue-50 border-b border-blue-100">
                                <tr>
                                    <th class="px-3 py-3">
                                        <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                                <i class="fa-solid fa-satellite-dish text-[10px]"></i>
                                            </div>
                                            <button @click="sortByItem('call_number')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">
                                                ID
                                                <i class="fas text-[10px] transition-all duration-300 opacity-0 group-hover:opacity-100 fa-sort text-blue-600" :class="getSortIcon('call_number')"></i>
                                            </button>
                                        </div>
                                    </th>
                                    @if(!in_array(auth()->user()->role?->name, ['zone_admin', 'sector_admin', 'beat_operator']))
                                    <th class="px-2 py-3">
                                        <div class="flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                                <i class="fa-solid fa-user-tie text-[10px]"></i>
                                            </div>
                                            <button @click="sortByItem('agent_id')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">
                                                Agent
                                                <i class="fas text-[10px] transition-all duration-300 opacity-0 group-hover:opacity-100 fa-sort text-blue-600" :class="getSortIcon('agent_id')"></i>
                                            </button>
                                        </div>
                                    </th>
                                    @endif
                                    <th class="px-2 py-3">
                                        <div class="flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                                <i class="fa-solid fa-tags text-[10px]"></i>
                                            </div>
                                            <button @click="sortByItem('call_type_id')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">
                                                Category
                                                <i class="fas text-[10px] transition-all duration-300 opacity-0 group-hover:opacity-100 fa-sort text-blue-600" :class="getSortIcon('call_type_id')"></i>
                                            </button>
                                        </div>
                                    </th>
                                    <th class="px-2 py-3">
                                        <div class="flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                                <i class="fa-solid fa-road text-[10px]"></i>
                                            </div>
                                            <button @click="sortByItem('beat_id')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">
                                                Zone / Sector / Beat
                                                <i class="fas text-[10px] transition-all duration-300 opacity-0 group-hover:opacity-100 fa-sort text-blue-600" :class="getSortIcon('beat_id')"></i>
                                            </button>
                                        </div>
                                    </th>
                                    <th class="px-2 py-3">
                                        <div class="flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                                <i class="fa-solid fa-user text-[10px]"></i>
                                            </div>
                                            <button @click="sortByItem('caller_name')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">
                                                Caller Info
                                                <i class="fas text-[10px] transition-all duration-300 opacity-0 group-hover:opacity-100 fa-sort text-blue-600" :class="getSortIcon('caller_name')"></i>
                                            </button>
                                        </div>
                                    </th>

                                    <th class="px-3 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] group cursor-pointer" @click="sortByItem('vehicle_no')">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-emerald-100">
                                                <i class="fa-solid fa-car text-[10px]"></i>
                                            </div>
                                            <span>Vehicle Info<i class="fas text-[10px] transition-all duration-300 opacity-0 group-hover:opacity-100 fa-sort text-blue-600" :class="getSortIcon('vehicle_no')"></i></span>
                                        </div>
                                    </th>
                                    <th class="px-3 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                                <i class="fa-solid fa-flag text-[10px]"></i>
                                            </div>
                                            <span class="cursor-pointer group flex items-center gap-1.5" @click="sortByItem('priority')">Priority <i class="fas text-[10px] transition-all duration-300 opacity-0 group-hover:opacity-100 fa-sort text-blue-600" :class="getSortIcon('priority')"></i></span>
                                        </div>
                                    </th>
                                    <th class="px-3 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                                <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                            </div>
                                            <span class="cursor-pointer group flex items-center gap-1.5" @click="sortByItem('status')">Status <i class="fas text-[10px] transition-all duration-300 opacity-0 group-hover:opacity-100 fa-sort text-blue-600" :class="getSortIcon('status')"></i></span>
                                        </div>
                                    </th>
                                    <th class="px-3 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                                <i class="fa-solid fa-bolt text-[10px]"></i>
                                            </div>
                                            <span>Actions</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <template x-for="item in pagedItems" :key="item.id">
                                    <tr class="hover:bg-blue-50/40 transition-colors group cursor-pointer border-l-4 border-transparent hover:border-blue-600" @click="window.location='/calls/' + item.id">
                                        <td class="px-2 py-3">
                                            <div class="flex flex-col gap-1">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-black text-blue-900 text-xs tracking-tighter uppercase group-hover:text-blue-600 transition-colors" x-text="item.call_number"></span>
                                                    <template x-if="item.call_reminder_count > 0">
                                                        <span class="px-1.5 py-0.5 rounded-full bg-rose-100 text-rose-600 text-[8px] font-black border border-rose-200" x-text="item.call_reminder_count"></span>
                                                    </template>
                                                </div>
                                                <div class="flex items-center gap-2 opacity-70">
                                                    <span class="text-[8px] font-bold text-slate-500 uppercase tracking-widest" x-text="formatTime(item.created_at)"></span>
                                                    <span class="text-[8px] font-bold text-blue-600 uppercase tracking-widest" x-text="formatDate(item.created_at)"></span>
                                                </div>
                                                <template x-if="item.last_reminder_at">
                                                    <div class="flex items-center gap-1">
                                                        <i class="fa-solid fa-bell text-[7px] text-rose-500 animate-pulse"></i>
                                                        <span class="text-[7px] font-black text-rose-600 uppercase tracking-tight" x-text="'Reminded: ' + formatTime(item.last_reminder_at)"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                        @if(!in_array(auth()->user()->role?->name, ['zone_admin', 'sector_admin', 'beat_operator']))
                                        <td class="px-2 py-3">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-black text-blue-900 uppercase" x-text="item.agent?.full_name || 'System'"></span>
                                                <span class="text-[9px] font-bold text-slate-400 uppercase italic">Call center Agent</span>
                                            </div>
                                        </td>
                                        @endif
                                        <td class="px-2 py-3">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-black text-blue-900 uppercase" x-text="item.call_type?.name || 'UNCATEGORIZED'"></span>
                                                <span class="text-[9px] font-bold text-slate-400 uppercase italic" x-text="item.call_sub_type?.name || 'GENERAL'"></span>
                                            </div>
                                        </td>
                                        <td class="px-2 py-3">
                                            <div class="flex flex-col">
                                                <div class="flex items-center gap-2 text-[10px] font-bold text-blue-900">
                                                    <i class="fa-solid fa-map-pin text-[8px] text-blue-400"></i>
                                                    <span x-text="getLocationHierarchy(item)"></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2 py-3">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-black text-blue-900 uppercase" x-text="formatTitleCase(item.caller_name) || 'Anonymous'"></span>
                                                <span class="text-[10px] font-black text-blue-900 uppercase tracking-widest" x-text="item.caller_number"></span>
                                            </div>
                                        </td>

                                        <td class="px-3 py-3 text-center">
                                            <div class="flex flex-col items-center">
                                                <span class="text-xs font-black text-blue-900 uppercase" x-text="item.vehicle_no || '---'"></span>
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5" x-text="item.vehicle_type?.name || '---'"></span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <div x-html="getPriorityBadge(item.priority, true)"></div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <div x-html="getStatusBadge(item.status)"></div>
                                            </div>
                                        </td>
                                        <td class="px-2 py-3" @click.stop>
                                            <div class="flex items-center justify-center gap-1.5 w-[120px] mx-auto">
                                                <!-- View Details -->
                                                <button @click="openDetailModal(item)" title="View Intelligence" class="w-9 h-9 bg-blue-500 border border-blue-600 text-white hover:bg-blue-600 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </button>

                                                <!-- Initialize Mission (Pending -> In Progress) -->
                                                <template x-if="item.status.toLowerCase() === 'pending' && (item.beat_id || item.office_id) && permissions.calls.manage_status">
                                                    <button @click="openTransitionModal(item, 'in_progress')" title="Initialize Mission" class="w-9 h-9 bg-amber-500 border border-amber-600 text-white hover:bg-amber-600 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                        <i class="fas fa-bolt text-xs"></i>
                                                    </button>
                                                </template>

                                                <!-- Resolve Help (In Progress -> Completed) -->
                                                <template x-if="item.status.toLowerCase() === 'in_progress' && (item.beat_id || item.office_id) && permissions.calls.manage_status">
                                                    <button @click="openTransitionModal(item, 'completed')" title="Resolve Acquisition" class="w-9 h-9 bg-emerald-600 border border-emerald-700 text-white hover:bg-emerald-600 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </button>
                                                </template>

                                                <!-- Agent/Supervisor Reminder -->
                                                <template x-if="['agent', 'agent_supervisor', 'super_admin'].includes(userRole) && ['pending', 'in_progress'].includes(item.status) && item.office_id">
                                                    <button @click="openReminderModal(item)" title="Send Reminder" class="w-9 h-9 bg-emerald-500 border border-emerald-600 text-white hover:bg-emerald-600 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                        <i class="fas fa-bell text-xs"></i>
                                                    </button>
                                                </template>

                                                <!-- Locked State -->
                                                <template x-if="['completed', 'forwarded', 'junk'].includes(item.status)">
                                                    <div class="w-9 h-9 bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center rounded-xl opacity-50 cursor-not-allowed mx-auto aspect-square shrink-0">
                                                        <i class="fas fa-lock text-xs"></i>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div x-show="totalPages > 1" class="p-8 border-t border-slate-100 bg-slate-50/30 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs font-bold text-slate-400">
                            Showing <span class="text-blue-900" x-text="((page - 1) * perPage) + 1"></span> to <span class="text-blue-900" x-text="Math.min(page * perPage, totalRecords)"></span> of <span class="text-blue-900" x-text="totalRecords"></span> Trace Entities
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- First/Next -->
                            <button @click="page = 1" :disabled="page === 1" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-400 disabled:opacity-30 hover:bg-slate-50 transition-all font-black text-[10px] uppercase">First</button>
                            <button @click="page++" :disabled="page === totalPages" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition-all"><i class="fa-solid fa-chevron-right"></i></button>
                            
                            <!-- Sliding Window -->
                            <template x-for="p in pagesToShow()" :key="p">
                                <div class="flex items-center">
                                    <template x-if="p === '...'">
                                        <span class="px-2 text-slate-300 font-bold">...</span>
                                    </template>
                                    <template x-if="p !== '...'">
                                        <button @click="page = p" :class="page === p ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/30' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" class="w-10 h-10 rounded-xl border font-black text-xs transition-all" x-text="p"></button>
                                    </template>
                                </div>
                            </template>

                            <!-- Prev/Last -->
                            <button @click="page--" :disabled="page === 1" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition-all"><i class="fa-solid fa-chevron-left"></i></button>
                            <button @click="page = totalPages" :disabled="page === totalPages" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-400 disabled:opacity-30 hover:bg-slate-50 transition-all font-black text-[10px] uppercase">Last</button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div x-show="totalRecords === 0" class="py-32 text-center bg-white">
                        <i class="fa-solid fa-satellite-dish text-6xl text-blue-100 mb-6 animate-pulse"></i>
                        <h3 class="text-2xl font-black text-blue-900">Zero Assistance Presence</h3>
                        <p class="text-slate-400 font-bold mt-2">No helps found matching the specified filter parameters.</p>
                        @can('calls.create')
                        <a href="{{ route('calls.create') }}" class="inline-flex items-center gap-2 mt-8 px-8 py-4 bg-blue-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-blue-600/20 hover:-translate-y-1 transition-all">
                            Initiate New Help
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Right Column - Intelligence Filters -->
            <div class="lg:col-span-3 pb-8 transition-all duration-300" x-show="showSidebar" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-4" x-cloak>
                <div class="lg:sticky lg:top-4 lg:max-h-[calc(100vh-100px)] lg:overflow-y-auto bg-white rounded-[2rem] shadow-xl border border-slate-100 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-blue-600 shadow-sm">
                                <i class="fas fa-filter"></i>
                            </div>
                            <div>
                                <h2 class="font-extrabold text-blue-900 text-lg tracking-tight">Intelligence Filters</h2>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Control Panel</p>
                            </div>
                        </div>
                        <button @click="showSidebar = false" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-900 shadow-sm flex items-center justify-center">
                            <i class="fas fa-angle-right"></i>
                        </button>
                    </div>

                    <div class="p-4 border-b border-slate-100 bg-blue-50/30 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Interface Mode</label>
                            <div class="flex items-center bg-white p-1 rounded-xl border border-slate-100 shadow-sm">
                                <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400'" class="w-8 h-8 flex items-center justify-center rounded-lg transition-all">
                                    <i class="fas fa-list-ul text-xs"></i>
                                </button>
                                <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400'" class="w-8 h-8 flex items-center justify-center rounded-lg transition-all">
                                    <i class="fas fa-th-large text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6 w-full flex-1">
                        <!-- Search -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-search text-blue-500"></i> Search
                            </label>
                            <div class="relative">
                                <input type="text" x-model="search" placeholder="Search ID/Caller/Vehicle..." class="w-full pl-11 pr-4 py-3 bg-white border-2 border-slate-100 rounded-xl focus:border-blue-600 outline-none font-bold text-blue-900 text-xs transition-all focus:shadow-lg focus:shadow-blue-500/10">
                                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            </div>
                        </div>

                        
                        <!-- Agent Filter -->
                        <template x-if="agents.length > 0">
                            <div class="space-y-4 pt-4 border-t border-slate-100">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                    <i class="fa-solid fa-user-tie text-blue-500"></i> Responsible Agent
                                </label>
                                <select x-model="filterAgent" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner focus:border-blue-500 transition-all">
                                    <option value="">All Agents</option>
                                    <template x-for="a in agents" :key="a.id">
                                        <option :value="a.id" x-text="a.username"></option>
                                    </template>
                                </select>
                            </div>
                        </template>

                        <!-- Date Range Filters -->
                        <div class="space-y-4 pt-4 border-t border-slate-100">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fa-regular fa-calendar-days text-indigo-500"></i> Date & Time Range
                            </label>
                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest block mb-1">Date From</label>
                                        <input type="date" x-model="dateFrom" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner focus:border-indigo-500 transition-all">
                                    </div>
                                    <div>
                                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest block mb-1">Time From</label>
                                        <input type="time" x-model="timeFrom" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner focus:border-indigo-500 transition-all">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest block mb-1">Date To</label>
                                        <input type="date" x-model="dateTo" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner focus:border-indigo-500 transition-all">
                                    </div>
                                    <div>
                                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest block mb-1">Time To</label>
                                        <input type="time" x-model="timeTo" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner focus:border-indigo-500 transition-all">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="space-y-4 pt-4 border-t border-slate-100">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-shield-virus text-emerald-500"></i> Status
                            </label>
                            <div class="grid grid-cols-1 gap-2">
                                <button @click="filterStatus = 'all'" :class="filterStatus === 'all' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-2 border-slate-100'" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-left flex items-center justify-between group">
                                    <span>Total Helps</span>
                                    <i class="fas fa-globe-americas transition-opacity" :class="filterStatus === 'all' ? 'opacity-100' : 'opacity-40'"></i>
                                </button>
                                <button @click="filterStatus = 'pending'" :class="filterStatus === 'pending' ? 'bg-rose-600 text-white shadow-md' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-2 border-slate-100'" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-left flex items-center justify-between group">
                                    <span>Pending Only</span>
                                    <i class="fas fa-clock transition-opacity" :class="filterStatus === 'pending' ? 'opacity-100' : 'opacity-40'"></i>
                                </button>
                                <button @click="filterStatus = 'in_progress'" :class="filterStatus === 'in_progress' ? 'bg-amber-600 text-white shadow-md' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-2 border-slate-100'" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-left flex items-center justify-between group">
                                    <span>In-Process</span>
                                    <i class="fas fa-bolt transition-opacity" :class="filterStatus === 'in_progress' ? 'opacity-100' : 'opacity-40'"></i>
                                </button>
                                <button @click="filterStatus = 'completed'" :class="filterStatus === 'completed' ? 'bg-emerald-600 text-white shadow-md' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-2 border-slate-100'" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-left flex items-center justify-between group">
                                    <span>Completed</span>
                                    <i class="fas fa-check-double transition-opacity" :class="filterStatus === 'completed' ? 'opacity-100' : 'opacity-40'"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Taxonomy Matrix -->
                        <div class="space-y-4 pt-4 border-t border-slate-100">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fa-solid fa-tags text-blue-500"></i> Category
                            </label>
                            <div class="space-y-3">
                                <select x-model="filterType" @change="filterSubType = ''" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner focus:border-blue-500 transition-all">
                                    <option value="">Primary Category</option>
                                    <template x-for="t in callTypes" :key="t.id">
                                        <option :value="t.id" x-text="t.name"></option>
                                    </template>
                                </select>
                                <select x-model="filterSubType" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner focus:border-blue-500 transition-all">
                                    <option value="">Secondary Sub-type</option>
                                    <template x-for="st in filteredSubTypesInFilter" :key="st.id">
                                        <option :value="st.id" x-text="st.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Geography Filters -->
                        <div class="space-y-4 pt-4 border-t border-slate-100">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fa-solid fa-earth-asia text-indigo-500"></i> Zone, Sector, Beat
                            </label>
                            <div class="space-y-3">
                                <!-- Zone Selector -->
                                <div class="space-y-1">
                                    <select x-model="filterZone" @change="filterSector = ''; filterBeat = ''" 
                                        :disabled="userScope && !['national', 'call_centre'].includes(userScope.type) && !['super_admin', 'agent_supervisor', 'agent'].includes(userRole)"
                                        class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner focus:border-indigo-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                        <option value="">Global Zones</option>
                                        <template x-for="z in zones" :key="z.id">
                                            <option :value="z.id" x-text="z.name"></option>
                                        </template>
                                    </select>
                                    <template x-if="userScope && !['national', 'call_centre'].includes(userScope.type) && !['super_admin', 'agent_supervisor', 'agent'].includes(userRole)">
                                        <p class="text-[8px] font-bold text-indigo-400 uppercase tracking-widest ml-4 italic">Zone Selected</p>
                                    </template>
                                </div>

                                <!-- Sector Selector -->
                                <div class="space-y-1">
                                    <select x-model="filterSector" @change="filterBeat = ''" 
                                        :disabled="userScope && ['sector', 'beat'].includes(userScope.type) && !['super_admin', 'agent_supervisor', 'agent'].includes(userRole)"
                                        class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner focus:border-indigo-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                        <option value="">All Sectors</option>
                                        <template x-for="s in filteredSectors" :key="s.id">
                                            <option :value="s.id" x-text="s.name"></option>
                                        </template>
                                    </select>
                                    <template x-if="userScope && ['sector', 'beat'].includes(userScope.type) && !['super_admin', 'agent_supervisor', 'agent'].includes(userRole)">
                                        <p class="text-[8px] font-bold text-indigo-400 uppercase tracking-widest ml-4 italic">Sector Selected</p>
                                    </template>
                                </div>

                                <!-- Beat Selector -->
                                <div class="space-y-1">
                                    <select x-model="filterBeat" 
                                        :disabled="userScope && userScope.type === 'beat' && !['super_admin', 'agent_supervisor', 'agent'].includes(userRole)"
                                        class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner focus:border-indigo-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                        <option value="">Select Beat</option>
                                        <template x-for="b in filteredBeats" :key="b.id">
                                            <option :value="b.id" x-text="b.name"></option>
                                        </template>
                                    </select>
                                    <template x-if="userScope && userScope.type === 'beat' && !['super_admin', 'agent_supervisor', 'agent'].includes(userRole)">
                                        <p class="text-[8px] font-bold text-indigo-400 uppercase tracking-widest ml-4 italic">Beat Restricted</p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Sort & Density -->
                        <div class="grid grid-cols-1 gap-4 pt-4 border-t border-slate-100">
                             <div class="space-y-3">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                    <i class="fas fa-compress-alt text-blue-900"></i> Page Density
                                </label>
                                <div class="grid grid-cols-2 gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200/50">
                                    <button @click="density = 'condensed'" :class="density === 'condensed' ? 'bg-white text-blue-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="py-2 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all">Condensed</button>
                                    <button @click="density = 'spacious'" :class="density === 'spacious' ? 'bg-white text-blue-900 shadow-sm font-black' : 'text-slate-500 hover:text-slate-700'" class="py-2 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all">Spacious</button>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                    <i class="fas fa-list-ol text-blue-900"></i> Records Per Page
                                </label>
                                <div class="grid grid-cols-4 gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200/50">
                                    <template x-for="size in [10, 25, 50, 100]" :key="size">
                                        <button @click="perPage = size; page = 1" :class="perPage == size ? 'bg-white text-blue-900 shadow-sm font-black' : 'text-slate-500 hover:text-slate-700'" class="py-2 text-[9px] uppercase tracking-widest rounded-lg transition-all" x-text="size"></button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Reset Buttons -->
                        <div class="space-y-3 pt-6 border-t border-slate-100">
                            <button @click="clearFilters()" class="rose-reset-btn w-full py-5 rounded-3xl text-[10px] font-black uppercase tracking-[0.3em] transition-all duration-300 flex items-center justify-center gap-3 active:scale-95">
                                <i class="fas fa-broom"></i> Reset Filters
                            </button>
                            <button @click="showSidebar = false" class="w-full py-4 mt-2 bg-blue-600 text-white rounded-3xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-800 transition-all flex items-center justify-between px-6 shadow-md shadow-blue-600/20">
                                <span>Hide Filters</span><i class="fas fa-eye-slash"></i>
                            </button>
                            <button @click="exportData()" type="button" class="w-full py-4 mt-2 bg-emerald-600 text-white rounded-3xl font-black text-[10px] uppercase tracking-widest hover:bg-emerald-700 transition-all flex items-center justify-center gap-3 shadow-md shadow-emerald-600/20 active:scale-95 text-center disabled:opacity-50" :disabled="isLoading">
                                <i class="fas fa-file-excel" :class="{'animate-pulse': isLoading}"></i> <span x-text="isLoading ? 'Exporting...' : 'Export CSV'"></span>
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('components.confirm-modal')

        {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
             INTELLIGENCE DETAIL MODAL
        ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
        <div x-show="showDetailModal" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-8 bg-white/50 backdrop-blur-sm" 
             x-transition x-cloak>
            <div @click.away="showDetailModal = false" 
                 class="bg-white rounded-[2.5rem] shadow-[0_32px_64px_-12px_rgba(0,0,0,0.14)] w-full max-w-4xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
                
                <div class="shrink-0 p-10 border-b border-white/10 flex items-center justify-between bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 relative overflow-hidden">
                    <div class="absolute inset-0 bg-grid-white/[0.1] bg-[size:20px_20px]"></div>
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="relative z-10 flex items-center gap-6">
                        <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white border border-white/30 shadow-2xl">
                            <i class="fa-solid fa-satellite-dish text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-3xl font-black text-white tracking-tighter uppercase italic scale-y-110" x-text="selectedItem?.call_number"></h3>
                            <p class="text-[10px] font-black text-blue-100 uppercase tracking-[0.4em] flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-300 animate-pulse"></span>
                                Assistance Details
                            </p>
                        </div>
                    </div>
                    <button @click="showDetailModal = false" class="relative z-10 w-11 h-11 rounded-full bg-white/10 border border-white/20 text-white hover:bg-white/20 transition-all flex items-center justify-center backdrop-blur-md">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Taxonomy & Identity --}}
                        <div class="space-y-6">
                            <div class="group">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Commuter Details</label>
                                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-blue-900">
                                            <i class="fa-solid fa-phone text-xs"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-blue-900 tabular-nums" x-text="selectedItem?.caller_number"></p>
                                            <p class="text-[9px] font-bold text-slate-500 uppercase" x-text="selectedItem?.caller_name || 'Anonymous Entity'"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="group">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Category</label>
                                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                                    <p class="text-lg font-black text-blue-900 uppercase tracking-tight" x-text="selectedItem?.call_type?.name"></p>
                                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mt-1" x-text="selectedItem?.call_sub_type?.name || 'General Resolution'"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Spatial Context --}}
                        <div class="space-y-6">
                            <div class="group">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Beat and location Details</label>
                                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">Assigned Beat</span>
                                        <span class="text-xs font-black text-blue-900 uppercase" x-text="(selectedItem?.office?.parent?.parent?.name || '---') + ' / ' + (selectedItem?.office?.parent?.name || '---') + ' / ' + (selectedItem?.office?.name || '---')"></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">Location Details</span>
                                        <span class="text-xs font-black text-blue-900 uppercase" x-text="selectedItem?.location_details || '---'"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="group">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Tiger & Priority</label>
                                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">Tiger Unit</span>
                                        <span class="text-xs font-black text-amber-600 uppercase" x-text="getAllocatedUnit(selectedItem)"></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">Priority Level</span>
                                        <div x-html="getPriorityBadge(selectedItem?.priority)"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Intelligence Log --}}
                    <div class="group">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Commuter Details & Notes</label>
                        <div class="p-8 bg-slate-50 rounded-[2rem] text-navy-900 relative overflow-hidden">
                            <i class="fa-solid fa-quote-left absolute left-4 top-4 text-navy-200 text-2xl"></i>
                            <p class="text-sm pt-2 font-medium leading-relaxed italic relative z-10" x-text="selectedItem?.details || 'Commuter notes are not provided...'"></p>
                        </div>
                    </div>
                </div>

                <div class="shrink-0 p-8 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-6">
                        <div class="flex flex-col">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Date & Time</span>
                            <span class="text-[10px] font-black text-blue-900" x-text="formatDate(selectedItem?.created_at) + ' ' + formatTime(selectedItem?.created_at)"></span>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <a :href="'/calls/' + selectedItem?.id" class="px-6 py-3 bg-white border border-slate-200 text-blue-900 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all">View Full Details</a>
                        <button @click="showDetailModal = false" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-blue-600/20 active:scale-95 transition-all">Dismiss</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
             OPERATIONAL TRANSITION MODAL
        ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
        <div x-show="showTransitionModal" 
             class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-white/50 backdrop-blur-sm" 
             x-transition x-cloak>
            <div @click.away="showTransitionModal = false" 
                 class="bg-white rounded-[3rem] shadow-[0_0_100px_rgba(0,0,0,0.4)] w-full max-w-lg overflow-hidden border border-white/20 transform transition-all">
                
                <div class="p-12 relative overflow-hidden" 
                     :class="targetStatus === 'in_progress' ? 'bg-gradient-to-br from-amber-400 via-orange-500 to-rose-500' : 'bg-gradient-to-br from-emerald-400 via-teal-500 to-cyan-500'">
                    <div class="absolute inset-0 bg-grid-white/[0.1] bg-[size:16px_16px]"></div>
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/20 rounded-full blur-[40px]"></div>
                    <div class="relative z-10 flex flex-col gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white border border-white/30 shadow-xl mb-2">
                            <i class="fa-solid" :class="targetStatus === 'in_progress' ? 'fa-bolt-lightning' : 'fa-check-double'"></i>
                        </div>
                        <h3 class="text-4xl font-black text-white tracking-widest italic scale-y-110 uppercase" x-text="targetStatus === 'in_progress' ? 'Initiate Help' : 'Complete Help'"></h3>
                        <p class="text-white/80 text-[10px] font-black uppercase tracking-[0.4em] flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                            Operational Authorization Protocol
                        </p>
                    </div>
                </div>

                <div class="p-10 space-y-8">
                    {{-- Unit Allocation for In-Progress --}}
                    <template x-if="targetStatus === 'in_progress'">
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-4">Tiger Allocation</label>
                            <select x-model="selectedTigerId" 
                                class="w-full px-6 py-5 bg-white border-2 border-slate-100 rounded-2xl outline-none font-bold text-blue-900 text-xs focus:border-blue-500 transition-all shadow-xl">
                                <option value="">Select Tiger Unit...</option>
                                <optgroup label="RESERVE TIGERS">
                                    <option value="T1">Tiger-1</option>
                                    <option value="T2">Tiger-2</option>
                                    <option value="T3">Tiger-3</option>
                                    <option value="T4">Tiger-4</option>
                                </optgroup>
                                <optgroup label="SPECIAL TIGERS">
                                    <option value="ST1">Special Tiger-1</option>
                                    <option value="ST2">Special Tiger-2</option>
                                    <option value="ST3">Special Tiger-3</option>
                                </optgroup>
                            </select>
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider ml-4">Showing assigned beat tigers</p>
                        </div>
                    </template>

                    {{-- Remarks --}}
                    <div class="space-y-4">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-4">Remarks</label>
                        <textarea x-model="transitionRemarks" rows="4" 
                            class="w-full px-6 py-5 bg-slate-50 border-2 border-slate-100 rounded-2xl outline-none font-bold text-blue-900 text-xs focus:border-blue-500 transition-all shadow-inner placeholder:text-slate-300"
                            placeholder="Enter help updates or officers details..."></textarea>
                    </div>

                    <div class="flex gap-4 pt-6">
                        <button @click="showTransitionModal = false" class="flex-1 px-8 py-5 bg-slate-50 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-all">Cancel</button>
                        <button @click="submitTransition()" 
                            :disabled="isTransiting || (targetStatus === 'in_progress' && !selectedTigerId)"
                            :class="targetStatus === 'in_progress' ? 'bg-gradient-to-r from-amber-500 to-orange-600 shadow-orange-500/30' : 'bg-gradient-to-r from-emerald-500 to-cyan-600 shadow-emerald-500/30'"
                            class="flex-[2] px-8 py-5 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-2xl hover:-translate-y-1 transition-all disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                            <template x-if="!isTransiting">
                                <span class="flex items-center gap-3">
                                    <i class="fa-solid fa-bolt-lightning"></i>
                                    <span>Submit</span>
                                </span>
                            </template>
                            <template x-if="isTransiting">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function callManager(config) {
            return {
                items: config.items || [],
                stats: config.stats || {},
                callTypes: config.callTypes || [],
                callSubTypes: config.callSubTypes || [],
                agents: config.agents || [],
                status: config.status || 'all',
                permissions: config.permissions || {},
                zones: config.zones || [],
                sectors: config.sectors || [],
                beats: config.beats || [],
                search: '',
                filterZone: '',
                filterSector: '',
                filterBeat: '',
                filterType: '',
                filterSubType: '',
                filterAgent: '',
                userRole: config.userRole || null,
                userScope: config.userScope || null,
                filterStatus: config.status || 'all',
                dateFrom: '',
                dateTo: '',
                timeFrom: '',
                timeTo: '',
                totalRecords: config.total || 0,
                totalPages: config.last_page || 1,
                isLoading: false,
                fetchTimeout: null,


                
                async fetchData() {
                    if (this.isLoading) return;
                    this.isLoading = true;
                    try {
                        const params = new URLSearchParams({
                            search: this.search,
                            status: this.filterStatus,
                            zone_id: this.filterZone,
                            sector_id: this.filterSector,
                            beat_id: this.filterBeat,
                            call_type_id: this.filterType,
                            call_sub_type_id: this.filterSubType,
                            agent_id: this.filterAgent,
                            date_from: this.dateFrom,
                            date_to: this.dateTo,
                            time_from: this.timeFrom,
                            time_to: this.timeTo,
                            page: this.page,
                            perPage: this.perPage,
                            sort: this.sortBy,
                            direction: this.sortDirection
                        });
                        
                        const res = await fetch(window.location.pathname + '?' + params.toString(), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            this.items = data.items;
                            this.stats = data.stats;
                            this.page = data.current_page;
                            this.totalPages = data.last_page;
                            this.totalRecords = data.total;
                        }
                    } catch (e) {
                        console.error('Fetch error:', e);
                        if (window.Notification) window.Notification.error('Communications array offline.', 'Connection Error');
                    } finally {
                        this.isLoading = false;
                    }
                },

                async exportData() {
                    if (this.isLoading) return;
                    this.isLoading = true;

                    if (window.Notification) window.Notification.info('Generating CSV package...', 'Exporting Data');

                    try {
                        const params = new URLSearchParams({
                            search: this.search,
                            status: this.filterStatus,
                            zone_id: this.filterZone,
                            sector_id: this.filterSector,
                            beat_id: this.filterBeat,
                            call_type_id: this.filterType,
                            call_sub_type_id: this.filterSubType,
                            agent_id: this.filterAgent,
                            date_from: this.dateFrom,
                            date_to: this.dateTo,
                            time_from: this.timeFrom,
                            time_to: this.timeTo,
                            sort: this.sortBy,
                            direction: this.sortDirection
                        });

                        const res = await fetch(`{{ route('calls.export') }}?${params.toString()}`, {
                            headers: { 'Accept': 'text/csv', 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        if (!res.ok) {
                            const errorData = await res.json().catch(() => ({ error: 'Export server error' }));
                            throw new Error(errorData.error || 'Failed to generate export');
                        }

                        const contentType = res.headers.get('content-type');
                        if (contentType && !contentType.includes('text/csv')) {
                            // If we got HTML or something else, it might be a redirect or error page
                            throw new Error('Server returned an invalid format. Please try again or check your filters.');
                        }

                        const blob = await res.blob();
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = `export_${Date.now()}.csv`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        if (window.Notification) window.Notification.success('Helps downloaded successfully.', 'Export Complete');
                    } catch (e) {
                        console.error('Export error:', e);
                        if (window.Notification) window.Notification.error(e.message || 'Network error, please try again.', 'Export Failed');
                    } finally {
                        this.isLoading = false;
                    }
                },

                triggerFetch() {
                    if(this.fetchTimeout) clearTimeout(this.fetchTimeout);
                    // Reset to page 1 ONLY IF the change wasn't pagination itself. 
                    // To handle this properly, the watcher for pagination handles itself independently.
                    this.fetchTimeout = setTimeout(() => {
                        this.fetchData();
                    }, 400);
                },

                init() {
                    // Check if the user is a privileged role (Admin, Supervisor, Agent)
                    const isPrivileged = ['super_admin', 'agent_supervisor', 'agent'].includes(this.userRole);

                    if (this.userScope && !isPrivileged) {
                        if (this.userScope.type === 'zone') {
                            this.filterZone = this.userScope.id;
                        } else if (this.userScope.type === 'sector') {
                            const sector = this.sectors.find(s => s.id == this.userScope.id);
                            if (sector) {
                                this.filterZone = sector.parent_id;
                                this.filterSector = sector.id;
                            }
                        } else if (this.userScope.type === 'beat') {
                            const beat = this.beats.find(b => b.id == this.userScope.id);
                            if (beat) {
                                const sector = this.sectors.find(s => s.id == beat.parent_id);
                                if (sector) {
                                    this.filterZone = sector.parent_id;
                                    this.filterSector = sector.id;
                                }
                                this.filterBeat = beat.id;
                            }
                        }
                    }

                    this.$watch('search', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('filterStatus', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('filterZone', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('filterSector', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('filterBeat', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('filterType', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('filterSubType', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('filterAgent', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('dateFrom', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('dateTo', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('timeFrom', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('timeTo', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('perPage', () => { this.page = 1; this.triggerFetch(); });
                    this.$watch('page', () => { this.triggerFetch(); });
                },

                density: 'spacious',
                viewMode: 'table',
                sortBy: 'call_number',
                sortDirection: 'desc',
                page: 1,
                perPage: 10,
                showSidebar: false,
                showDetailModal: false,
                showTransitionModal: false,
                selectedItem: null,
                targetStatus: '',
                transitionRemarks: '',
                selectedTigerId: '',
                isTransiting: false,
                showConfirmModal: false,
                confirmLoading: false,
                confirmConfig: { title: '', message: '', icon: '', isDanger: false },

                get filteredBeats() {
                    if (!this.filterSector) return [];
                    return this.beats.filter(b => b.parent_id == this.filterSector);
                },

                get allSubTypes() {
                    return this.callSubTypes;
                },

                get filteredSubTypesInFilter() {
                    if (!this.filterType) return [];
                    return this.allSubTypes.filter(st => st.call_type_id == this.filterType);
                },

                get filteredSectors() {
                    if (!this.filterZone) return [];
                    return this.sectors.filter(s => s.parent_id == this.filterZone);
                },

                get pagedItems() { return this.items; },

                sortByItem(field) {
                    if (this.sortBy === field) {
                        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortBy = field;
                        this.sortDirection = 'asc';
                    }
                    this.triggerFetch();
                },

                getSortIcon(field) {
                    if (this.sortBy !== field) return 'fa-sort opacity-20';
                    return this.sortDirection === 'asc' ? 'fa-sort-up text-blue-600' : 'fa-sort-down text-blue-600';
                },

                getAllocatedUnit(item) {
                    if (!item) return 'UNALLOCATED';
                    if (item.tiger) return item.tiger.tiger_code;
                    if (item.inprogress_remarks && (item.inprogress_remarks.includes('[Allocated Asset:') || item.inprogress_remarks.includes('[Allocated Static Asset:'))) {
                        const match = item.inprogress_remarks.match(/\[Allocated (?:Static )?Asset: (.*?)\]/);
                        return match ? match[1] : 'UNALLOCATED';
                    }
                    return 'UNALLOCATED';
                },

                formatDate(dateStr) {
                    if (!dateStr) return '---';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
                },

                formatTime(dateStr) {
                    if (!dateStr) return '---';
                    const d = new Date(dateStr);
                    return d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                },

                formatTitleCase(str) {
                    if (!str) return '---';
                    return str.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
                },

                getLocationHierarchy(item) {
                    if (!item || !item.office) return 'OFF-GRID';
                    const parts = [];
                    if (item.office.parent?.parent?.name) parts.push(item.office.parent.parent.name);
                    if (item.office.parent?.name) parts.push(item.office.parent.name);
                    if (item.office.name) parts.push(item.office.name);
                    return parts.length > 0 ? parts.join(' / ') : 'OFF-GRID';
                },

                getStatusBadge(status) {
                    const badges = {
                        'pending': '<span class="px-3 py-1 bg-rose-50 text-rose-600 border border-rose-100 rounded-lg text-[9px] font-black uppercase tracking-widest">Pending</span>',
                        'in_progress': '<span class="px-3 py-1 bg-amber-50 text-amber-600 border border-amber-100 rounded-lg text-[9px] font-black uppercase tracking-widest">In-Process</span>',
                        'completed': '<span class="px-3 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg text-[9px] font-black uppercase tracking-widest">Resolved</span>',
                        'forwarded': '<span class="px-3 py-1 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg text-[9px] font-black uppercase tracking-widest">Forwarded</span>',
                        'junk': '<span class="px-3 py-1 bg-slate-50 text-slate-400 border border-slate-100 rounded-lg text-[9px] font-black uppercase tracking-widest">Junk</span>',
                        'cancelled': '<span class="px-3 py-1 bg-slate-100 text-slate-500 border border-slate-200 rounded-lg text-[9px] font-black uppercase tracking-widest">Cancelled</span>'
                    };
                    return badges[status] || status;
                },

                openDetailModal(item) {
                    this.selectedItem = item;
                    this.showDetailModal = true;
                },

                openTransitionModal(item, status) {
                    this.selectedItem = item;
                    this.targetStatus = status;
                    this.transitionRemarks = '';
                    this.selectedTigerId = item.tiger_id || '';
                    this.showTransitionModal = true;
                },

                async submitTransition() {
                    if (this.isTransiting) return;
                    this.isTransiting = true;

                    if (window.Notification) window.Notification.info('Updating help status...', 'Processing Help');

                    try {
                        const response = await fetch(`/calls/${this.selectedItem.id}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                status: this.targetStatus,
                                remarks: this.transitionRemarks,
                                tiger_id: this.selectedTigerId
                            })
                        });

                        const result = await response.json();

                        if (response.ok) {
                            const index = this.items.findIndex(i => i.id === this.selectedItem.id);
                            if (index !== -1) {
                                this.items[index].status = this.targetStatus;
                                if (this.selectedTigerId) {
                                    this.items[index].tiger_id = this.selectedTigerId;
                                }
                            }
                            
                            this.showTransitionModal = false;
                            if (window.Notification) window.Notification.success(result.message || 'Help updated successfully.', 'Success');
                            
                            setTimeout(() => window.location.reload(), 5000);
                        } else {
                            if (window.Notification) window.Notification.warning(result.message || 'Help not updated.', 'Help Update Failed');
                        }
                    } catch (e) {
                        if (window.Notification) window.Notification.error('Unable to update help status.', 'Connection Error');
                    } finally {
                        this.isTransiting = false;
                    }
                },

                getPriorityBadge(priority, compact = false) {
                    const configs = {
                        1: { label: 'Critical', bg: 'bg-rose-600', text: 'text-white', icon: 'fa-bolt' },
                        2: { label: 'Urgent', bg: 'bg-orange-500', text: 'text-white', icon: 'fa-triangle-exclamation' },
                        3: { label: 'Normal', bg: 'bg-amber-500', text: 'text-white', icon: 'fa-clock' }
                    };
                    const config = configs[priority] || configs[3];
                    if (compact) {
                        return `<span class="px-2 py-0.5 ${config.bg} ${config.text} rounded text-[9px] font-black uppercase flex items-center gap-1 w-fit justify-center mx-auto shadow-sm"><i class="fa-solid ${config.icon} text-[8px]"></i>${config.label}</span>`;
                    }
                    return `<span class="px-3 py-1 ${config.bg} ${config.text} rounded-lg text-[9px] font-black uppercase tracking-widest shadow-sm flex items-center gap-1.5 w-fit"><i class="fa-solid ${config.icon}"></i>${config.label}</span>`;
                },

                clearFilters() {
                    this.filterStatus = 'all';
                    this.filterZone = this.userScope?.type === 'zone' ? this.userScope.id : '';
                    this.filterSector = this.userScope?.type === 'sector' ? this.userScope.id : '';
                    this.filterBeat = this.userScope?.type === 'beat' ? this.userScope.id : '';
                    this.filterType = '';
                    this.filterSubType = '';
                    this.filterAgent = '';
                    this.search = '';
                    this.page = 1;
                    this.dateFrom = '';
                    this.dateTo = '';
                    this.timeFrom = '';
                    this.timeTo = '';
                    this.triggerFetch();

                },

                confirmDelete(item) {
                    this.confirmConfig = {
                        title: 'Purge Record',
                        message: `Are you sure you want to permanently purge trace <strong>${item.call_number}</strong>? <br><br>This action cannot be undone.`,
                        icon: 'fa-trash-can',
                        isDanger: true,
                        action: async () => {
                            const response = await fetch(`/calls/${item.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                }
                            });
                            if (response.ok) {
                                this.items = this.items.filter(i => i.id !== item.id);
                                if (window.showSuccess) showSuccess('Trace purged from directory.');
                            } else {
                                if (window.showError) showError('Command execution failed.');
                            }
                        }
                    };
                    this.showConfirmModal = true;
                },

                async executeConfirmAction() {
                    if (typeof this.confirmConfig.action === 'function') {
                        this.confirmLoading = true;
                        try {
                            await this.confirmConfig.action();
                        } finally {
                            this.confirmLoading = false;
                            this.showConfirmModal = false;
                        }
                    }
                },

                openReminderModal(item) {
                    this.confirmConfig = {
                        title: 'Dispatch Reminder',
                        message: `Initialize a reminder protocol for Trace <strong class="text-amber-500">${item.call_number}</strong>? <br><br>This will alert assigned beat operator for immediate action.`,
                        icon: 'fa-bell',
                        isDanger: false,
                        action: async () => {
                            await this.sendReminder(item);
                        }
                    };
                    this.showConfirmModal = true;
                },

                pagesToShow() {
                    const pages = [];
                    const delta = 2;
                    const left = this.page - delta;
                    const right = this.page + delta + 1;
                    const range = [];
                    const rangeWithDots = [];
                    let l;

                    for (let i = 1; i <= this.totalPages; i++) {
                        if (i === 1 || i === this.totalPages || (i >= left && i < right)) {
                            range.push(i);
                        }
                    }

                    for (let i of range) {
                        if (l) {
                            if (i - l === 2) {
                                rangeWithDots.push(l + 1);
                            } else if (i - l !== 1) {
                                rangeWithDots.push('...');
                            }
                        }
                        rangeWithDots.push(i);
                        l = i;
                    }

                    return rangeWithDots;
                },

                async sendReminder(item) {
                    try {
                        const res = await fetch(`/api/calls/${item.id}/reminder`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (res.ok) {
                            item.call_reminder_count = data.call.call_reminder_count;
                            item.last_reminder_at = data.call.last_reminder_at;
                            if (window.Notification) window.Notification.success(data.message, 'Protocol Triggered');
                        } else {
                            if (window.Notification) window.Notification.warning(data.message, 'Manual Override Needed');
                        }
                    } catch (e) {
                        if (window.Notification) window.Notification.error('Communications fail-safe triggered.', 'Sync Error');
                    }
                }
            }
        }
    </script>
    @endpush
@endsection
