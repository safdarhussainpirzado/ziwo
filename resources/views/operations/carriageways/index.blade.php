@extends('layouts.app')

@section('title', 'Carriageway Directory - NHMP 130')

@section('page-title', 'Carriageway Directory')

@section('content')


    <div x-data="carriagewayManager(@js(['items' => $carriageways]))" class="space-y-8 relative mt-4 max-w-[1700px] mx-auto" x-cloak>


        <!-- Floating Sidebar Toggle -->
        <button @click="showSidebar = true"
            x-show="!showSidebar"
            x-transition:enter="transition ease-out duration-500 delay-100"
            x-transition:enter-start="translate-x-full opacity-100"
            x-transition:enter-end="translate-x-0 opacity-100"
            class="fixed top-1/2 right-0 -translate-y-1/2 z-40 bg-gradient-to-b from-blue-600 to-blue-800 text-white p-2.5 py-6 rounded-l-2xl shadow-[0_0_30px_-5px_rgba(37,99,235,0.4)] hover:shadow-[-5px_0_40px_-5px_rgba(37,99,235,0.7)] hover:pr-4 transition-all duration-300 flex flex-col items-center gap-4 cursor-pointer" title="Open Filters">
            <i class="fas fa-sliders-h drop-shadow-lg text-sm"></i>
            <span style="writing-mode: vertical-rl;" class="text-[9px] font-black uppercase tracking-[0.3em] rotate-180 drop-shadow-md text-blue-50">Intelligence Scope</span>
        </button>

        <!-- STATS CARDS -->
        <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-4">
            <!-- Total Routes Card -->
            <div @click="filterStatus = ''; typeFilter = ''" :class="!filterStatus && !typeFilter ? 'card-3d-active blue' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-blue-600 shadow-[0_8px_16px_rgba(37,99,235,0.2)]">
                    <i class="fas fa-road text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-blue-500 uppercase">Arterial Routes</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.length"></h4>
                </div>
            </div>

            <!-- Active Routes Card -->
            <div @click="filterStatus = (filterStatus === 'active' ? '' : 'active')" :class="filterStatus === 'active' ? 'card-3d-active emerald' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-emerald-500 shadow-[0_8px_16px_rgba(16,185,129,0.2)]">
                    <i class="fas fa-check-circle text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-emerald-500 uppercase">Operational</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.filter(i => i.status === 'active').length"></h4>
                </div>
            </div>

            <!-- Restricted Routes Card -->
            <div @click="filterStatus = (filterStatus === 'inactive' ? '' : 'inactive')" :class="filterStatus === 'inactive' ? 'card-3d-active rose' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-rose-500 shadow-[0_8px_16px_rgba(225,29,72,0.2)]">
                    <i class="fas fa-ban text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-rose-500 uppercase">Restricted</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.filter(i => i.status !== 'active').length"></h4>
                </div>
            </div>

            <!-- Motorways Card -->
            <div @click="typeFilter = (typeFilter === 'Motorway' ? '' : 'Motorway')" :class="typeFilter === 'Motorway' ? 'card-3d-active amber' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-amber-500 shadow-[0_8px_16px_rgba(245,158,11,0.2)]">
                    <i class="fas fa-car-tunnel text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-amber-500 uppercase">Motorways</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.filter(i => i.type === 'Motorway').length"></h4>
                </div>
            </div>

            <!-- Highways Card -->
            <div @click="typeFilter = (typeFilter === 'Highway' ? '' : 'Highway')" :class="typeFilter === 'Highway' ? 'card-3d-active indigo' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-indigo-500 shadow-[0_8px_16px_rgba(79,70,229,0.2)]">
                    <i class="fas fa-bridge text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-indigo-500 uppercase">Highways</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.filter(i => i.type === 'Highway').length"></h4>
                </div>
            </div>
        </div>

        <!-- MAIN CONTROL PANEL -->
        <div class="grid lg:grid-cols-12 gap-8 relative pb-20 px-4 sm:px-0">
            
            <!-- Left Column Content -->
            <div class="transition-all duration-500" :class="showSidebar ? 'lg:col-span-9' : 'lg:col-span-12'">
                <div class="bg-white rounded-[2rem] shadow-[0_10px_40_rgba(0,0,0,0.03)] border border-gray-100 overflow-hidden flex flex-col relative z-20">
                    
                    <!-- Panel Header -->
                    <!-- Panel Header -->
                    <div class="bg-blue-50/50 p-8 border-b border-blue-100/50">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-[1.25rem] bg-white flex items-center justify-center border border-blue-100 shadow-sm transition-transform hover:scale-105 duration-300">
                                    <i class="fas fa-traffic-light text-3xl text-blue-600"></i>
                                </div>
                                <div class="relative z-10">
                                    <h2 class="text-2xl font-extrabold text-blue-900 tracking-tight flex items-center gap-3">
                                        Network Backbone <span class="text-lg font-bold text-slate-400" x-text="'(' + filteredItems.length + ' routes)'"></span>
                                    </h2>
                                    <p class="text-slate-500 text-sm font-bold mt-1">Configure and manage primary carriageway routes</p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-4 items-center">
                                <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-1.5 shadow-sm">
                                    <span class="text-[9px] font-black text-slate-400 border-r border-slate-100 pr-2 uppercase font-mono">Row Density</span>
                                    <select x-model="perPage" class="bg-transparent text-blue-600 text-[10px] font-black uppercase cursor-pointer outline-none focus:ring-0 border-none p-0 pr-4">
                                        <option value="10">10 Per Page</option>
                                        <option value="25">25 Per Page</option>
                                        <option value="50">50 Per Page</option>
                                        <option value="100">100 Per Page</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl p-1.5 shadow-sm">
                                    <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-blue-600'" class="w-10 h-10 flex items-center justify-center rounded-lg transition-all"><i class="fas fa-list-ul"></i></button>
                                    <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-400 hover:text-blue-600'" class="w-10 h-10 flex items-center justify-center rounded-lg transition-all"><i class="fas fa-th-large"></i></button>
                                </div>

                                <button @click="resetForm(); modalMode = 'add'; showModal = true" class="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-black shadow-[0_8px_20px_rgba(37,99,235,0.3)] transition-all active:scale-95 group">
                                    <i class="fas fa-plus group-hover:rotate-180 transition-transform duration-500"></i> Register Highway
                                </button>
                                
                                <button @click="showSidebar = !showSidebar" class="w-12 h-12 flex items-center justify-center bg-white border border-slate-200 text-blue-600 rounded-xl hover:bg-blue-50 transition-colors shadow-sm" :title="showSidebar ? 'Hide Filters' : 'Show Filters'">
                                    <i class="fas" :class="showSidebar ? 'fa-eye-slash' : 'fa-filter'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Grid View -->
                    <div x-show="viewMode === 'grid'" x-transition class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                            <template x-for="item in pagedItems" :key="item.id">
                                <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8 hover:-translate-y-2 transition-all duration-300 group relative overflow-hidden">
                                    <div class="absolute -right-4 -top-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                                        <i class="fa-solid fa-road text-9xl text-blue-900"></i>
                                    </div>
                                    
                                    <div class="flex items-start justify-between mb-8 relative z-10">
                                        <div class="w-20 h-20 rounded-[1.75rem] bg-navy-900 p-1 flex items-center justify-center shadow-lg group-hover:rotate-6 transition-transform">
                                            <div class="w-full h-full rounded-[1.5rem] bg-white flex items-center justify-center text-navy-900 font-extrabold text-2xl tracking-tight uppercase" x-text="item.road"></div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase mb-1">Operational State</div>
                                            <template x-if="item.status === 'active'">
                                                <span class="flex items-center gap-2 text-blue-600 font-black text-[10px] tracking-widest uppercase justify-end">
                                                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span> Active
                                                </span>
                                            </template>
                                            <template x-if="item.status !== 'active'">
                                                <span class="flex items-center gap-2 text-rose-600 font-black text-[10px] tracking-widest uppercase justify-end">
                                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Restricted
                                                </span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="relative z-10">
                                        <h4 class="text-2xl font-extrabold text-blue-900 tracking-tight leading-tight mb-1" x-text="item.road_name"></h4>
                                        <div class="flex items-center gap-2 mb-6 text-xs font-black text-blue-600 uppercase tracking-widest">
                                            <span x-text="item.type"></span>
                                        </div>

                                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 mb-8">
                                            <div class="flex items-center justify-between mb-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                                <span>Distance Reach</span>
                                                <span class="text-blue-900" x-text="item.total_km + ' KM'"></span>
                                            </div>
                                            <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                                <div class="h-full bg-blue-900 rounded-full transition-all duration-1000" style="width: 100%"></div>
                                            </div>
                                            <div class="flex items-center justify-between mt-4">
                                                <div class="flex flex-col">
                                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Sectors</span>
                                                    <span class="text-xs font-bold text-blue-900" x-text="item.sectors_count || 0"></span>
                                                </div>
                                                <div class="flex flex-col text-right">
                                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Patrol Beats</span>
                                                    <span class="text-xs font-bold text-blue-900" x-text="item.beats_count || 0"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pt-5 border-t border-slate-100 space-y-3">
                                            <div class="flex items-center gap-3 w-full bg-slate-50 p-3 rounded-xl border border-slate-100 mb-3">
                                                <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 flex items-center justify-center shadow-sm">
                                                    <i class="fa-solid fa-route text-xs"></i>
                                                </div>
                                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest text-wrap" x-text="item.road_from + ' → ' + item.road_to"></span>
                                            </div>
                                            <!-- Action Grid: 3-col × 2-row icon buttons -->
                                            <div class="grid grid-cols-3 gap-2">
                                                <button @click="viewItem(item)" title="Inspect Data" class="w-9 h-9 rounded-xl bg-blue-500 border border-blue-600 text-white hover:bg-blue-600 active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </button>
                                                <button @click="editItem(item)" title="Modify parameters" class="w-9 h-9 rounded-xl bg-indigo-500 border border-indigo-600 text-white hover:bg-indigo-600 active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
                                                    <i class="fa-solid fa-sliders text-xs"></i>
                                                </button>
                                                <button @click="confirmStatus(item)" title="Toggle State" :class="item.status === 'active' ? 'bg-amber-500 border-amber-600 hover:bg-amber-600' : 'bg-emerald-500 border-emerald-600 hover:bg-emerald-600'" class="w-9 h-9 rounded-xl text-white border active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
                                                    <i class="fa-solid fa-power-off text-xs"></i>
                                                </button>
                                                <button @click="confirmDelete(item)" title="Purge" class="w-9 h-9 rounded-xl bg-rose-600 border border-rose-700 text-white hover:bg-rose-700 active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
                                                    <i class="fa-solid fa-trash-alt text-xs"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Table View -->
                    <div x-show="viewMode === 'table'" x-transition class="overflow-x-auto shadow-inner bg-white rounded-3xl border border-slate-100">
                        <table class="w-full text-left" :class="density === 'condensed' ? 'condensed-table' : 'spacious-table'">
                            <thead class="bg-blue-50 border-b border-blue-100">
                                <tr>
                                    <th class="px-4 py-4 border-b border-slate-50">
                                        <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-road text-[10px]"></i></div>
                                            <button @click="sortByField('road')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">Index <i class="fas text-[10px] transition-all opacity-0 group-hover:opacity-100 text-blue-600" :class="getSortIcon('road')"></i></button>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50">
                                        <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-tag text-[10px]"></i></div>
                                            <button @click="sortByField('road_name')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">Nomenclature <i class="fas text-[10px] transition-all opacity-0 group-hover:opacity-100 text-blue-600" :class="getSortIcon('road_name')"></i></button>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50">
                                        <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-ruler text-[10px]"></i></div>
                                            <button @click="sortByField('total_km')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">Distance <i class="fas text-[10px] transition-all opacity-0 group-hover:opacity-100 text-blue-600" :class="getSortIcon('total_km')"></i></button>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50 text-center">
                                        <div class="flex items-center justify-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-toggle-on text-[10px]"></i></div>
                                            <button @click="sortByField('status')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">Status <i class="fas text-[10px] transition-all opacity-0 group-hover:opacity-100 text-blue-600" :class="getSortIcon('status')"></i></button>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50 text-center">
                                        <div class="flex items-center justify-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-shield-halved text-[10px]"></i></div>
                                            <span>Patrols</span>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50 text-center">
                                        <div class="flex items-center justify-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-bolt text-[10px]"></i></div>
                                            <span>Actions</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="item in pagedItems" :key="item.id">
                                    <tr class="hover:bg-blue-50/40 transition-colors group">
                                        <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <span class="font-black text-blue-900 text-sm uppercase tracking-wider" x-text="item.road"></span>
                                        </td>
                                        <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-blue-900 text-sm" x-text="item.road_name"></span>
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter" x-text="item.road_from + ' → ' + item.road_to"></span>
                                            </div>
                                        </td>
                                        <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <span class="text-xs font-black text-blue-600 bg-blue-50 px-2 py-1 rounded" x-text="item.total_km + ' KM'"></span>
                                        </td>
                                        <td class="px-6 text-center" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <button @click="confirmStatus(item)" :class="item.status === 'active' ? 'bg-blue-600 text-white shadow-[0_4px_12px_rgba(37,99,235,0.3)]' : 'bg-slate-200 text-slate-500'" class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest transition-all active:scale-95">
                                                <span x-text="item.status"></span>
                                            </button>
                                        </td>
                                        <td class="px-6 text-center" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <span class="px-3 py-1 bg-blue-900 rounded-lg text-[9px] font-black text-white" x-text="item.beats_count || 0"></span>
                                        </td>
                                        <td class="px-6 text-center" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <div class="flex flex-wrap items-center justify-center gap-1.5 w-[120px] mx-auto">
                                                <button @click="viewItem(item)" title="Inspect Data" class="w-9 h-9 bg-blue-500 border border-blue-600 text-white hover:bg-blue-600 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </button>
                                                <button @click="editItem(item)" title="Modify parameters" class="w-9 h-9 bg-indigo-500 border border-indigo-600 text-white hover:bg-indigo-600 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                    <i class="fa-solid fa-sliders text-xs"></i>
                                                </button>
                                                <button @click="confirmStatus(item)" title="Toggle State" :class="item.status === 'active' ? 'bg-amber-500 border-amber-600 hover:bg-amber-600' : 'bg-emerald-500 border-emerald-600 hover:bg-emerald-600'" class="w-9 h-9 text-white border transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                    <i class="fa-solid fa-power-off text-xs"></i>
                                                </button>
                                                <button @click="confirmDelete(item)" title="Purge" class="w-9 h-9 bg-rose-600 border border-rose-700 text-white hover:bg-rose-700 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                    <i class="fa-solid fa-trash-alt text-xs"></i>
                                                </button>
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
                            Showing <span class="text-blue-900" x-text="((page - 1) * perPage) + 1"></span> to <span class="text-blue-900" x-text="Math.min(page * perPage, filteredItems.length)"></span> of <span class="text-blue-900" x-text="filteredItems.length"></span> Routes
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="page--" :disabled="page === 1" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition-all"><i class="fa-solid fa-chevron-left"></i></button>
                            <template x-for="p in totalPages" :key="p">
                                <button @click="page = p" :class="page === p ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/30' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" class="w-10 h-10 rounded-xl border font-black text-xs transition-all" x-text="p"></button>
                            </template>
                            <button @click="page++" :disabled="page === totalPages" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-50 transition-all"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div x-show="filteredItems.length === 0" class="py-32 text-center bg-white">
                        <i class="fa-solid fa-road text-6xl text-slate-100 mb-6 animate-pulse"></i>
                        <h3 class="text-2xl font-black text-blue-900" x-text="search ? 'No arterial matches' : 'Network Grid Empty'"></h3>
                        <p class="text-slate-400 font-bold mt-2" x-text="search ? 'Refine your network parameters.' : 'Database contains no established carriageway routes.'"></p>
                    </div>

                </div>
            </div>

            <!-- Right Column - Operational Filters -->
            <div class="lg:col-span-3 pb-8 transition-all duration-300" x-show="showSidebar" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-4" x-cloak>
                <div class="lg:sticky lg:top-4 lg:max-h-[calc(100vh-100px)] lg:overflow-y-auto bg-white rounded-[2rem] shadow-xl border border-slate-100 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-blue-600 shadow-sm">
                                <i class="fas fa-filter"></i>
                            </div>
                            <div>
                                <h2 class="font-extrabold text-white text-lg tracking-tight">Intelligence</h2>
                                <p class="text-[9px] font-black text-blue-200 uppercase tracking-widest mt-0.5 opacity-60">Refine Route Scope</p>
                            </div>
                        </div>
                        <button @click="showSidebar = false" class="w-8 h-8 rounded-lg bg-white/10 border border-white/20 text-white hover:bg-white/20 shadow-sm flex items-center justify-center transition-colors">
                            <i class="fas fa-angle-right"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-6 overflow-y-auto w-full flex-1 no-scrollbar">
                        <!-- Search -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-search text-blue-500"></i> Localize Route
                            </label>
                            <div class="relative">
                                <input type="text" x-model="search" placeholder="Search code/name..." class="w-full pl-11 pr-4 py-3 bg-white border-2 border-slate-100 rounded-xl focus:border-blue-500 outline-none font-bold text-slate-700 text-sm transition-all focus:shadow-lg focus:shadow-blue-500/10">
                                <i class="fas fa-map-marked-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            </div>
                        </div>
                        <!-- Page Density Filter -->
                        <div class="space-y-3 pt-4 border-t border-slate-100">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-compress-alt text-blue-500"></i> Page Density
                            </label>
                            <div class="grid grid-cols-2 gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200/50">
                                <button @click="density = 'condensed'" :class="density === 'condensed' ? 'bg-white text-blue-600 shadow-sm' :
                                        'text-slate-500 hover:text-slate-700'" class="py-2 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all text-slate-500 hover:text-slate-700">
                                    Condensed
                                </button>
                                <button @click="density = 'spacious'" :class="density === 'spacious' ? 'bg-white text-blue-600 shadow-sm font-black' :
                                        'text-slate-500 hover:text-slate-700'" class="py-2 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all">
                                    Spacious
                                </button>
                            </div>
                        </div>

                        <!-- Records Per Page Filter -->
                        <div class="space-y-3 pt-4 border-t border-slate-100">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-list-ol text-blue-500"></i> Records Per Page
                            </label>
                            <div class="grid grid-cols-4 gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200/50">
                                <template x-for="size in [10, 25, 50, 100]" :key="size">
                                    <button @click="perPage = size; page = 1" :class="perPage == size ? 'bg-white text-blue-600 shadow-sm font-black' :
                                            'text-slate-500 hover:text-slate-700'" class="py-2 text-[9px] uppercase tracking-widest rounded-lg transition-all" x-text="size">
                                    </button>
                                </template>
                            </div>
                        </div>



                        <!-- Sort Logic -->
                        <div class="space-y-3 border-t border-slate-100 pt-6">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-sort-amount-down text-amber-500"></i> Sequencing
                            </label>
                            <div class="grid grid-cols-1 gap-2">
                                <button @click="sortBy = 'road'" :class="sortBy === 'road' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-50 text-slate-600 '" class="px-4 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-left flex items-center justify-between transition-all">
                                    <span>Index Code</span>
                                    <i class="fas fa-barcode opacity-40"></i>
                                </button>
                                <button @click="sortBy = 'road_name'" :class="sortBy === 'road_name' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-50 text-slate-600 '" class="px-4 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-left flex items-center justify-between transition-all">
                                    <span>Route Name</span>
                                    <i class="fas fa-tag opacity-40"></i>
                                </button>
                                <button @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'" class="mt-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-blue-100 transition-colors flex items-center justify-center gap-2">
                                    <i class="fas" :class="sortDirection === 'asc' ? 'fa-sort-alpha-up' : 'fa-sort-alpha-down'"></i>
                                    <span x-text="sortDirection === 'asc' ? 'Ascending Order' : 'Descending Order'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Status Quick Toggle -->
                        <div class="space-y-3 border-t border-slate-100 pt-6">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-toggle-on text-teal-500"></i> Quick Command
                            </label>
                            <div class="space-y-3">
                                <button @click="search = ''; typeFilter = ''; sortBy = 'road'; sortDirection = 'asc';" class="w-full py-5 text-rose-500 hover:bg-rose-50 rounded-3xl text-[10px] font-black uppercase tracking-[0.3em] transition-all duration-300 flex items-center justify-center gap-3 active:scale-95 border-2 border-rose-100">
                                    <i class="fas fa-broom"></i> Reset Filters
                                </button>
                                <button @click="showSidebar = false" class="w-full py-4 mt-2 bg-blue-600 text-white rounded-3xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-700 transition-all flex items-center justify-between px-6 shadow-md shadow-blue-600/20">
                                    <span>Hide Filters</span><i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal (HMS Original MedCare Look) -->
        <div x-show="showModal" class="fixed inset-0 z-[100] overflow-y-auto px-4 py-6" x-transition.opacity>
            <div class="flex items-center justify-center min-h-screen">
                <div class="fixed inset-0 bg-white/30 backdrop-blur-sm z-[90]" @click="showModal = false"></div>
                
                <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-xl z-[100] overflow-hidden relative border border-slate-100" x-transition.scale.origin.center>
                    <div :class="modalMode === 'add' ? 'from-blue-600 to-blue-700' : (modalMode === 'edit' ? 'from-blue-600 to-purple-700' : 'from-slate-800 to-blue-900')" class="bg-gradient-to-br p-8 text-white relative">
                        <div class="flex items-center justify-between relative z-10">
                            <div class="flex items-center gap-5">
                                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-xl border border-white/20">
                                    <i class="fa-solid text-2xl text-white" :class="modalMode === 'add' ? 'fa-plus-circle' : (modalMode === 'edit' ? 'fa-pen-to-square' : 'fa-eye')"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-extrabold tracking-tight" x-text="modalMode === 'add' ? 'Register Highway' : (modalMode === 'edit' ? 'Modify Route' : 'Route Intelligence')"></h3>
                                    <p class="text-blue-100 text-[10px] font-black uppercase tracking-widest mt-1" x-text="modalMode === 'view' ? 'Operational Network Profile' : 'Establishing national network integration'"></p>
                                </div>
                            </div>
                            <button @click="showModal = false" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                                <i class="fa-solid fa-times text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="submitForm" data-no-pjax class="p-8 space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Network Classification</label>
                                <select x-model="form.type" :disabled="modalMode === 'view'" required class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-black text-slate-800 text-sm shadow-inner transition-colors">
                                    <option value="Motorway">Motorway</option>
                                    <option value="Highway">Highway</option>
                                    <option value="Strategic Route">Strategic Route</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Road Index (e.g. M-1)</label>
                                <input type="text" x-model="form.road" :disabled="modalMode === 'view'" required placeholder="e.g. M-1" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-black text-slate-800 text-sm shadow-inner transition-colors uppercase">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Managed Distance (KM)</label>
                                <input type="number" step="0.01" x-model="form.total_km" :disabled="modalMode === 'view'" placeholder="0.0" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors uppercase">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Official Nomenclature</label>
                                <input type="text" x-model="form.road_name" :disabled="modalMode === 'view'" required placeholder="Road Name" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors uppercase">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Origin</label>
                                <input type="text" x-model="form.road_from" :disabled="modalMode === 'view'" placeholder="Start City" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors uppercase">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Destination</label>
                                <input type="text" x-model="form.road_to" :disabled="modalMode === 'view'" placeholder="End City" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors uppercase">
                            </div>
                        </div>
                        
                        <div class="flex gap-4 pt-6" x-show="modalMode !== 'view'">
                            <button type="button" @click="showModal = false" class="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-200 transition-all">Abort</button>
                            <button type="submit" :disabled="saving" class="flex-[2] py-4 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-[0_8px_20px_rgba(16,185,129,0.3)] hover:shadow-[0_8px_25px_rgba(16,185,129,0.4)] hover:-translate-y-0.5 active:scale-95 transition-all disabled:opacity-50">
                                <span x-show="!saving" x-text="modalMode === 'add' ? 'Integrate Route' : 'Update Parameters'"></span>
                                <span x-show="saving"><i class="fa-solid fa-circle-notch animate-spin mr-2"></i> Latency Sync...</span>
                            </button>
                        </div>
                        <div class="pt-6" x-show="modalMode === 'view'">
                            <button type="button" @click="showModal = false" class="w-full py-4 bg-blue-900 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl hover:-translate-y-0.5 active:scale-95 transition-all">Acknowledge</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @include('components.confirm-modal')

    </div>

    @push('scripts')
    <script>
        window.carriagewayManager = function(config) {

            return {
                items: config.items || [],
                search: '',
                filterStatus: '',
                density: 'spacious',
                typeFilter: '',
                viewMode: 'table',
                sortBy: 'road',
                sortDirection: 'asc',
                page: 1,
                perPage: 15,
                showModal: false,
                modalMode: 'add',
                showSidebar: false,
                saving: false,
                showConfirmModal: false,
                confirmLoading: false,
                selectedItem: null,
                confirmConfig: { title: '', message: '', icon: '', isDanger: false, action: null },
                form: {
                    id: null,
                    type: 'Motorway',
                    road: '',
                    road_short: '',
                    road_name: '',
                    road_from: '',
                    road_to: '',
                    total_km: ''
                },
                formErrors: {},

                getSortIcon(field) {
                    if (this.sortBy !== field) return 'fa-sort';
                    return this.sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
                },

                sortByField(field) {
                    if (this.sortBy === field) {
                        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortBy = field;
                        this.sortDirection = 'asc';
                    }
                    this.page = 1;
                },

                clearFilters() {
                    this.search = '';
                    this.typeFilter = '';
                    this.sortBy = 'road';
                    this.sortDirection = 'asc';
                    this.page = 1;
                },

                get filteredItems() {
                    let filtered = this.items.filter(item => {
                        const searchLower = this.search.toLowerCase();
                        const matchesSearch = (item.road_name || '').toLowerCase().includes(searchLower) || 
                                              (item.road || '').toLowerCase().includes(searchLower);
                        const matchesType = !this.typeFilter || item.type === this.typeFilter;
                        const matchesStatus = !this.filterStatus || item.status === this.filterStatus;
                        return matchesSearch && matchesType && matchesStatus;
                    });

                    return filtered.sort((a, b) => {
                        let fieldA = (a[this.sortBy] || '').toString().toLowerCase();
                        let fieldB = (b[this.sortBy] || '').toString().toLowerCase();
                        if (fieldA < fieldB) return this.sortDirection === 'asc' ? -1 : 1;
                        if (fieldA > fieldB) return this.sortDirection === 'asc' ? 1 : -1;
                        return 0;
                    });
                },

                get totalPages() {
                    return Math.ceil(this.filteredItems.length / this.perPage);
                },

                get pagedItems() {
                    if (this.page > this.totalPages) this.page = 1;
                    const start = (this.page - 1) * this.perPage;
                    return this.filteredItems.slice(start, start + this.perPage);
                },

                resetForm() {
                    this.form = { id: null, type: 'Motorway', road: '', road_short: '', road_name: '', road_from: '', road_to: '', total_km: '' };
                },

                viewItem(item) {
                    this.form = { ...item };
                    this.modalMode = 'view';
                    this.showModal = true;
                },

                editItem(item) {
                    this.form = { ...item };
                    this.modalMode = 'edit';
                    this.showModal = true;
                },

                confirmStatus(item) {
                    this.selectedItem = item;
                    const willBeActive = item.status !== 'active';
                    this.confirmConfig = {
                        title: willBeActive ? 'Activate Carriageway' : 'Deactivate Carriageway',
                        message: willBeActive 
                            ? `Are you sure you want to activate capability for route <strong>${item.road_name}</strong>? Operational tracking will resume.`
                            : `Are you sure you want to restrict route <strong>${item.road_name}</strong>? Capabilities may be taken offline.`,
                        icon: 'fa-power-off',
                        isDanger: !willBeActive,
                        action: async () => {
                            try {
                                const response = await fetch(`/mgmt/carriageways/${item.id}/toggle-status`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    }
                                });
                                const result = await response.json();
                                if (result.success) {
                                    item.status = result.status;
                                    showSuccess(`Route status updated to ${result.status}`);
                                } else {
                                    showError("Status synchronization failed");
                                }
                            } catch (error) {
                                showError("Status synchronization failed");
                            }
                        }
                    };
                    this.showConfirmModal = true;
                },

                confirmDelete(item) {
                    this.selectedItem = item;
                    this.confirmConfig = {
                        title: 'Purge Operational Route',
                        message: `WARNING: Purging route <strong>${item.road_name}</strong> will destroy granular capabilities across the entire route grid.<br><br>Proceed with purge?`,
                        icon: 'fa-trash-alt',
                        isDanger: true,
                        action: async () => {
                            try {
                                const response = await fetch(`/mgmt/carriageways/${item.id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    }
                                });
                                if (response.ok) {
                                    this.items = this.items.filter(i => i.id !== item.id);
                                    showSuccess('Route parameters purged.');
                                } else {
                                    showError("Purge sequence failed.");
                                }
                            } catch (error) {
                                showError("Purge sequence failed.");
                            }
                        }
                    };
                    this.showConfirmModal = true;
                },

                async executeConfirmAction() {
                    if (typeof this.confirmConfig.action === 'function') {
                        this.confirmLoading = true;
                        await this.confirmConfig.action();
                        this.confirmLoading = false;
                        this.showConfirmModal = false;
                    }
                },

                async submitForm() {
                    this.saving = true;
                    const url = this.modalMode === 'add' ? '/mgmt/carriageways' : `/mgmt/carriageways/${this.form.id}`;
                    const method = this.modalMode === 'add' ? 'POST' : 'PUT';
                    
                    try {
                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.form)
                        });
                        
                        const result = await response.json();
                        
                        if (response.ok) {
                            if (this.modalMode === 'add') {
                                this.items.unshift(result.carriageway);
                                showSuccess("Route integrated into grid");
                            } else {
                                const index = this.items.findIndex(i => i.id === this.form.id);
                                if (index !== -1) this.items[index] = result.carriageway;
                                showSuccess("Route parameters updated");
                            }
                            this.showModal = false;
                            this.resetForm();
                        } else {
                            if (result.errors) {
                                Object.values(result.errors).forEach(err => showError(err[0]));
                            } else {
                                showError(result.message || "Route integration error");
                            }
                        }
                    } catch (error) {
                        showError("Grid communication failure");
                    } finally {
                        this.saving = false;
                    }
                }
            };
        }
    </script>
    @endpush
@endsection
