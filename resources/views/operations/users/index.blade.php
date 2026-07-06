@extends('layouts.app')

@section('title', 'Access Control - NHMP 130')

@section('page-title', 'Access Control')

@section('content')




    <div x-data="userManager(@js([
        'items' => $users,
        'roles' => $roles,
        'designations' => $designations,
        'zones' => $zones,
        'sectors' => $sectors,
        'beats' => $beats,
        'currentUserRole' => auth()->user()->role->name,
        'currentUserId' => auth()->id(),
    ]))" class="space-y-8 relative mt-4 max-w-[1700px] mx-auto" x-cloak>


        <!-- Floating Sidebar Toggle -->
        <button @click="showSidebar = true"
            x-show="!showSidebar"
            x-transition:enter="transition ease-out duration-500 delay-100"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            class="fixed top-1/2 right-0 -translate-y-1/2 z-40 bg-gradient-to-b from-blue-600 to-blue-800 text-white p-2.5 py-6 rounded-l-2xl shadow-[0_0_30px_-5px_rgba(59,130,246,0.4)] hover:shadow-[-5px_0_40px_-5px_rgba(59,130,246,0.7)] hover:pr-4 transition-all duration-300 flex flex-col items-center gap-4 cursor-pointer" title="Open Filters">
            <i class="fas fa-sliders-h drop-shadow-lg text-sm"></i>
            <span style="writing-mode: vertical-rl;" class="text-[9px] font-black uppercase tracking-[0.3em] rotate-180 drop-shadow-md text-blue-50">User Filters</span>
        </button>

        <!-- STATS CARDS -->
        <div class="grid grid-cols-2 md:grid-cols-6 lg:grid-cols-6 gap-4">
            <!-- HQ Administrators Card -->
            <div @click="filterStatus = (filterStatus === 'admin' ? '' : 'admin')" :class="filterStatus === 'admin' ? 'card-3d-active amber' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-amber-500 shadow-[0_8px_16px_rgba(245,158,11,0.2)]">
                    <i class="fas fa-building-shield text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-amber-500 uppercase">Administrators</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.filter(i => (i.role_name === 'operation_admin' || i.role_name === 'super_admin') && (i.scope_unit_type === 'plhq' || i.scope_unit_type === 'national' || i.scope_unit_type === 'region')).length"></h4>
                </div>
            </div>

            <!-- Zone Authority Card -->
            <div @click="filterStatus = (filterStatus === 'zone_user' ? '' : 'zone_user')" :class="filterStatus === 'zone_user' ? 'card-3d-active blue' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-blue-600 shadow-[0_8px_16px_rgba(37,99,235,0.2)]">
                    <i class="fas fa-map-location-dot text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-blue-500 uppercase">Zone Authority</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.filter(i => i.scope_unit_type === 'zone').length"></h4>
                </div>
            </div>

            <!-- Sector Authority Card -->
            <div @click="filterStatus = (filterStatus === 'sector_user' ? '' : 'sector_user')" :class="filterStatus === 'sector_user' ? 'card-3d-active indigo' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-indigo-600 shadow-[0_8px_16px_rgba(79,70,229,0.2)]">
                    <i class="fas fa-landmark text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-indigo-500 uppercase">Sectors</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.filter(i => i.scope_unit_type === 'sector').length"></h4>
                </div>
            </div>

            <!-- Beat Operations Card -->
            <div @click="filterStatus = (filterStatus === 'beat_user' ? '' : 'beat_user')" :class="filterStatus === 'beat_user' ? 'card-3d-active purple' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-purple-600 shadow-[0_8px_16px_rgba(147,51,234,0.2)]">
                    <i class="fas fa-tower-broadcast text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-purple-500 uppercase">Beats Ops</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.filter(i => i.scope_unit_type === 'beat').length"></h4>
                </div>
            </div>

            <!-- Call Center Card -->
            <div @click="filterStatus = (filterStatus === 'call_centre_user' ? '' : 'call_centre_user')" :class="filterStatus === 'call_centre_user' ? 'card-3d-active emerald' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-emerald-600 shadow-[0_8px_16px_rgba(5,150,105,0.2)]">
                    <i class="fas fa-headset text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-emerald-500 uppercase">Call Center</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.filter(i => i.scope_unit_type === 'call_center' || i.scope_unit_type === 'call_centre' || i.role_name === 'agent' || i.role_name === 'agent_supervisor').length"></h4>
                </div>
            </div>

            <!-- Inactive Card -->
            <div @click="filterStatus = (filterStatus === 'inactive' ? '' : 'inactive')" :class="filterStatus === 'inactive' ? 'card-3d-active rose' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-rose-600 shadow-[0_8px_16px_rgba(225,29,72,0.2)]">
                    <i class="fas fa-user-lock text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-rose-500 uppercase">Inactive</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.filter(i => !i.is_active).length"></h4>
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
                                    <i class="fas fa-fingerprint text-3xl text-blue-600"></i>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-extrabold text-blue-900 tracking-tight flex items-center gap-3">
                                         User Management <span class="text-lg font-bold text-slate-400" x-text="'(' + filteredItems.length + ' users)'"></span>
                                    </h2>
                                    <p class="text-slate-500 text-sm font-bold mt-1">Manage system access keys and identity parameters</p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-4 items-center">
                                <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-1.5 shadow-sm">
                                    <span class="text-[9px] font-black text-slate-400 border-r border-slate-100 pr-2 uppercase font-mono">Row Density</span>
                                    <select x-model.number="perPage" class="bg-transparent text-blue-600 text-[10px] font-black uppercase cursor-pointer outline-none focus:ring-0 border-none p-0 pr-4">
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
                                    <i class="fas fa-plus group-hover:rotate-180 transition-transform duration-500"></i> Provision Key
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
                                    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                                        <i class="fa-solid fa-id-card-clip text-9xl text-blue-900"></i>
                                    </div>
                                    
                                    <div class="flex items-start justify-between mb-8 relative z-10">
                                        <div class="w-20 h-20 rounded-[1.75rem] bg-blue-900 p-1 flex items-center justify-center shadow-lg group-hover:rotate-6 transition-transform">
                                            <div class="w-full h-full rounded-[1.5rem] bg-white flex items-center justify-center text-blue-900 font-extrabold text-2xl tracking-tight uppercase" x-text="item.username.substring(0, 2)"></div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase mb-1">Access State</div>
                                            <template x-if="item.is_active">
                                                <span class="flex items-center gap-2 text-blue-600 font-black text-[10px] tracking-widest uppercase justify-end">
                                                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span> Active
                                                </span>
                                            </template>
                                            <template x-if="!item.is_active">
                                                <span class="flex items-center gap-2 text-rose-600 font-black text-[10px] tracking-widest uppercase justify-end">
                                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Locked
                                                </span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="relative z-10">
                                        <h4 class="text-2xl font-extrabold text-blue-900 tracking-tight leading-tight mb-1" x-text="item.username"></h4>
                                        <div class="flex items-center gap-2 mb-6 text-xs font-black text-blue-600 uppercase tracking-widest" x-text="item.role ? item.role.display_name : 'Sentinel'"></div>

                                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 mb-8">
                                            <div class="space-y-4">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Designation</span>
                                                    <span class="text-xs font-bold text-blue-900" x-text="item.designation ? item.designation.name : 'System Identity'"></span>
                                                </div>
                                                <div class="flex items-center justify-between pt-4 border-t border-slate-200/50">
                                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">CNIC No</span>
                                                    <span class="text-xs font-bold text-slate-600" x-text="item.cnic || 'N/A'"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pt-5 border-t border-slate-100 space-y-3">
                                            <!-- Provisioned badge — full-width single line -->
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center shrink-0">
                                                    <i class="fa-solid fa-key text-xs"></i>
                                                </div>
                                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Provisioned</span>
                                            </div>

                                            <!-- Action Grid: 3-col × 2-row, slim w-9 h-9 matching table style -->
                                            <div class="grid grid-cols-3 gap-2">
                                                <button @click="viewDetails(item)" title="View Profile" class="w-9 h-9 rounded-xl bg-blue-500 border border-blue-600 text-white hover:bg-blue-600 active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </button>
                                                <button @click="editItem(item)" title="Modify Parameters" class="w-9 h-9 rounded-xl bg-indigo-500 border border-indigo-600 text-white hover:bg-indigo-600 active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
                                                    <i class="fa-solid fa-sliders text-xs"></i>
                                                </button>
                                                <template x-if="canManageUser(item)">
                                                    <button @click="openPasswordReset(item)" title="Force Key Rotation" class="w-9 h-9 rounded-xl bg-slate-700 border border-slate-800 text-white hover:bg-slate-800 active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
                                                        <i class="fa-solid fa-key text-xs"></i>
                                                    </button>
                                                </template>
                                                <template x-if="!canManageUser(item)">
                                                    <div title="Restricted Authority" class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 text-slate-300 flex items-center justify-center mx-auto aspect-square shrink-0 cursor-not-allowed opacity-50">
                                                        <i class="fa-solid fa-lock text-xs"></i>
                                                    </div>
                                                </template>
                                                <button @click="viewAccess(item)" title="View Access Topology" class="w-9 h-9 rounded-xl bg-purple-600 border border-purple-700 text-white hover:bg-purple-700 active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
                                                    <i class="fa-solid fa-shield-halved text-xs"></i>
                                                </button>
                                                <button @click="confirmStatus(item)" title="Toggle Active State" :class="item.is_active ? 'bg-amber-500 border-amber-600 hover:bg-amber-600' : 'bg-emerald-500 border-emerald-600 hover:bg-emerald-600'" class="w-9 h-9 rounded-xl text-white border active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
                                                    <i class="fa-solid fa-power-off text-xs"></i>
                                                </button>
                                                <button @click="confirmDelete(item)" title="Purge Identity" class="w-9 h-9 rounded-xl bg-rose-600 border border-rose-700 text-white hover:bg-rose-700 active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
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
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                                <i class="fas fa-tag text-[10px]"></i>
                                            </div>
                                            <button @click="sortByItem('username')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">
                                                Identity Handle
                                                <i class="fas text-[10px] transition-all duration-300 opacity-0 group-hover:opacity-100 fa-sort text-blue-600" :class="getSortIcon('username')"></i>
                                            </button>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50">
                                        <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                                <i class="fas fa-user-shield text-[10px]"></i>
                                            </div>
                                            <button @click="sortByItem('role_id')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">
                                                Operational Role
                                                <i class="fas text-[10px] transition-all duration-300 opacity-0 group-hover:opacity-100 fa-sort text-blue-600" :class="getSortIcon('role_id')"></i>
                                            </button>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50">
                                        <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                                <i class="fas fa-id-card text-[10px]"></i>
                                            </div>
                                            <span class="flex items-center gap-1.5 text-slate-400">
                                                User Info
                                            </span>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50">
                                        <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                                <i class="fas fa-map-marked-alt text-[10px]"></i>
                                            </div>
                                            <span class="flex items-center gap-1.5 text-slate-400">
                                                Operational Scope
                                            </span>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50 text-center">
                                        <div class="flex items-center justify-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                                <i class="fas fa-power-off text-[10px]"></i>
                                            </div>
                                            <button @click="sortByItem('is_active')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">
                                                State
                                                <i class="fas text-[10px] transition-all duration-300 opacity-0 group-hover:opacity-100 fa-sort text-blue-600" :class="getSortIcon('is_active')"></i>
                                            </button>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50 text-center">
                                        <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="item in pagedItems" :key="item.id">
                                    <tr class="hover:bg-blue-50/40 transition-colors group">
                                        <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <span class="font-black text-blue-900 text-sm uppercase tracking-wider" x-text="item.username"></span>
                                        </td>
                                        <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <span class="text-xs font-black text-blue-600 bg-blue-50 px-2 py-1 rounded" x-text="item.role ? item.role.display_name : 'Standard Operative'"></span>
                                        </td>
                                        <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-blue-900 text-sm" x-text="item.full_name || 'Personnel'"></span>
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter" x-text="item.designation ? (item.designation.name + (item.designation.bps ? ' (BPS-' + item.designation.bps + ')' : '')) : 'System Identity'"></span>
                                            </div>
                                        </td>
                                        <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold text-slate-700" x-text="item.scope_unit_name || 'National'"></span>
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest" x-text="item.scope_unit_type || 'Master'"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 text-center" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <span :class="item.is_active ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-rose-50 text-rose-600 border-rose-100'" class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border" x-text="item.is_active ? 'Active' : 'Locked'"></span>
                                        </td>
                                        <td class="px-6 text-center" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <!-- Action Grid: 3-col × 2-row icon buttons -->
                                            <div class="flex flex-wrap items-center justify-center gap-1.5 w-[120px] mx-auto">
                                                <button @click="viewDetails(item)" title="View Profile" class="w-9 h-9 bg-blue-500 border border-blue-600 text-white hover:bg-blue-600 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </button>
                                                <button @click="editItem(item)" title="Modify Parameters" class="w-9 h-9 bg-indigo-500 border border-indigo-600 text-white hover:bg-indigo-600 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                    <i class="fas fa-sliders text-xs"></i>
                                                </button>
                                                <template x-if="canManageUser(item)">
                                                    <button @click="openPasswordReset(item)" title="Rotate Key" class="w-9 h-9 bg-slate-700 border border-slate-800 text-white hover:bg-slate-800 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                        <i class="fas fa-key text-xs"></i>
                                                    </button>
                                                </template>
                                                <template x-if="!canManageUser(item)">
                                                    <div title="Restricted" class="w-9 h-9 bg-slate-50 border border-slate-100 text-slate-300 flex items-center justify-center rounded-xl opacity-50 cursor-not-allowed aspect-square shrink-0">
                                                        <i class="fas fa-lock text-xs"></i>
                                                    </div>
                                                </template>
                                                <button @click="viewAccess(item)" title="View Access Topology" class="w-9 h-9 bg-purple-600 border border-purple-700 text-white hover:bg-purple-700 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                    <i class="fas fa-shield-halved text-xs"></i>
                                                </button>
                                                <button @click="confirmStatus(item)" title="Toggle State" :class="item.is_active ? 'bg-amber-500 border-amber-600 hover:bg-amber-600' : 'bg-emerald-500 border-emerald-600 hover:bg-emerald-600'" class="w-9 h-9 text-white border transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
                                                    <i class="fa-solid fa-power-off text-xs"></i>
                                                </button>
                                                <button @click="confirmDelete(item)" title="Purge Identity" class="w-9 h-9 bg-rose-600 border border-rose-700 text-white hover:bg-rose-700 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 aspect-square shrink-0">
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
                            Showing <span class="text-blue-900" x-text="((page - 1) * perPage) + 1"></span> to <span class="text-blue-900" x-text="Math.min(page * perPage, filteredItems.length)"></span> of <span class="text-blue-900" x-text="filteredItems.length"></span> users
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
                    <div x-show="filteredItems.length === 0" class="py-32 text-center bg-white">
                        <i class="fa-solid fa-users-slash text-6xl text-slate-100 mb-6 animate-pulse"></i>
                        <h3 class="text-2xl font-black text-blue-900" x-text="search ? 'No identity matches' : 'Identity Registry Empty'"></h3>
                        <p class="text-slate-400 font-bold mt-2" x-text="search ? 'Refine your query parameters.' : 'Database contains no configured users.'"></p>
                    </div>

                </div>
            </div>

            <!-- Right Column - Operational Filters -->
            <div class="lg:col-span-3 pb-8 transition-all duration-300" x-show="showSidebar" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-4" x-cloak>
                <div class="lg:sticky lg:top-8 bg-white rounded-[2rem] shadow-xl border border-slate-100 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-blue-600 shadow-sm">
                                <i class="fas fa-filter"></i>
                            </div>
                            <div>
                                <h2 class="font-extrabold text-blue-900 text-lg tracking-tight">Intelligence</h2>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Refine Access Scope</p>
                            </div>
                        </div>
                        <button @click="showSidebar = false" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 shadow-sm flex items-center justify-center">
                            <i class="fas fa-angle-right"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-6 w-full flex-1">
                        <!-- Search -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-search text-blue-500"></i> Localize Identity
                            </label>
                            <div class="relative">
                                <input type="text" x-model="search" placeholder="Search handle/CNIC/Full Name..." class="w-full pl-11 pr-4 py-3 bg-white border-2 border-slate-100 rounded-xl focus:border-blue-500 outline-none font-bold text-slate-700 text-sm transition-all focus:shadow-lg focus:shadow-blue-500/10">
                                <i class="fas fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            </div>
                        </div>

                        <!-- Role Filter -->
                        <div class="space-y-3 pt-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-user-shield text-amber-500"></i> Authority Level
                            </label>
                            <select x-model="filterRole" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner">
                                <option value="">Global Access</option>
                                <template x-for="r in roles" :key="r.id">
                                    <option :value="r.id" x-text="r.display_name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Area Filters (Hierarchical) -->
                        <div class="space-y-4 pt-4 border-t border-slate-100">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-map-marked-alt text-blue-500"></i> Operation Area
                            </label>

                            <!-- Zone Filter -->
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Zone</label>
                                <select x-model="filterZone" @change="filterSector = ''; filterBeat = ''; filterCallCentre = ''" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner">
                                    <option value="">All Zones</option>
                                    <template x-for="z in zones" :key="z.id">
                                        <option :value="z.id" x-text="z.name"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Sector Filter -->
                            <div class="space-y-2" x-show="filterZone">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Sector</label>
                                <select x-model="filterSector" @change="filterBeat = ''" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner">
                                    <option value="">All Sectors</option>
                                    <template x-for="s in filteredSectorsForFilter" :key="s.id">
                                        <option :value="s.id" x-text="s.name"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Beat Filter -->
                            <div class="space-y-2" x-show="filterSector">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Beat</label>
                                <select x-model="filterBeat" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner">
                                    <option value="">All Beats</option>
                                    <template x-for="b in filteredBeatsForFilter" :key="b.id">
                                        <option :value="b.id" x-text="b.name"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Call Center Filter -->
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Call Center</label>
                                <select x-model="filterCallCentre" @change="filterZone = ''; filterSector = ''; filterBeat = ''" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner">
                                    <option value="">Global Call Center</option>
                                    <option value="1">National Call Center (HQ)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="space-y-4 pt-4 border-t border-slate-100">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-shield-virus text-emerald-500"></i> Status State
                            </label>
                            <div class="grid grid-cols-1 gap-2">
                                <button @click="filterStatus = ''" :class="filterStatus === '' ?
                                        'bg-blue-600 text-white shadow-md' :
                                        'bg-slate-50 text-slate-600 hover:bg-slate-100 border-2 border-slate-100'" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-left flex items-center justify-between group">
                                    <span>Global Data</span>
                                    <i class="fas fa-globe-americas transition-opacity" :class="filterStatus === '' ? 'opacity-100' : 'opacity-40'"></i>
                                </button>
                                <button @click="filterStatus = 'active'" :class="filterStatus === 'active' ?
                                        'bg-emerald-600 text-white shadow-md' :
                                        'bg-slate-50 text-slate-600 hover:bg-slate-100 border-2 border-slate-100'" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-left flex items-center justify-between group">
                                    <span>Authorized Only</span>
                                    <i class="fas fa-check-circle transition-opacity" :class="filterStatus === 'active' ? 'opacity-100' : 'opacity-40'"></i>
                                </button>
                                <button @click="filterStatus = 'inactive'" :class="filterStatus === 'inactive' ?
                                        'bg-gradient-to-r from-rose-600 to-rose-400 text-white shadow-md' :
                                        'bg-slate-50 text-slate-600 hover:bg-slate-100 border-2 border-slate-100'" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-left flex items-center justify-between group">
                                    <span>Locked Vaults</span>
                                    <i class="fas fa-lock transition-opacity" :class="filterStatus === 'inactive' ? 'opacity-100' : 'opacity-40'"></i>
                                </button>
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
                        <div class="space-y-3 pt-6 border-t border-slate-100">
                            <button @click="clearFilters()" class="w-full py-5 bg-rose-600 text-white hover:bg-rose-700 rounded-3xl text-[10px] font-black uppercase tracking-[0.3em] transition-all duration-300 flex items-center justify-center gap-3 active:scale-95 shadow-lg shadow-rose-600/20">
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

        <!-- Add/Edit Modal (HMS Original MedCare Look) -->
        <div x-show="showModal" class="fixed inset-0 z-[100] overflow-y-auto px-4 py-6" x-transition.opacity>
            <div class="flex items-center justify-center min-h-screen">
                <div class="fixed inset-0 bg-white/30 backdrop-blur-sm z-[90]" @click="showModal = false"></div>
                
                <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl z-[100] overflow-hidden relative border border-slate-100" x-transition.scale.origin.center>
                    <div :class="modalMode === 'add' ? 'from-blue-600 to-blue-700' : 'from-blue-600 to-purple-700'" class="bg-gradient-to-br p-6 text-white relative">
                        <div class="flex items-center justify-between relative z-10">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-xl border border-white/20">
                                    <i class="fa-solid fa-user-plus text-xl text-white" x-show="modalMode === 'add'"></i>
                                    <i class="fa-solid fa-user-pen text-xl text-white" x-show="modalMode === 'edit'"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-extrabold tracking-tight" x-text="modalMode === 'add' ? 'Provision Key' : 'Modify Identity'"></h3>
                                    <p class="text-blue-100 text-[9px] font-black uppercase tracking-widest mt-0.5" x-text="modalMode === 'add' ? 'Establishing national personnel integration' : 'Identity: ' + form.username"></p>
                                </div>
                            </div>
                            <button @click="showModal = false" class="absolute top-6 right-6 w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                                <i class="fa-solid fa-times text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="submitForm" data-no-pjax class="p-8 space-y-6 bg-slate-50/50">
                        <div class="grid grid-cols-2 gap-6">
                            
                            <!-- Core Identity Section -->
                            <div class="col-span-2 bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col gap-5">
                                <h4 class="text-xs font-black text-blue-900 uppercase tracking-widest flex items-center gap-2 mb-2"><i class="fas fa-fingerprint text-blue-500"></i> Core Identity</h4>
                                <div class="grid grid-cols-2 gap-5">
                                    <div class="col-span-2 sm:col-span-1">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Identity Handle (Username)</label>
                                        <input type="text" x-model="form.username" required autocomplete="username" placeholder="system.admin" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                                    </div>
                                    <div class="col-span-2 sm:col-span-1">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Authority Level (Role)</label>
                                        <select x-model="form.role_id" required class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                                            <option value="">Select Role...</option>
                                            <template x-for="r in filteredRolesForAssignment" :key="r.id">
                                                <option :value="r.id" x-text="r.display_name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information Section -->
                            <div class="col-span-2 sm:col-span-1 bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col gap-5 text-indigo-900 font-bold">
                                <h4 class="text-xs font-black text-indigo-900 uppercase tracking-widest flex items-center gap-2 mb-2"><i class="fas fa-id-card text-indigo-500"></i> Personnel Data</h4>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Full Name</label>
                                    <input type="text" x-model="form.full_name" required placeholder="John Doe" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-indigo-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">CNIC (00000-0000000-0)</label>
                                    <input type="text" x-model="form.cnic" required pattern="[0-9]{5}-[0-9]{7}-[0-9]{1}" placeholder="35202-1234567-1" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-indigo-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Email Address</label>
                                    <input type="email" x-model="form.email" required placeholder="admin@nhmp.gov.pk" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-indigo-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Mobile (03xxxxxxxxx)</label>
                                    <input type="text" x-model="form.mobile_no" required pattern="^03[0-9]{9}$" placeholder="03001234567" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-indigo-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors" title="Mobile number must start with 03 and be 11 digits long.">
                                </div>
                            </div>

                            <!-- Operational Scope Section -->
                            <div class="col-span-2 sm:col-span-1 flex flex-col gap-6">
                                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col gap-5 flex-1">
                                    <h4 class="text-xs font-black text-emerald-900 uppercase tracking-widest flex items-center gap-2 mb-2"><i class="fas fa-network-wired text-emerald-500"></i> Operational Scope</h4>
                                    
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Data Bounds</label>
                                        <select x-model="form.scope_unit_type" @change="form.scope_unit_id = ''; tempZoneId = ''; tempSectorId = ''" required class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                                            <template x-for="st in availableScopeTypes" :key="st.value">
                                                <option :value="st.value" x-text="st.label"></option>
                                            </template>
                                        </select>
                                    </div>
                                    
                                    <div x-show="form.scope_unit_type !== 'national'" x-transition class="space-y-4">
                                        
                                        <!-- Zone Selection (Required for Sector/Beat) -->
                                        <div x-show="['zone', 'sector', 'beat'].includes(form.scope_unit_type)">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Select Zone</label>
                                            <select x-model="tempZoneId" @change="tempSectorId = ''; form.scope_unit_id = (form.scope_unit_type === 'zone' ? $event.target.value : '')" :required="['zone', 'sector', 'beat'].includes(form.scope_unit_type)" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                                                <option value="">Select Zone...</option>
                                                <template x-for="z in zones" :key="z.id">
                                                    <option :value="z.id" x-text="z.name"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Sector Selection (Required for Beat, optional filter for Sector) -->
                                        <div x-show="['sector', 'beat'].includes(form.scope_unit_type)">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Select Sector</label>
                                            <select x-model="tempSectorId" @change="form.scope_unit_id = (form.scope_unit_type === 'sector' ? $event.target.value : '')" :required="['sector', 'beat'].includes(form.scope_unit_type)" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                                                <option value="">Select Sector...</option>
                                                <template x-for="s in filteredSectors" :key="s.id">
                                                    <option :value="s.id" x-text="s.name"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Final Unit Selection (Beat or Call Center) -->
                                        <div x-show="['beat', 'call_centre'].includes(form.scope_unit_type)">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2" x-text="form.scope_unit_type === 'beat' ? 'Select Beat' : 'Select Call Center'"></label>
                                            
                                            <select x-model="form.scope_unit_id" :required="form.scope_unit_type === 'beat'" x-show="form.scope_unit_type === 'beat'" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                                                <option value="">Select Beat...</option>
                                                <template x-for="b in filteredBeats" :key="b.id">
                                                    <option :value="b.id" x-text="b.name"></option>
                                                </template>
                                            </select>

                                            <select x-model="form.scope_unit_id" :required="form.scope_unit_type === 'call_centre'" x-show="form.scope_unit_type === 'call_centre'" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                                                <option value="">Select Call Center...</option>
                                                <option value="1">National Call Center (HQ)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Professional Rank (Designation)</label>
                                            <select x-model="tempDesignationName" @change="form.designation_id = ''" required class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                                                <option value="">Select Rank...</option>
                                                <template x-for="name in availableDesignationNames" :key="name">
                                                    <option :value="name" x-text="name"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <div x-show="tempDesignationName" x-transition>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Basic Pay Scale (BPS)</label>
                                            <select x-model="form.designation_id" required class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-emerald-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                                                <option value="">Select BPS Grade...</option>
                                                <template x-for="d in availableBPS" :key="d.id">
                                                    <option :value="d.id" x-text="'BPS-' + d.bps + ' (' + d.short_code + ')'"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Security Section - Only shows when adding to allow generating the first key -->
                            <!-- Security Section - Only shows when adding to allow generating the first key -->
                            <div x-show="modalMode === 'add'" class="col-span-2 bg-white p-6 rounded-3xl shadow-md border-2 border-dashed border-blue-200/60 flex flex-col gap-5 text-slate-700" :style="modalMode === 'add' ? 'background-image: radial-gradient(at top left, rgba(219, 234, 254, 0.4), transparent), radial-gradient(at bottom right, rgba(fee2e2, 0.4), transparent);' : ''">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-black text-blue-900 uppercase tracking-widest flex items-center gap-2"><i class="fas fa-key text-blue-500"></i> Generation Vault</h4>
                                    <div class="text-[9px] font-black text-blue-600 uppercase tracking-widest bg-blue-100 px-2 py-1 rounded border border-blue-200">Security Grade: Enterprise</div>
                                </div>
                                <div class="grid grid-cols-2 gap-5 relative">
                                    <div class="col-span-2 sm:col-span-1">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Initial Key</label>
                                        <input type="password" x-model="form.password" autocomplete="new-password" :required="modalMode === 'add'" placeholder="••••••••" class="w-full px-5 py-3.5 bg-white border-2 border-slate-100 focus:border-blue-500 rounded-2xl outline-none font-bold text-slate-800 text-sm transition-colors shadow-inner">
                                    </div>
                                    <div class="col-span-2 sm:col-span-1">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Validate Match</label>
                                        <input type="password" x-model="form.password_confirmation" autocomplete="new-password" :required="modalMode === 'add'" placeholder="••••••••" class="w-full px-5 py-3.5 bg-white border-2 border-slate-100 focus:border-blue-500 rounded-2xl outline-none font-bold text-slate-800 text-sm transition-colors shadow-inner">
                                    </div>
                                </div>
                                <!-- Password Guidelines -->
                                <div class="mt-2 bg-white/50 rounded-xl p-4 border border-blue-100/50">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Security Requirements</p>
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                                        <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500">
                                            <i class="fas fa-check-circle text-emerald-500"></i> Minimum 12 characters
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500">
                                            <i class="fas fa-check-circle text-emerald-500"></i> Mixed-case (A-z)
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500">
                                            <i class="fas fa-check-circle text-emerald-500"></i> Numbers & Symbols
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500">
                                            <i class="fas fa-shield-alt text-blue-500"></i> Uncompromised check
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        
                        <div class="flex gap-4 pt-4 mt-4">
                            <button type="button" @click="showModal = false" class="flex-1 px-8 py-5 bg-slate-50 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-all">Abort Profile</button>
                            <button type="submit" :disabled="saving" 
                                :class="modalMode === 'add' ? 'bg-gradient-to-r from-blue-600 to-indigo-700 shadow-blue-500/30' : 'bg-gradient-to-r from-emerald-500 to-cyan-600 shadow-emerald-500/30'"
                                class="flex-[2] px-8 py-5 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-2xl hover:-translate-y-1 transition-all active:scale-95 disabled:opacity-50 flex items-center justify-center gap-3">
                                <span x-show="!saving" x-text="modalMode === 'add' ? 'Initialize Identity' : 'Update Parameters'"></span>
                                <span x-show="saving"><i class="fa-solid fa-circle-notch animate-spin mr-2"></i> Latency Sync...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Password Reset Modal -->
        <div x-show="showPasswordModal" class="fixed inset-0 z-[100] overflow-y-auto px-4 py-6" x-transition.opacity style="display: none;">
            <div class="flex items-center justify-center min-h-screen">
                <div class="fixed inset-0 bg-white/30 backdrop-blur-sm z-[90]" @click="showPasswordModal = false"></div>
                <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md z-[100] overflow-hidden relative border border-slate-100" x-transition.scale.origin.center>
                    <div class="bg-gradient-to-br from-rose-600 to-rose-700 p-6 text-white relative">
                        <div class="flex items-center justify-between relative z-10">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-xl border border-white/20">
                                    <i class="fa-solid fa-key text-xl text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-extrabold tracking-tight">Force Key Reset</h3>
                                    <p class="text-emerald-100 text-[9px] font-black uppercase tracking-widest mt-0.5" x-text="'Target Handle: ' + (selectedItem ? selectedItem.username : '')"></p>
                                </div>
                            </div>
                            <button @click="showPasswordModal = false" class="absolute top-6 right-6 w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                                <i class="fa-solid fa-times text-lg"></i>
                            </button>
                        </div>
                    </div>
                    <form @submit.prevent="submitPasswordReset" data-no-pjax class="p-8 space-y-6">
                        <input type="text" autocomplete="username" style="display: none;" x-bind:value="selectedItem ? selectedItem.username : ''">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">New Security Key</label>
                            <input type="password" x-model="passwordForm.password" autocomplete="new-password" required placeholder="••••••••" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-rose-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Validate New Key</label>
                            <input type="password" x-model="passwordForm.password_confirmation" autocomplete="new-password" required placeholder="••••••••" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-rose-500 outline-none font-bold text-slate-800 text-sm shadow-inner transition-colors">
                        </div>
                        <!-- Password Requirements Hint -->
                        <div class="bg-rose-50 rounded-2xl p-4 border border-rose-100">
                             <p class="text-[9px] font-black text-rose-900 uppercase tracking-[0.2em] mb-2">Security Standard</p>
                             <ul class="text-[10px] font-bold text-rose-700 space-y-1">
                                 <li class="flex items-center gap-2"><i class="fas fa-check-circle text-rose-500"></i> Minimum 12 characters</li>
                                 <li class="flex items-center gap-2"><i class="fas fa-check-circle text-rose-500"></i> Must include mixed-case, numbers & symbols</li>
                             </ul>
                        </div>
                        <div class="flex gap-4 pt-6">
                            <button type="button" @click="showPasswordModal = false" class="flex-1 px-8 py-5 bg-slate-50 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-all">Abort</button>
                            <button type="submit" :disabled="saving" class="flex-[2] px-8 py-5 bg-gradient-to-r from-rose-600 to-rose-700 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-2xl shadow-rose-500/30 hover:-translate-y-1 transition-all active:scale-95 disabled:opacity-50 flex items-center justify-center gap-3">
                                <span x-show="!saving">Execute Override</span>
                                <span x-show="saving"><i class="fa-solid fa-circle-notch animate-spin mr-2"></i> Syncing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Access Viewer Modal -->
        <div x-show="showAccessModal" class="fixed inset-0 z-[100] overflow-y-auto px-4 py-6" x-transition.opacity style="display: none;">
            <div class="flex items-center justify-center min-h-screen">
                <div class="fixed inset-0 bg-white/30 backdrop-blur-sm z-[90]" @click="showAccessModal = false"></div>
                <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-4xl z-[100] overflow-hidden relative border border-slate-100" x-transition.scale.origin.center>
                    <!-- Access Topology Header -->
                    <div class="bg-gradient-to-br from-amber-400 via-orange-500 to-rose-500 p-8 relative overflow-hidden text-white">
                        <div class="absolute inset-0 bg-grid-white/[0.1] bg-[size:16px_16px]"></div>
                        <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/20 rounded-full blur-[40px]"></div>
                        <div class="relative z-10 flex items-center gap-5">
                            <div class="w-12 h-12 shrink-0 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white border border-white/30 shadow-xl">
                                <i class="fa-solid fa-shield-halved text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black tracking-widest italic scale-y-110 uppercase leading-none" x-text="(selectedItem ? selectedItem.username : '') + ' - Permissions'"></h3>
                                <p class="text-white/80 text-[10px] font-black uppercase tracking-[0.3em] flex items-center gap-2 mt-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                    Operational Authorization Protocol
                                </p>
                            </div>
                        </div>
                        <button @click="showAccessModal = false" class="absolute top-6 right-6 w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                            <i class="fa-solid fa-times text-lg text-white"></i>
                        </button>
                    </div>
                    <div class="p-8 bg-white min-h-[300px] max-h-[60vh] overflow-y-auto w-full">
                        <template x-if="selectedItem && selectedItem.role">
                            <div class="space-y-6">
                                <div class="flex items-center gap-3 mb-6">
                                    <span class="px-3 py-1 bg-amber-100 text-amber-700 font-black text-xs uppercase tracking-widest rounded-lg" x-text="'Bound Authority: ' + selectedItem.role.display_name"></span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <template x-if="selectedItem.role.permissions && selectedItem.role.permissions.length > 0">
                                        <template x-for="perm in selectedItem.role.permissions" :key="perm.id">
                                            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start gap-3">
                                                <div class="mt-1 w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.8)]"></div>
                                                <div>
                                                    <div class="text-sm font-bold text-slate-800" x-text="perm.display_name"></div>
                                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1" x-text="perm.name"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </template>
                                    <template x-if="!selectedItem.role.permissions || selectedItem.role.permissions.length === 0">
                                        <div class="col-span-full py-12 text-center text-slate-400 font-bold">
                                            <i class="fa-solid fa-ban text-4xl mb-4 opacity-50"></i>
                                            <p>No granular capabilities bound to this authority level directly.</p>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex gap-4 pt-8">
                                    <!-- <button @click="showAccessModal = false" class="flex-1 px-8 py-5 bg-slate-50 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-all">Abort Protocol</button> -->
                                    <button @click="showAccessModal = false" class="flex-[2] px-8 py-5 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-2xl shadow-orange-500/30 hover:-translate-y-1 transition-all active:scale-95">Close</button>
                                </div>
                            </div>
                        </template>
                        <template x-if="!selectedItem || !selectedItem.role">
                            <div class="py-12 text-center text-slate-400 font-bold">
                                <i class="fa-solid fa-user-lock text-4xl mb-4 opacity-50"></i>
                                <p>Identity possesses no explicit authority level assignments.</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Details Modal -->
        <div x-show="showViewModal" class="fixed inset-0 z-[100] overflow-y-auto px-4 py-6" x-transition.opacity style="display: none;">
            <div class="flex items-center justify-center min-h-screen">
                <div class="fixed inset-0 bg-white/30 backdrop-blur-sm z-[90]" @click="showViewModal = false"></div>
                <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl z-[100] overflow-hidden relative border border-slate-100" x-transition.scale.origin.center>
                    <!-- View User Header -->
                    <div class="bg-gradient-to-br from-emerald-400 via-teal-500 to-cyan-500 p-8 relative overflow-hidden text-white">
                        <div class="absolute inset-0 bg-grid-white/[0.1] bg-[size:16px_16px]"></div>
                        <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/20 rounded-full blur-[40px]"></div>
                        <div class="relative z-10 flex items-center gap-5">
                            <div class="w-12 h-12 shrink-0 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white border border-white/30 shadow-xl">
                                <i class="fa-solid fa-fingerprint text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-black tracking-widest italic scale-y-110 uppercase leading-none" x-text="selectedItem ? selectedItem.username : 'Unknown'"></h3>
                                <p class="text-white/80 text-[10px] font-black uppercase tracking-[0.3em] flex items-center gap-2 mt-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                    Personnel Identity Profile
                                </p>
                            </div>
                        </div>
                        <button @click="showViewModal = false" class="absolute top-6 right-6 w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                            <i class="fa-solid fa-xmark text-lg text-white"></i>
                        </button>
                    </div>

                    <div class="p-8 space-y-6 bg-white overflow-y-auto max-h-[70vh]">
                        <template x-if="selectedItem">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Info Cards (Bento Style) -->
                                <div class="bg-slate-50/70 p-6 rounded-[2rem] border border-slate-100 transition-all hover:bg-white hover:shadow-xl hover:shadow-blue-500/5 group">
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <i class="fas fa-user-tag text-xs text-blue-500"></i>
                                        </div>
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Full Name</span>
                                    </div>
                                    <div class="font-bold text-blue-900 text-lg leading-tight" x-text="selectedItem.full_name || 'N/A'"></div>
                                </div>

                                <div class="bg-slate-50/70 p-6 rounded-[2rem] border border-slate-100 transition-all hover:bg-white hover:shadow-xl hover:shadow-blue-500/5 group">
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <i class="fas fa-envelope text-xs text-blue-500"></i>
                                        </div>
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email Address</span>
                                    </div>
                                    <div class="font-bold text-blue-900 break-all" x-text="selectedItem.email || 'N/A'"></div>
                                </div>

                                <div class="bg-slate-50/70 p-6 rounded-[2rem] border border-slate-100 transition-all hover:bg-white hover:shadow-xl hover:shadow-blue-500/5 group">
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <i class="fas fa-mobile-screen text-xs text-blue-500"></i>
                                        </div>
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Mobile Number</span>
                                    </div>
                                    <div class="font-bold text-blue-900 text-lg tabular-nums" x-text="selectedItem.mobile_no || 'N/A'"></div>
                                </div>

                                <div class="bg-slate-50/70 p-6 rounded-[2rem] border border-slate-100 transition-all hover:bg-white hover:shadow-xl hover:shadow-blue-500/5 group">
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <i class="fas fa-id-card text-xs text-blue-500"></i>
                                        </div>
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">CNIC Number</span>
                                    </div>
                                    <div class="font-bold text-blue-900 text-lg tabular-nums" x-text="selectedItem.cnic || 'N/A'"></div>
                                </div>

                                <div class="col-span-full bg-blue-50/40 p-1 rounded-[2.5rem] border border-blue-100/50">
                                    <div class="bg-white p-8 rounded-[2.25rem] border border-white shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
                                        <div class="w-full">
                                            <div class="flex items-center gap-3 mb-6">
                                                <div class="w-1.5 h-6 bg-blue-500 rounded-full"></div>
                                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Scope & Access Linkages</div>
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-8">
                                                <div>
                                                    <div class="text-[9px] font-black text-blue-500 uppercase tracking-widest mb-2">Operational Scope</div>
                                                    <div class="font-black text-slate-800 text-xl tracking-tight capitalize" x-text="selectedItem.scope_unit_type.replace('_', ' ') + ': ' + selectedItem.scope_unit_name"></div>
                                                </div>
                                                <div>
                                                    <div class="text-[9px] font-black text-indigo-500 uppercase tracking-widest mb-2">Designation Link</div>
                                                    <div class="font-black text-slate-800 text-xl tracking-tight" x-text="selectedItem.designation ? selectedItem.designation.name : 'System Identity'"></div>
                                                </div>
                                            </div>

                                                <div class="mt-8 pt-8 border-t border-slate-50 flex items-center justify-between gap-4">
                                                    <div>
                                                        <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">System Role</div>
                                                        <div class="text-xs font-black text-blue-600 bg-blue-50 px-3 py-1.5 rounded-xl border border-blue-100 inline-block" x-text="selectedItem.role ? selectedItem.role.display_name : 'No Role Assigned'"></div>
                                                    </div>
                                                    <div class="flex flex-1 gap-4">
                                                        <!-- <button @click="showViewModal = false" class="flex-1 px-8 py-5 bg-slate-50 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-all">Abort Profile</button> -->
                                                        <button @click="showViewModal = false" class="flex-[2] px-8 py-5 bg-gradient-to-r from-emerald-500 to-cyan-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-2xl hover:-translate-y-1 transition-all active:scale-95">Close</button>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        @include('components.confirm-modal')

    </div>

    @push('scripts')
    <script>
        window.userManager = function(config) {
            return {
                items: (config.items || []).map(i => {
                    const primaryScope = i.active_scopes?.[0] || i.scopes?.[0];
                    let unitName = 'National';
                    let unitType = 'national';
                    let unitId = null;
                    let parentZoneId = null;
                    let parentSectorId = null;

                    if (primaryScope && primaryScope.office) {
                        const office = primaryScope.office;
                        unitName = office.name;
                        unitType = office.type;
                        unitId = office.id;
                        
                        if (office.type === 'zone') {
                            parentZoneId = office.id;
                        } else if (office.type === 'sector') {
                            parentZoneId = office.parent_id || office.zone_id;
                            parentSectorId = office.id;
                        } else if (office.type === 'beat') {
                            parentSectorId = office.parent_id;
                            // Attempt to find zone via sector
                            const sector = config.sectors.find(s => s.id == office.parent_id);
                            if (sector) parentZoneId = sector.zone_id;
                        }
                    }
                    return { 
                        ...i, 
                        scope_unit_type: unitType,
                        scope_unit_name: unitName,
                        scope_unit_id: unitId,
                        parent_zone_id: parentZoneId,
                        parent_sector_id: parentSectorId,
                        role_name: i.role ? i.role.name : '',
                        scope_unit_type_raw: (primaryScope && primaryScope.office) ? primaryScope.office.type : 'plhq'
                    };
                }),
                roles: config.roles || [],
                designations: config.designations || [],
                zones: config.zones || [],
                sectors: config.sectors || [],
                beats: config.beats || [],
                currentUserRole: config.currentUserRole || '',
                search: '',
                filterRole: '',
                filterStatus: '',
                filterZone: '',
                filterSector: '',
                filterBeat: '',
                filterCallCentre: '',
                density: 'spacious',
                viewMode: 'table',
                sortBy: 'username',
                sortDirection: 'asc',
                page: 1,
                perPage: 10,
                showModal: false,
                modalMode: 'add',
                showSidebar: false,

                get filteredRolesForAssignment() {
                    if (this.currentUserRole === 'operation_admin') {
                        // Restricted: Cannot assign super_admin or operation_admin
                        return this.roles.filter(r => r.name !== 'super_admin' && r.name !== 'operation_admin');
                    }
                    return this.roles;
                },

                get availableScopeTypes() {
                    const allTypes = [
                        { value: 'national', label: 'National (All Access)' },
                        { value: 'zone', label: 'Zone Locked' },
                        { value: 'sector', label: 'Sector Locked' },
                        { value: 'beat', label: 'Beat Locked' },
                        { value: 'call_centre', label: 'Call Center' }
                    ];
                    if (this.currentUserRole === 'operation_admin') {
                        return allTypes.filter(t => t.value !== 'national');
                    }
                    return allTypes;
                },
                saving: false,
                showPasswordModal: false,
                showAccessModal: false,
                showViewModal: false,
                showConfirmModal: false,
                confirmLoading: false,
                selectedItem: null,
                passwordForm: {
                    password: '',
                    password_confirmation: ''
                },
                confirmConfig: {
                    title: '',
                    message: '',
                    icon: '',
                    isDanger: false,
                    action: null
                },
                tempZoneId: '',
                tempSectorId: '',
                tempDesignationName: '',

                get availableDesignationNames() {
                    return [...new Set(this.designations.map(d => d.name))];
                },

                get availableBPS() {
                    if (!this.tempDesignationName) return [];
                    return this.designations.filter(d => d.name === this.tempDesignationName);
                },

                canManageUser(item) {
                    // Users can always manage themselves
                    if (this.currentUserId === item.id) return true;
                    // Super Admin can manage everyone else
                    if (this.currentUserRole === 'super_admin') return true;
                    // Operation Admin cannot manage Super Admins or other Operation Admins
                    if (this.currentUserRole === 'operation_admin') {
                        const targetRole = item.role ? item.role.name : item.role_name;
                        return !['super_admin', 'operation_admin'].includes(targetRole);
                    }
                    return false;
                },
                get filteredSectors() {
                    if (!this.tempZoneId) return [];
                    return this.sectors.filter(s => s.zone_id == this.tempZoneId);
                },

                get filteredBeats() {
                    if (!this.tempSectorId) return [];
                    return this.beats.filter(b => b.sector_id == this.tempSectorId);
                },

                get filteredSectorsForFilter() {
                    if (!this.filterZone) return [];
                    return this.sectors.filter(s => s.zone_id == this.filterZone);
                },

                get filteredBeatsForFilter() {
                    if (!this.filterSector) return [];
                    return this.beats.filter(b => b.sector_id == this.filterSector);
                },
                form: {
                    id: null,
                    username: '',
                    full_name: '',
                    email: '',
                    mobile_no: '',
                    cnic: '',
                    scope_unit_type: (config.currentUserRole === 'operation_admin') ? 'zone' : 'national',
                    scope_unit_id: '',
                    role_id: '',
                    designation_id: '',
                    password: '',
                    password_confirmation: ''
                },

                get filteredItems() {
                    let filtered = this.items.filter(item => {
                        const searchLower = this.search.toLowerCase();
                        const matchesSearch = (item.username || '').toLowerCase().includes(searchLower) || 
                                             (item.full_name || '').toLowerCase().includes(searchLower) ||
                                             (item.cnic || '').toLowerCase().includes(searchLower) ||
                                             (item.designation && item.designation.name ? item.designation.name.toLowerCase().includes(searchLower) : false) ||
                                             (item.designation && item.designation.bps ? ('bps-' + item.designation.bps).includes(searchLower) : false);
                        const matchesRole = !this.filterRole || item.role_id == this.filterRole;
                        let matchesStatus = true;
                        
                        if (this.filterStatus === 'active') {
                            matchesStatus = item.is_active;
                        } else if (this.filterStatus === 'inactive') {
                            matchesStatus = !item.is_active;
                        } else if (this.filterStatus === 'admin') {
                            matchesStatus = item.role && (item.role.name === 'operation_admin' || item.role.name === 'super_admin');
                        } else if (this.filterStatus === 'zone_user') {
                            matchesStatus = item.scope_unit_type_raw === 'zone';
                        } else if (this.filterStatus === 'sector_user') {
                            matchesStatus = item.scope_unit_type_raw === 'sector';
                        } else if (this.filterStatus === 'beat_user') {
                            matchesStatus = item.scope_unit_type_raw === 'beat';
                        } else if (this.filterStatus === 'call_centre_user') {
                            matchesStatus = item.scope_unit_type_raw === 'call_center' || item.scope_unit_type_raw === 'call_centre';
                        } else if (this.filterStatus === 'ops') {
                            matchesStatus = ['sector', 'beat'].includes(item.scope_unit_type_raw);
                        }
                        
                        // Hierarchical & Area Filters
                        let matchesArea = true;
                        if (this.filterZone || this.filterSector || this.filterBeat || this.filterCallCentre) {
                            matchesArea = false;
                            
                            // Check if user's primary scope matches the selected filters
                            if (this.filterBeat && item.scope_unit_type === 'beat' && item.scope_unit_id == this.filterBeat) matchesArea = true;
                            else if (this.filterSector && item.scope_unit_type === 'sector' && item.scope_unit_id == this.filterSector) matchesArea = true;
                            else if (this.filterZone && item.scope_unit_type === 'zone' && item.scope_unit_id == this.filterZone) matchesArea = true;
                            else if (this.filterCallCentre && item.scope_unit_type === 'call_centre' && item.scope_unit_id == this.filterCallCentre) matchesArea = true;
                            
                            // Hierarchical Fallback: If user is in a Beat, they should show up when filtering by its parent Sector or Zone
                            if (!matchesArea) {
                                if (this.filterBeat) {
                                    // Already checked direct match above
                                } else if (this.filterSector) {
                                    if (item.parent_sector_id == this.filterSector) matchesArea = true;
                                } else if (this.filterZone) {
                                    if (item.parent_zone_id == this.filterZone) matchesArea = true;
                                }
                            }
                        }

                        return matchesSearch && matchesRole && matchesStatus && matchesArea;
                    });

                    return filtered.sort((a, b) => {
                        let fieldA = (a[this.sortBy] || '').toString().toLowerCase();
                        let fieldB = (b[this.sortBy] || '').toString().toLowerCase();
                        if (fieldA < fieldB) return this.sortDirection === 'asc' ? -1 : 1;
                        if (fieldA > fieldB) return this.sortDirection === 'asc' ? 1 : -1;
                        return 0;
                    });
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

                get totalPages() {
                    return Math.ceil(this.filteredItems.length / this.perPage);
                },

                get pagedItems() {
                    if (this.page > this.totalPages) this.page = Math.max(1, this.totalPages);
                    const start = (this.page - 1) * this.perPage;
                    return this.filteredItems.slice(start, start + this.perPage);
                },

                resetForm() {
                    this.form = { 
                        id: null, 
                        username: '', 
                        full_name: '', 
                        email: '', 
                        mobile_no: '', 
                        cnic: '',
                        scope_unit_type: this.currentUserRole === 'operation_admin' ? 'zone' : 'national', 
                        scope_unit_id: '', 
                        role_id: '', 
                        designation_id: '', 
                        password: '', 
                        password_confirmation: '' 
                    };
                    this.tempZoneId = '';
                    this.tempSectorId = '';
                },

                editItem(user) {
                    this.modalMode = 'edit';
                    const item = JSON.parse(JSON.stringify(user));
                    this.form = { ...item, password: '', password_confirmation: '' };
                    
                    // Pre-populate designation name to show BPS options
                    if (user.designation) {
                        this.tempDesignationName = user.designation.name;
                        this.form.designation_id = user.designation_id;
                    } else {
                        this.tempDesignationName = '';
                    }
                    this.tempZoneId = '';
                    this.tempSectorId = '';

                    const scopes = item.active_scopes || item.scopes || [];
                    if (scopes.length > 0) {
                        const scope = scopes[0];
                        const office = scope.office;
                        this.form.scope_unit_type = office ? office.type : 'national';
                        this.form.scope_unit_id = scope.office_id;

                        if (office) {
                            if (office.type === 'zone') {
                                this.tempZoneId = scope.office_id;
                            } else if (office.type === 'sector') {
                                this.tempZoneId = office.parent_id;
                                setTimeout(() => { 
                                    this.tempSectorId = scope.office_id; 
                                    this.form.scope_unit_id = scope.office_id;
                                }, 150);
                            } else if (office.type === 'beat') {
                                const beat = office;
                                const sector = this.sectors.find(s => s.id == beat.parent_id);
                                if (sector) {
                                    this.tempZoneId = sector.zone_id;
                                    setTimeout(() => { 
                                        this.tempSectorId = beat.parent_id; 
                                        setTimeout(() => { 
                                            this.form.scope_unit_id = scope.office_id; 
                                        }, 150);
                                    }, 150);
                                }
                            }
                        }
                    }
                    this.modalMode = 'edit';
                    this.showModal = true;
                },

                openPasswordReset(item) {
                    this.selectedItem = item;
                    this.passwordForm = { password: '', password_confirmation: '' };
                    this.showPasswordModal = true;
                },

                viewDetails(item) {
                    this.selectedItem = item;
                    this.showViewModal = true;
                },

                viewAccess(item) {
                    this.selectedItem = item;
                    this.showAccessModal = true;
                },

                clearFilters() {
                    this.search = '';
                    this.filterRole = '';
                    this.filterStatus = '';
                    this.filterZone = '';
                    this.filterSector = '';
                    this.filterBeat = '';
                    this.filterCallCentre = '';
                    this.density = 'spacious';
                    this.page = 1;
                    this.perPage = 10;
                    this.sortBy = 'username';
                    this.sortDirection = 'asc';
                },

                sortByItem(field) {
                    if (this.sortBy === field) {
                        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortBy = field;
                        this.sortDirection = 'asc';
                    }
                },

                getSortIcon(field) {
                    if (this.sortBy !== field) return 'fa-sort opacity-40 text-blue-300';
                    return this.sortDirection === 'asc' 
                        ? 'fa-sort-up opacity-100 text-blue-600 scale-125' 
                        : 'fa-sort-down opacity-100 text-blue-600 scale-125';
                },

                confirmStatus(item) {
                    this.selectedItem = item;
                    const willBeActive = !item.is_active;
                    this.confirmConfig = {
                        title: willBeActive ? 'Activate Identity' : 'Deactivate Identity',
                        message: willBeActive 
                            ? `Are you sure you want to activate access for <strong>${item.username}</strong>? They will regain system capabilities.`
                            : `Are you sure you want to lock <strong>${item.username}</strong> out of the system? Active sessions will be terminated.`,
                        icon: 'fa-power-off',
                        isDanger: !willBeActive,
                        action: async () => {
                            try {
                                const response = await fetch(`/mgmt/users/${item.id}/toggle-status`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    }
                                });
                                const result = await response.json();
                                if (result.success) {
                                    item.is_active = (result.status === 'active');
                                    showSuccess(`Identity status updated to ${result.status}`);
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
                        title: 'Purge Identity',
                        message: `WARNING: You are about to permanently purge the identity <strong>${item.username}</strong>.<br><br>This action cannot be undone.`,
                        icon: 'fa-trash-alt',
                        isDanger: true,
                        action: async () => {
                            try {
                                const response = await fetch(`/mgmt/users/${item.id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    }
                                });
                                if (response.ok) {
                                    this.items = this.items.filter(i => i.id !== item.id);
                                    showSuccess("Identity purged from system.");
                                } else {
                                    showError("Delete command failure.");
                                }
                            } catch (error) {
                                showError("Delete command failure.");
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

                async submitPasswordReset() {
                    this.saving = true;
                    try {
                        const response = await fetch(`/mgmt/users/${this.selectedItem.id}/reset-password`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.passwordForm)
                        });
                        const result = await response.json();
                        if (response.ok) {
                            showSuccess(result.message || "Security key overridden.");
                            this.showPasswordModal = false;
                        } else {
                            if (result.errors) {
                                showError(Object.values(result.errors).flat(), "Policy Violation");
                            } else {
                                showError("Key synchronization error");
                            }
                        }
                    } catch(e) {
                         showError("Key synchronization error");
                    } finally {
                         this.saving = false;
                    }
                },

                async submitForm() {
                    this.saving = true;
                    const url = this.modalMode === 'add' ? '/mgmt/users' : `/mgmt/users/${this.form.id}`;
                    const method = this.modalMode === 'add' ? 'POST' : 'PUT';
                    
                    try {
                        const payload = { ...this.form };
                        
                        // Handle current user scope assignments (simplified assume one primary scope from the modal)
                        payload.scopes = [{
                            office_id: payload.scope_unit_id || null,
                            access_level: 'full'
                        }];

                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });
                        
                        const result = await response.json();
                        
                        if (response.ok) {
                            if (this.modalMode === 'add') {
                                this.items.unshift(result.user);
                                showSuccess("Identity integrated into registry");
                            } else {
                                const index = this.items.findIndex(i => i.id === this.form.id);
                                if (index !== -1) this.items[index] = result.user;
                                showSuccess("Identity parameters updated");
                            }
                            this.showModal = false;
                            this.resetForm();
                        } else {
                            console.error('User Registry Error Payload:', result);
                            if (result.errors) {
                                showError(Object.values(result.errors).flat(), "Grid Linkage Error");
                            } else {
                                showError(result.message || "Identity integration error");
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
