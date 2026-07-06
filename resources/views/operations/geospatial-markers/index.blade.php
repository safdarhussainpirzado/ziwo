@extends('layouts.app')

@section('title', 'Geospatial Markers - NHMP 130')

@section('page-title', 'Geospatial Markers')

@section('content')


    <div x-data="kmManager(@js([
        'items' => $geospatialMarkers->items(),
        'zones' => $zones,
        'beats' => $beats
    ]), @js($stats), @js([
        'current_page' => $geospatialMarkers->currentPage(),
        'last_page'    => $geospatialMarkers->lastPage(),
        'total'        => $geospatialMarkers->total(),
        'per_page'     => $geospatialMarkers->perPage()
    ]))" class="space-y-8 relative mt-4 max-w-[1700px] mx-auto" x-cloak>


        <!-- Floating Sidebar Toggle -->
        <button @click="showSidebar = true"
            x-show="!showSidebar"
            x-transition:enter="transition ease-out duration-500 delay-100"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            class="fixed top-1/2 right-0 -translate-y-1/2 z-40 bg-gradient-to-b from-blue-600 to-blue-800 text-white p-2.5 py-6 rounded-l-2xl shadow-[0_0_30px_-5px_rgba(37,99,235,0.4)] hover:shadow-[-5px_0_40px_-5px_rgba(37,99,235,0.7)] hover:pr-4 transition-all duration-300 flex flex-col items-center gap-4 cursor-pointer" title="Open Filters">
            <i class="fas fa-sliders-h drop-shadow-lg text-sm"></i>
            <span style="writing-mode: vertical-rl;" class="text-[9px] font-black uppercase tracking-[0.3em] rotate-180 drop-shadow-md text-blue-50">Marker Filters</span>
        </button>

        <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-5 gap-4">
            <!-- Total Markers Card -->
            <div @click="status = ''; fetchData()" :class="!status ? 'card-3d-active blue' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-blue-600 shadow-[0_8px_16px_rgba(37,99,235,0.2)]">
                    <i class="fas fa-map-marker-alt text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-blue-500 uppercase">Total Markers</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="stats.total_markers"></h4>
                </div>
            </div>

            <!-- Active Markers Card -->
            <div @click="status = (status === 'active' ? '' : 'active'); fetchData()" :class="status === 'active' ? 'card-3d-active emerald' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-emerald-500 shadow-[0_8px_16px_rgba(16,185,129,0.2)]">
                    <i class="fas fa-check-circle text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-emerald-500 uppercase">Active</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="stats.active_markers"></h4>
                </div>
            </div>

            <!-- Inactive Markers Card -->
            <div @click="status = (status === 'inactive' ? '' : 'inactive'); fetchData()" :class="status === 'inactive' ? 'card-3d-active rose' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-rose-500 shadow-[0_8px_16px_rgba(225,29,72,0.2)]">
                    <i class="fas fa-times-circle text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-rose-500 uppercase">Inactive</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="stats.inactive_markers"></h4>
                </div>
            </div>

            <!-- Motorways Card -->
            <div @click="status = (status === 'motorway' ? '' : 'motorway'); fetchData()" :class="status === 'motorway' ? 'card-3d-active amber' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-amber-500 shadow-[0_8px_16px_rgba(245,158,11,0.2)]">
                    <i class="fas fa-road text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-amber-500 uppercase">Motorways</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="stats.motorways"></h4>
                </div>
            </div>

            <!-- Highways Card -->
            <div @click="status = (status === 'highway' ? '' : 'highway'); fetchData()" :class="status === 'highway' ? 'card-3d-active indigo' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-indigo-500 shadow-[0_8px_16px_rgba(79,70,229,0.2)]">
                    <i class="fas fa-earth-asia text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-indigo-500 uppercase">Highways</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="stats.highways"></h4>
                </div>
            </div>
        </div>

        <!-- MAIN CONTROL PANEL -->
        <div class="grid lg:grid-cols-12 gap-8 relative pb-20 px-4 sm:px-0">
            
            <!-- Left Column Content -->
            <div class="transition-all duration-500" :class="showSidebar ? 'lg:col-span-9' : 'lg:col-span-12'">
                <div class="bg-white rounded-[2rem] shadow-[0_10px_40_rgba(0,0,0,0.03)] border border-gray-100 overflow-hidden flex flex-col relative z-20">
                    
                    <!-- Panel Header -->
                    <div class="bg-blue-50/50 p-8 border-b border-blue-100/50">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-[1.25rem] bg-white flex items-center justify-center border border-blue-100 shadow-sm transition-transform hover:scale-105 duration-300">
                                    <i class="fas fa-satellite-dish text-3xl text-blue-600"></i>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-extrabold text-blue-900 tracking-tight flex items-center gap-3">
                                        Geospatial Registry <span class="text-lg font-bold text-slate-400" x-text="'(' + pagination.total + ' markers)'"></span>
                                    </h2>
                                    <p class="text-slate-500 text-sm font-bold mt-1">Manage physical reference points across the national network</p>
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

                                <button @click="resetForm(); modalMode = 'add'; showModal = true" class="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-black shadow-[0_8px_20px_rgba(79,70,229,0.3)] transition-all active:scale-95 group">
                                    <i class="fas fa-plus group-hover:rotate-180 transition-transform duration-500"></i> Register Marker
                                </button>
                                
                                <button @click="showSidebar = !showSidebar" class="w-12 h-12 flex items-center justify-center bg-white border border-slate-200 text-blue-600 rounded-xl hover:bg-blue-50 transition-colors shadow-sm" :title="showSidebar ? 'Hide Filters' : 'Show Filters'">
                                    <i class="fas" :class="showSidebar ? 'fa-eye-slash' : 'fa-filter'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Grid View -->
                    <div x-show="viewMode === 'grid'" x-transition class="p-8 relative">
                        <!-- Loading Overlay -->
                        <div x-show="loading" class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-50 flex items-center justify-center rounded-[2rem]">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-xs font-black text-blue-900 uppercase tracking-widest">Syncing Grid...</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                            <template x-for="item in items" :key="item.id">
                                <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8 hover:-translate-y-2 transition-all duration-300 group relative overflow-hidden">
                                    <div class="absolute -right-4 -top-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                                        <i class="fa-solid fa-location-dot text-9xl text-blue-900"></i>
                                    </div>
                                    
                                    <div class="flex items-start justify-between mb-8 relative z-10">
                                        <div class="w-20 h-20 rounded-[1.75rem] p-1 flex items-center justify-center shadow-lg group-hover:rotate-6 transition-transform bg-blue-600">
                                            <div class="w-full h-full rounded-[1.5rem] bg-blue-900 flex flex-col items-center justify-center text-white">
                                                <span class="text-[8px] font-black opacity-60 uppercase mb-0.5" x-text="item.road_name || 'MW'"></span>
                                                <span class="text-lg font-black tracking-tighter" x-text="item.km_marker || '0'"></span>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="relative z-10">
                                        <h4 class="text-2xl font-extrabold text-blue-900 tracking-tight leading-tight mb-1" x-text="item.location_name || 'TRANSIT POINT'"></h4>
                                        <div class="flex items-center gap-2 mb-6">
                                            <span class="text-xs font-black text-blue-600 uppercase tracking-widest">Geospatial Token</span>
                                        </div>

                                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 mb-8">
                                            <div class="space-y-3">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Zone Alignment</span>
                                                    <span class="text-[10px] font-bold text-blue-900 uppercase" x-text="item.zone_name || 'N/A'"></span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Sector Cover</span>
                                                    <span class="text-[10px] font-bold text-blue-900 uppercase" x-text="item.sector_name || 'N/A'"></span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Beat Assign</span>
                                                    <span class="text-[10px] font-bold text-blue-900 uppercase" x-text="item.beat_name || 'N/A'"></span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Contact</span>
                                                    <span class="text-[10px] font-bold text-emerald-600 uppercase" x-text="item.contact_numbers || 'N/A'"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pt-5 border-t border-slate-100 space-y-3">
                                            <div class="flex items-center gap-3 w-full bg-slate-50 p-3 rounded-xl border border-slate-100 mb-3">
                                                <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 flex items-center justify-center shadow-sm">
                                                    <i class="fa-solid fa-satellite-dish text-xs"></i>
                                                </div>
                                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Network Mark</span>
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
                    <div x-show="viewMode === 'table'" x-transition class="overflow-x-auto shadow-inner bg-white rounded-3xl border border-slate-100 relative min-h-[400px]">
                        <!-- Loading Overlay -->
                        <div x-show="loading" class="absolute inset-0 bg-white/60 backdrop-blur-[2px] z-50 flex items-center justify-center">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-xs font-black text-blue-900 uppercase tracking-widest">Refining Table...</span>
                            </div>
                        </div>

                        <table class="w-full text-left" :class="density === 'condensed' ? 'condensed-table' : 'spacious-table'">
                            <thead class="bg-blue-50 border-b border-blue-100">
                                <tr>
                                    <th class="px-4 py-4 border-b border-slate-50">
                                        <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-satellite-dish text-[10px]"></i></div>
                                            <span>Marker Identity</span>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50">
                                        <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-ruler text-[10px]"></i></div>
                                            <span>Spatial Value (KM)</span>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50 text-center">
                                        <div class="flex items-center justify-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-sitemap text-[10px]"></i></div>
                                            <span>Hierarchy</span>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50 text-center">
                                        <div class="flex items-center justify-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-phone text-[10px]"></i></div>
                                            <span>Contact</span>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50 text-center">
                                        <div class="flex items-center justify-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-toggle-on text-[10px]"></i></div>
                                            <span>Status</span>
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
                                <template x-for="item in items" :key="item.id">
                                    <tr class="hover:bg-blue-50/40 transition-colors group">
                                        <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-lg bg-blue-900 text-white flex items-center justify-center font-black text-[10px] uppercase" x-text="item.road_name || 'MW'"></div>
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-blue-900 text-sm" x-text="item.location_name || 'TRANSIT POINT'"></span>
                                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight" x-text="item.road_name || 'Arterial Road'"></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <span class="text-xs font-black text-blue-600 bg-blue-50 px-2 py-1 rounded" x-text="'KM ' + item.km_marker"></span>
                                        </td>
                                        <td class="px-6 text-center" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="px-2 py-0.5 bg-slate-100 rounded text-[8px] font-black text-slate-500 uppercase tracking-tighter" x-text="'Z: ' + (item.zone_name || 'N/A')"></span>
                                                <span class="px-2 py-0.5 bg-slate-900 rounded text-[8px] font-black text-white uppercase tracking-tighter" x-text="'S: ' + (item.sector_name || 'N/A')"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 text-center" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <span class="text-[10px] font-bold text-emerald-600 whitespace-pre-wrap leading-tight" x-text="item.contact_numbers || 'N/A'"></span>
                                        </td>

                                        <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <button @click="confirmStatus(item)" :class="item.status === 'active' ? 'bg-blue-600 text-white shadow-[0_4px_12px_rgba(37,99,235,0.3)]' : 'bg-slate-200 text-slate-500'" class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest transition-all active:scale-95">
                                                <span x-text="item.status"></span>
                                            </button>
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
                    <div x-show="pagination.last_page > 1" class="p-8 border-t border-slate-100 bg-slate-50/30 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs font-bold text-slate-400">
                            Showing <span class="text-blue-900" x-text="((pagination.current_page - 1) * perPage) + 1"></span> to <span class="text-blue-900" x-text="Math.min(pagination.current_page * perPage, pagination.total)"></span> of <span class="text-blue-900" x-text="pagination.total"></span> Marker Tokens
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- First/Next -->
                            <button @click="page = 1" :disabled="page === 1" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-400 disabled:opacity-30 hover:bg-slate-50 transition-all font-black text-[10px] uppercase">First</button>
                            <button @click="page++" :disabled="page === pagination.last_page" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition-all"><i class="fa-solid fa-chevron-right"></i></button>
                            
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
                            <button @click="page = pagination.last_page" :disabled="page === pagination.last_page" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-400 disabled:opacity-30 hover:bg-slate-50 transition-all font-black text-[10px] uppercase">Last</button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div x-show="items.length === 0 && !loading" class="py-32 text-center bg-white">
                        <i class="fa-solid fa-satellite-dish text-6xl text-slate-100 mb-6 animate-pulse"></i>
                        <h3 class="text-2xl font-black text-blue-900" x-text="search ? 'No spatial matches' : 'Spatial Registry Empty'"></h3>
                        <p class="text-slate-400 font-bold mt-2" x-text="search ? 'Refine your coordinate parameters.' : 'Network contains no registered geospatial markers.'"></p>
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
                                <h2 class="font-extrabold text-blue-900 text-lg tracking-tight">Intelligence</h2>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Refine Spatial Scope</p>
                            </div>
                        </div>
                        <button @click="showSidebar = false" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 shadow-sm flex items-center justify-center">
                            <i class="fas fa-angle-right"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Search -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-search text-blue-500"></i> Localize Mark
                            </label>
                            <div class="relative">
                                <input type="text" x-model="search" placeholder="Search name/km..." class="w-full pl-11 pr-4 py-3 bg-white border-2 border-slate-100 rounded-xl focus:border-blue-500 outline-none font-bold text-slate-700 text-sm transition-all focus:shadow-lg focus:shadow-blue-500/10">
                                <i class="fas fa-compass absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            </div>
                        </div>

                        <!-- Road Name Filter -->
                        <div class="space-y-3 pt-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-road text-blue-500"></i> Road Filter
                            </label>
                            <input type="text" x-model.debounce.400="search" placeholder="e.g. M-1, N-5, KKH..." class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs transition-all focus:border-blue-500 placeholder:font-normal">
                        </div>

                        <!-- Zone Filter -->
                        <div class="space-y-3 pt-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-satellite-dish text-amber-500"></i> Regional Hub
                            </label>
                            <select x-model="filterZone" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs transition-all focus:border-blue-500">
                                <option value="">All Regions</option>
                                <template x-for="z in zones" :key="z">
                                    <option :value="z" x-text="z"></option>
                                </template>
                            </select>
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
                                <i class="fas fa-sort-amount-down text-blue-500"></i> Sequencing
                            </label>
                            <div class="grid grid-cols-1 gap-2">
                                <button @click="sortBy = 'km_marker'" :class="sortBy === 'km_marker' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-50 text-slate-600 '" class="px-4 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-left flex items-center justify-between transition-all">
                                    <span>KM Positioning</span>
                                    <i class="fas fa-ruler-horizontal opacity-40"></i>
                                </button>
                                <button @click="sortBy = 'loc'" :class="sortBy === 'loc' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-50 text-slate-600 '" class="px-4 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-left flex items-center justify-between transition-all">
                                    <span>Point Nomenclature</span>
                                    <i class="fas fa-tag opacity-40"></i>
                                </button>
                                <button @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'" class="mt-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-blue-100 transition-colors flex items-center justify-center gap-2">
                                    <i class="fas" :class="sortDirection === 'asc' ? 'fa-sort-numeric-up' : 'fa-sort-numeric-down'"></i>
                                    <span x-text="sortDirection === 'asc' ? 'Ascending Mark' : 'Descending Mark'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Status Quick Toggle -->
                        <div class="space-y-3 border-t border-slate-100 pt-6">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-toggle-on text-teal-500"></i> Quick Command
                            </label>
                            <div class="space-y-3">
                                <button @click="search = ''; filterZone = ''; sortBy = 'km_numeric'; sortDirection = 'asc';" class="w-full py-5 text-rose-500 hover:bg-rose-50 rounded-3xl text-[10px] font-black uppercase tracking-[0.3em] transition-all duration-300 flex items-center justify-center gap-3 active:scale-95 border-2 border-rose-100">
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
                
                <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl z-[100] overflow-hidden relative border border-slate-100" x-transition.scale.origin.center>
                    <div :class="modalMode === 'add' ? 'from-blue-600 to-blue-700' : (modalMode === 'edit' ? 'from-purple-600 to-blue-700' : 'from-slate-800 to-blue-900')" class="bg-gradient-to-br p-8 text-white relative">
                        <div class="flex items-center justify-between relative z-10">
                            <div class="flex items-center gap-5">
                                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-xl border border-white/20">
                                    <i class="fa-solid text-2xl text-white" :class="modalMode === 'add' ? 'fa-location-dot' : (modalMode === 'edit' ? 'fa-pen-circle' : 'fa-crosshairs')"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-extrabold tracking-tight" x-text="modalMode === 'add' ? 'Register Marker' : (modalMode === 'edit' ? 'Modify Spatial Mark' : 'Geospatial Intelligence')"></h3>
                                    <p class="text-blue-100 text-[10px] font-black uppercase tracking-widest mt-1" x-text="modalMode === 'view' ? 'Network Coordinate Profile' : 'Establishing geospatial reference integration'"></p>
                                </div>
                            </div>
                            <button @click="showModal = false" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                                <i class="fa-solid fa-times text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="submitForm" data-no-pjax class="p-8 space-y-6 overflow-y-auto max-h-[70vh] no-scrollbar">
                        <!-- Primary Identity -->
                        <div class="grid grid-cols-2 gap-6">
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Road / Highway Name</label>
                                <input type="text" x-model="form.road_name" :disabled="modalMode === 'view'" required placeholder="e.g. M-1, N-5, KKH" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors uppercase">
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Contact Numbers</label>
                                <input type="text" x-model="form.contact_numbers" :disabled="modalMode === 'view'" placeholder="e.g. 051-9278808" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                            </div>
                        </div>

                        <!-- Coordination -->
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">KM Positioning</label>
                                <input type="number" step="0.01" x-model="form.km_marker" :disabled="modalMode === 'view'" required placeholder="0.00" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-black text-slate-800 text-sm shadow-inner transition-colors">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Point Nomenclature</label>
                                <input type="text" x-model="form.location_name" :disabled="modalMode === 'view'" placeholder="e.g. Near Salt Range" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors uppercase">
                            </div>
                        </div>

                        <!-- Hierarchy Alignment -->
                        <div class="grid grid-cols-3 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Zone</label>
                                <select x-model="form.zone_id" :disabled="modalMode === 'view'" required class="w-full px-4 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-700 text-xs shadow-inner transition-colors">
                                    <option value="">Select Zone</option>
                                    <template x-for="z in zones" :key="z.id">
                                        <option :value="z.id" x-text="z.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Sector</label>
                                <select x-model="form.sector_id" :disabled="modalMode === 'view'" required class="w-full px-4 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-700 text-xs shadow-inner transition-colors">
                                    <option value="">Select Sector</option>
                                    <template x-for="s in filteredFormSectors" :key="s.id">
                                        <option :value="s.id" x-text="s.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Beat</label>
                                <select x-model="form.beat_id" :disabled="modalMode === 'view'" required class="w-full px-4 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-700 text-xs shadow-inner transition-colors">
                                    <option value="">Select Beat</option>
                                    <template x-for="b in filteredFormBeats" :key="b.id">
                                        <option :value="b.id" x-text="b.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex gap-4 pt-6" x-show="modalMode !== 'view'">
                            <button type="button" @click="showModal = false" class="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-200 transition-all">Abort</button>
                            <button type="submit" :disabled="saving" class="flex-[2] py-4 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-[0_8px_20px_rgba(79,70,229,0.3)] hover:shadow-[0_8px_25px_rgba(79,70,229,0.4)] hover:-translate-y-0.5 active:scale-95 transition-all disabled:opacity-50">
                                <span x-show="!saving" x-text="modalMode === 'add' ? 'Integrate Mark' : 'Update Spatiality'"></span>
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

@endsection

@push('scripts')
<script>
    window.kmManager = function(config, stats, pagination) {

            return {
                items: config.items || [],
                zones: config.zones || [],
                beats: config.beats || [],
                stats: stats || { total_markers: 0, active_markers: 0, inactive_markers: 0, motorways: 0, highways: 0 },
                pagination: pagination || { current_page: 1, last_page: 1, total: 0 },
                density: 'spacious',
                filterZone: '',
                search: '',
                status: '',
                viewMode: 'table',
                sortBy: 'km_numeric',
                sortDirection: 'asc',
                page: 1,
                perPage: 15,
                loading: false,
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
                    road_name: '',
                    km_marker: '',

                    beat_id: '',
                    sector_id: '',
                    zone_id: '',
                    location_name: ''
                },

                init() {
                    this.$watch('search', () => { this.page = 1; this.fetchData(); });
                    this.$watch('filterZone', () => { this.page = 1; this.fetchData(); });
                    this.$watch('sortBy', () => { this.fetchData(); });
                    this.$watch('sortDirection', () => { this.fetchData(); });
                    this.$watch('page', () => { this.fetchData(); });
                    this.$watch('perPage', () => { this.page = 1; this.fetchData(); });
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams({
                            page: this.page,
                            per_page: this.perPage,
                            search: this.search,
                            status: this.status,
                            zone_id: this.filterZone,
                            sort_by: this.sortBy,
                            sort_dir: this.sortDirection
                        });
                        
                        const response = await fetch(`/mgmt/geospatial-markers?${params.toString()}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        
                        if (!response.ok) throw new Error('Grid resolution failure');
                        
                        const result = await response.json();
                        this.items = result.items;
                        this.pagination = result.pagination;
                        this.stats = result.stats;
                    } catch (error) {
                        showError(error.message);
                    } finally {
                        this.loading = false;
                    }
                },

                pagesToShow() {
                    const pages = [];
                    const delta = 2;
                    const left = this.page - delta;
                    const right = this.page + delta + 1;
                    const range = [];
                    const rangeWithDots = [];
                    let l;

                    for (let i = 1; i <= this.pagination.last_page; i++) {
                        if (i === 1 || i === this.pagination.last_page || (i >= left && i < right)) {
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

                get filteredFormSectors() {
                    if (!this.form.zone_id) return this.sectors;
                    return this.sectors.filter(s => s.zone_id == this.form.zone_id);
                },

                get filteredFormBeats() {
                    if (!this.form.sector_id) return this.beats;
                    return this.beats.filter(b => b.sector_id == this.form.sector_id);
                },



                resetForm() {
                    this.form = { id: null, road_name: '', km_marker: '', beat_id: '', sector_id: '', zone_id: '', location_name: '' };
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
                        title: willBeActive ? 'Activate Marker' : 'Deactivate Marker',
                        message: willBeActive 
                            ? `Are you sure you want to activate capability for spatial marker <strong>${item.location_name}</strong>? Operational tracking will resume.`
                            : `Are you sure you want to restrict spatial marker <strong>${item.location_name}</strong>? Capabilities may be taken offline.`,
                        icon: 'fa-power-off',
                        isDanger: !willBeActive,
                        action: async () => {
                            try {
                                const response = await fetch(`/mgmt/geospatial-markers/${item.id}/toggle-status`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    }
                                });
                                const result = await response.json();
                                if (result.success) {
                                    item.status = result.status;
                                    showSuccess(`Marker status updated to ${result.status}`);
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
                        title: 'Purge Spatial Mark',
                        message: `WARNING: Purging marker <strong>${item.loc}</strong> will destroy granular capabilities at KM ${item.km_marker}.<br><br>Proceed with purge?`,
                        icon: 'fa-trash-alt',
                        isDanger: true,
                        action: async () => {
                            try {
                                const response = await fetch(`/mgmt/geospatial-markers/${item.id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    }
                                });
                                if (response.ok) {
                                    this.items = this.items.filter(i => i.id !== item.id);
                                    this.pagination.total -= 1;
                                    showSuccess('Marker parameters purged.');
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
                    const url = this.modalMode === 'add' ? '/mgmt/geospatial-markers' : `/mgmt/geospatial-markers/${this.form.id}`;
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
                            this.fetchData(); // Reload current page
                            showSuccess(this.modalMode === 'add' ? "Spatial mark integrated into grid" : "Marker parameters updated");
                            this.showModal = false;
                            this.resetForm();
                        } else {
                            if (result.errors) {
                                Object.values(result.errors).forEach(err => showError(err[0]));
                            } else {
                                showError(result.message || "Marker integration error");
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
