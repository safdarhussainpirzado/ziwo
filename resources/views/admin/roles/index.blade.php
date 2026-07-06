@extends('layouts.app')

@section('title', 'Roles Management - NHMP 130')

@section('page-title', 'Roles Management')

@section('content')


    <div x-data="roleManager(@js([
        'items' => $roles,
        'permissions' => $permissions
    ]))" class="space-y-8 relative mt-4 max-w-[1700px] mx-auto pb-32" @keydown.escape="showDetailsModal = false; showViewModal = false; showConfirmModal = false" x-cloak>


        <!-- Floating Sidebar Toggle -->
        <button @click="showSidebar = true"
            x-show="!showSidebar && pageMode === 'index'"
            x-transition:enter="transition ease-out duration-500 delay-100"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            class="fixed top-1/2 right-0 -translate-y-1/2 z-[100] bg-gradient-to-b from-blue-500 to-blue-700 text-white p-2.5 py-6 rounded-l-2xl shadow-[0_0_30px_-5px_rgba(79,70,229,0.4)] hover:shadow-[-5px_0_40px_-5px_rgba(79,70,229,0.7)] hover:pr-4 transition-all duration-300 flex flex-col items-center gap-4 cursor-pointer" title="Open Filters">
            <i class="fas fa-sliders-h drop-shadow-lg text-sm"></i>
            <span style="writing-mode: vertical-rl;" class="text-[9px] font-black uppercase tracking-[0.3em] rotate-180 drop-shadow-md text-blue-50 border-r-0">Management Filters</span>
        </button>

        <!-- Page Mode: Index -->
        <div x-show="pageMode === 'index'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">

        <!-- STATS CARDS -->
        <div class="grid grid-cols-2 md:grid-cols-6 lg:grid-cols-6 gap-4">
            <!-- Defined Roles Card -->
            <div @click="filterStatus = ''" :class="filterStatus === '' ? 'card-3d-active blue' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer col-span-2">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-blue-600 shadow-[0_8px_16px_rgba(37,99,235,0.2)]">
                    <i class="fas fa-shield-alt text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-blue-500 uppercase mt-2">Defined Roles</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.length"></h4>
                </div>
            </div>

            <!-- Active Controls Card -->
            <div @click="filterStatus = (filterStatus === 'active' ? '' : 'active')" :class="filterStatus === 'active' ? 'card-3d-active emerald' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer col-span-2">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-emerald-500 shadow-[0_8px_16px_rgba(16,185,129,0.2)]">
                    <i class="fas fa-check-circle text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-emerald-500 uppercase mt-2">Active Controls</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.length"></h4>
                </div>
            </div>

            <!-- Permission Matrix Card -->
            <div @click="filterStatus = (filterStatus === 'matrix' ? '' : 'matrix')" :class="filterStatus === 'matrix' ? 'card-3d-active purple' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer col-span-2">
                <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-purple-500 shadow-[0_8px_16px_rgba(168,85,247,0.2)]">
                    <i class="fas fa-layer-group text-xs text-white"></i>
                </div>
                <div class="p-5 text-right pt-4">
                    <p class="text-[8px] font-black tracking-widest text-purple-500 uppercase mt-2">Permission Matrix</p>
                    <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="Object.values(permissions).flat().length"></h4>
                </div>
            </div>
        </div>

        <!-- MAIN CONTROL PANEL -->
        <div class="grid lg:grid-cols-12 gap-8 relative pb-20 px-4 sm:px-0">
            
            <!-- Left Column Content -->
            <div class="transition-all duration-500" :class="showSidebar ? 'lg:col-span-9' : 'lg:col-span-12'">
                <div class="bg-white rounded-[2rem] shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-gray-100 overflow-hidden flex flex-col relative z-20">
                    
                    <!-- Panel Header -->
                    <div class="bg-blue-50/50 p-8 border-b border-blue-100/50">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-[1.25rem] bg-white flex items-center justify-center border border-blue-100 shadow-sm transition-transform hover:scale-105 duration-300">
                                    <i class="fas fa-shield-alt text-3xl text-blue-600"></i>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-extrabold text-blue-900 tracking-tight flex items-center gap-3">
                                        Roles Registry <span class="text-lg font-bold text-slate-400" x-text="'(' + filteredItems.length + ' records)'"></span>
                                    </h2>
                                    <p class="text-slate-500 text-sm font-bold mt-1">Manage system-wide access and authority levels</p>
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

                                <button @click="addRole()" class="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-black shadow-[0_8px_20px_rgba(37,99,235,0.3)] transition-all active:scale-95 group">
                                    <i class="fas fa-plus group-hover:rotate-180 transition-transform duration-500"></i> Create Role
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
                                <div class="bg-white border-2 border-slate-200 shadow-md rounded-[2.5rem] p-8 transition-all group relative overflow-hidden">
                                    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                                        <i class="fa-solid fa-shield-halved text-9xl text-blue-900"></i>
                                    </div>
                                    
                                    <div class="flex items-start justify-between mb-8 relative z-10">
                                        <div class="w-16 h-16 rounded-[1.25rem] bg-blue-600 text-white flex items-center justify-center shadow-lg group-hover:rotate-6 transition-transform">
                                            <i class="fas fa-shield-alt text-2xl"></i>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase mb-1">Access State</div>
                                            <template x-if="item.status === 'active'">
                                                <span class="flex items-center gap-2 text-blue-600 font-black text-[10px] tracking-widest uppercase justify-end">
                                                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span> Active
                                                </span>
                                            </template>
                                            <template x-if="item.status !== 'active'">
                                                <span class="flex items-center gap-2 text-rose-600 font-black text-[10px] tracking-widest uppercase justify-end">
                                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Blocked
                                                </span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="relative z-10">
                                        <h4 class="text-2xl font-extrabold text-blue-900 tracking-tight leading-tight mb-2" x-text="item.display_name"></h4>
                                        <div class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest mb-6" x-text="item.name"></div>

                                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 mb-8 max-h-[100px] overflow-y-auto no-scrollbar">
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="perm in (item.permissions || [])" :key="perm.id">
                                                    <span class="px-2 py-1 bg-slate-200 text-slate-600 font-bold text-[9px] rounded-md uppercase tracking-wider block whitespace-nowrap mb-1 mr-1" x-text="perm.name.split('.')[1]"></span>
                                                </template>
                                                <template x-if="!(item.permissions && item.permissions.length)">
                                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest p-2">No rights bound.</span>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="pt-5 border-t border-slate-100 space-y-3 w-full">
                                            <!-- Action Grid: 3-col × 2-row icon buttons -->
                                            <div class="grid grid-cols-3 gap-2">
                                                <button @click="viewItem(item)" title="View Permissions" class="w-9 h-9 rounded-xl bg-emerald-500 border border-emerald-600 text-white hover:bg-emerald-600 active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </button>
                                                <button @click="editItem(item)" title="Edit Role" class="w-9 h-9 rounded-xl bg-blue-500 border border-blue-600 text-white hover:bg-blue-600 active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
                                                    <i class="fa-solid fa-sliders text-xs"></i>
                                                </button>
                                                <button @click="confirmStatus(item)" title="Toggle State" :class="item.status === 'active' ? 'bg-amber-500 border-amber-600 hover:bg-amber-600' : 'bg-emerald-500 border-emerald-600 hover:bg-emerald-600'" class="w-9 h-9 rounded-xl text-white border active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
                                                    <i class="fa-solid fa-power-off text-xs"></i>
                                                </button>
                                                <button @click="confirmDelete(item)" title="Delete Role" class="w-9 h-9 rounded-xl bg-rose-600 border border-rose-700 text-white hover:bg-rose-700 active:scale-95 transition-all shadow-sm flex items-center justify-center mx-auto aspect-square shrink-0">
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
                                    <th class="px-4 py-4 border-b border-slate-50" style="width:48px">
                                        <div class="flex items-center justify-center">
                                            <div class="w-8 h-8 rounded-lg bg-white text-blue-500 border border-blue-100 flex items-center justify-center"><i class="fas fa-cube text-xs"></i></div>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50">
                                        <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-shield-halved text-[10px]"></i></div>
                                            <button @click="sortByField('display_name')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">Role Label <i class="fas text-[10px] transition-all opacity-0 group-hover:opacity-100 text-blue-600" :class="getSortIcon('display_name')"></i></button>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50">
                                        <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-id-badge text-[10px]"></i></div>
                                            <button @click="sortByField('name')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">System ID <i class="fas text-[10px] transition-all opacity-0 group-hover:opacity-100 text-blue-600" :class="getSortIcon('name')"></i></button>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50">
                                        <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-toggle-on text-[10px]"></i></div>
                                            <button @click="sortByField('status')" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors group">State <i class="fas text-[10px] transition-all opacity-0 group-hover:opacity-100 text-blue-600" :class="getSortIcon('status')"></i></button>
                                        </div>
                                    </th>
                                    <th class="px-4 py-4 border-b border-slate-50">
                                        <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100"><i class="fas fa-link text-[10px]"></i></div>
                                            <span>Bindings</span>
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
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center font-black shadow-sm group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-colors" x-text="item.id"></div>
                                        </td>
                                        <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <div class="flex flex-col">
                                                <span class="font-extrabold text-blue-900 text-base mb-0.5" x-text="item.display_name"></span>
                                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400" x-text="item.name === 'admin' ? 'Root Administrator' : 'Standard Role'"></span>
                                            </div>
                                        </td>
                                        <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <span class="text-xs font-black uppercase tracking-widest text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100" x-text="item.name"></span>
                                        </td>
                                        <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <span :class="item.status === 'active' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-rose-50 text-rose-600 border-rose-100'" class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border" x-text="item.status === 'active' ? 'Active' : 'Blocked'"></span>
                                        </td>
                                        <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <span class="text-xs font-extrabold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200 shadow-inner block w-fit" x-text="(item.permissions ? item.permissions.length : 0) + ' bindings'"></span>
                                        </td>
                                        <td class="px-6 text-center" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                            <div class="flex items-center justify-center gap-1.5 whitespace-nowrap mx-auto">
                                                <button @click="viewItem(item)" title="View Capabilities" class="w-9 h-9 bg-blue-500 border border-blue-600 text-white hover:bg-blue-600 transition-all flex items-center justify-center rounded-xl shadow-sm active:scale-95 group/btn">
                                                    <i class="fa-solid fa-eye text-[10px]"></i>
                                                </button>
                                                <button @click="configurePermissions(item)" title="Configure Permissions" class="w-9 h-9 bg-indigo-500 border border-indigo-600 text-white hover:bg-indigo-600 transition-all flex items-center justify-center rounded-xl shadow-sm active:scale-95 group/btn">
                                                    <i class="fa-solid fa-microchip text-[10px] group-hover:rotate-12 transition-transform"></i>
                                                </button>
                                                <button @click="editDetails(item)" title="Edit Basic Details" class="w-9 h-9 bg-blue-50 border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center rounded-xl shadow-sm active:scale-95 group/btn">
                                                    <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                                </button>
                                                <button @click="confirmStatus(item)" title="Toggle State" :class="item.status === 'active' ? 'bg-amber-500 border-amber-600 hover:bg-amber-600' : 'bg-emerald-500 border-emerald-600 hover:bg-emerald-600'" class="w-9 h-9 text-white border transition-all flex items-center justify-center rounded-xl shadow-sm active:scale-95">
                                                    <i class="fa-solid fa-power-off text-[10px]"></i>
                                                </button>
                                                <button @click="confirmDelete(item)" title="Delete Role" class="w-9 h-9 bg-rose-600 border border-rose-700 text-white hover:bg-rose-700 transition-all flex items-center justify-center rounded-xl shadow-sm active:scale-95">
                                                    <i class="fa-solid fa-trash-alt text-[10px]"></i>
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
                            Showing <span class="text-blue-900" x-text="((page - 1) * perPage) + 1"></span> to <span class="text-blue-900" x-text="Math.min(page * perPage, filteredItems.length)"></span> of <span class="text-blue-900" x-text="filteredItems.length"></span> Points
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
                        <i class="fa-solid fa-shield-virus text-6xl text-slate-100 mb-6 animate-pulse"></i>
                        <h3 class="text-2xl font-black text-blue-900" x-text="search ? 'No access points match' : 'Registry Empty'"></h3>
                        <p class="text-slate-400 font-bold mt-2" x-text="search ? 'Refine your query parameters.' : 'Database contains no configured authorization layers.'"></p>
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
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Filter Records</p>
                            </div>
                        </div>
                        <button @click="showSidebar = false" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 shadow-sm flex items-center justify-center">
                            <i class="fas fa-angle-right"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Search Localize Point -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-search text-blue-500"></i> Localize Role
                            </label>
                            <div class="relative">
                                <input type="text" x-model="search" placeholder="Search Role Name..." class="w-full pl-11 pr-4 py-3 bg-white border-2 border-slate-100 rounded-xl focus:border-blue-500 outline-none font-bold text-slate-700 text-sm transition-all focus:shadow-lg focus:shadow-blue-500/10">
                                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
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
                                <i class="fas fa-sort-amount-down text-blue-500"></i> Sequencing
                            </label>
                            <div class="grid grid-cols-1 gap-2">
                                <button @click="sortBy = 'display_name'" :class="sortBy === 'display_name' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-50 text-slate-600 '" class="px-4 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-left flex items-center justify-between transition-all">
                                    <span>Identity Handle</span>
                                    <i class="fas fa-signature opacity-40"></i>
                                </button>
                                <button @click="sortBy = 'name'" :class="sortBy === 'name' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-50 text-slate-600 '" class="px-4 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-left flex items-center justify-between transition-all">
                                    <span>System Code</span>
                                    <i class="fas fa-code opacity-40"></i>
                                </button>
                                <button @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'" class="mt-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-blue-100 transition-colors flex items-center justify-center gap-2">
                                    <i class="fas" :class="sortDirection === 'asc' ? 'fa-sort-alpha-up' : 'fa-sort-alpha-down'"></i>
                                    <span x-text="sortDirection === 'asc' ? 'Forward Flow' : 'Reverse Flow'"></span>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-3 pt-6 border-t border-slate-100">
                            <button @click="search = ''; sortBy = 'display_name'; sortDirection = 'asc'; filterStatus = '';" class="w-full py-5 bg-rose-600 text-white hover:bg-rose-700 rounded-3xl text-[10px] font-black uppercase tracking-[0.3em] transition-all duration-300 flex items-center justify-center gap-3 active:scale-95 shadow-lg shadow-rose-600/20">
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
    </div> <!-- End Page Mode: Index -->

        <!-- Page Mode: Permissions Matrix (Bento Styling Full Width) -->
        <div x-show="pageMode === 'permissions'" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100"
             class="space-y-8 pb-32 relative z-[10]" x-cloak>
            
            <!-- Header Bento Card -->
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-10 relative overflow-hidden">
                <div class="absolute inset-0 bg-grid-slate-900/[0.02] bg-[size:32px_32px]"></div>
                <div class="absolute -right-20 -top-20 w-96 h-96 bg-blue-50 rounded-full blur-3xl opacity-50"></div>
                <div class="absolute -left-20 -bottom-20 w-96 h-96 bg-purple-50 rounded-full blur-3xl opacity-50"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
                    <div class="flex items-center gap-8">
                        <div class="w-24 h-24 rounded-[2rem] bg-gradient-to-br from-indigo-600 to-blue-800 flex items-center justify-center text-white shadow-2xl border-4 border-white transform hover:rotate-6 transition-transform duration-500 shrink-0">
                            <i class="fas fa-microchip text-4xl drop-shadow-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-4xl font-black text-blue-900 tracking-tighter">Configure Capability Matrix</h2>
                            <p class="text-slate-500 font-bold mt-2 flex items-center gap-2 text-lg">
                                <span>Refining logic access for: </span>
                                <span class="px-4 py-1.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-blue-500/20" x-text="form.display_name"></span>
                            </p>
                        </div>
                    </div>

                    <!-- Integrated Security Notice -->
                    <div class="bg-rose-50/50 border-l-4 border-rose-500 p-6 rounded-2xl max-w-xl">
                        <div class="flex gap-4">
                            <i class="fas fa-user-shield text-rose-500 text-xl mt-1"></i>
                            <div>
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-rose-600 mb-1">Security Notice</h4>
                                <p class="text-xs font-bold text-rose-900/70 leading-relaxed">
                                    Modifying role permissions will immediately impact all users assigned to this role. Ensure you have validated the matrix before synchronization.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Capability Matrix (Full Width) -->
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 flex flex-col relative overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/30">
                    <h4 class="text-xs font-black text-blue-900 uppercase tracking-[0.2em] flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fas fa-key"></i></span>
                        Capability Matrix
                    </h4>
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-3 pr-6 border-r border-slate-200">
                            <button @click="form.permissions = []" class="text-[10px] font-black text-rose-500 uppercase tracking-widest hover:underline transition-all">Clear Matrix</button>
                            <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest px-3 py-1 bg-blue-50 rounded-lg" x-text="form.permissions.length + ' Points Bound'"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <button @click="pageMode = 'index'" class="px-6 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-200 transition-all active:scale-95">Cancel</button>
                            <button @click="submitForm()" :disabled="saving" class="px-8 py-2.5 bg-blue-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-blue-500/20 hover:-translate-y-0.5 active:scale-95 transition-all flex items-center gap-2">
                                <span x-show="!saving">Save Changes</span>
                                <span x-show="saving"><i class="fas fa-circle-notch animate-spin"></i> Syncing...</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-8 max-h-[800px] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
                        <template x-for="(perms, module) in permissions" :key="module">
                            <div class="bg-slate-50/50 rounded-3xl p-6 border border-slate-100 group hover:border-blue-200 transition-all">
                                <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-5 pb-3 border-b border-slate-200/50 flex items-center justify-between group-hover:text-blue-600">
                                    <span x-text="module.replace('_', ' ') + ' Control'"></span>
                                    <button @click="toggleModule(perms)" class="text-[8px] hover:underline" x-text="allModuleSelected(perms) ? 'Deselect Module' : 'Select Module'"></button>
                                </h5>
                                <div class="grid grid-cols-1 gap-3">
                                    <template x-for="perm in perms" :key="perm.id">
                                        <label class="flex items-center justify-between bg-white px-4 py-3.5 rounded-2xl border border-slate-100 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer group/item">
                                            <div class="flex flex-col">
                                                <span class="text-[11px] font-black text-slate-700 uppercase tracking-widest group-hover/item:text-blue-700 transition-colors" x-text="perm.name.split('.')[1] || perm.name"></span>
                                                <span class="text-[8px] font-bold text-slate-400 font-mono mt-0.5" x-text="perm.name"></span>
                                            </div>
                                            <input type="checkbox" :value="perm.id" x-model="form.permissions" class="w-5 h-5 rounded-lg border-2 border-slate-200 text-blue-600 focus:ring-blue-500 transition-all cursor-pointer">
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Bottom Sticky Action Bar -->
                <div class="p-8 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">End of Matrix</p>
                        <p class="text-xs font-bold text-slate-500 mt-1">Review all bindings before final synchronization.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button @click="pageMode = 'index'" class="px-8 py-4 bg-white border border-slate-200 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-50 transition-all active:scale-95 shadow-sm">Abort Changes</button>
                        <button @click="submitForm()" :disabled="saving" class="px-12 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-2xl shadow-blue-500/30 hover:-translate-y-1 active:scale-95 transition-all flex items-center gap-3">
                            <span x-show="!saving">Sync Logic Points</span>
                            <span x-show="saving"><i class="fas fa-circle-notch animate-spin"></i> Harmonizing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>




        <!-- Modals moved to bottom for stacking priority -->
        <div>
            <!-- View Capabilities Modal (Modernized) -->
            <div x-show="showViewModal" class="fixed inset-0 z-[10000] overflow-y-auto px-4 py-6" x-transition.opacity x-cloak>
                <div class="flex items-center justify-center min-h-screen">
                    <div class="fixed inset-0 bg-slate-900/90 backdrop-blur-md z-[9999]" @click="showViewModal = false"></div>
                    
                    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-4xl z-[10000] overflow-hidden relative border border-slate-100" x-transition.scale.origin.center>
                    <!-- Header with Dynamic Gradient -->
                    <div class="bg-gradient-to-br from-indigo-600 via-blue-700 to-blue-900 p-10 text-white relative overflow-hidden">
                        <div class="absolute inset-0 bg-grid-white/[0.08] bg-[size:20px_20px]"></div>
                        <div class="absolute -right-20 -top-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
                        
                        <div class="flex items-center justify-between relative z-10">
                            <div class="flex items-center gap-8">
                                <div class="w-20 h-20 rounded-[1.5rem] bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 shadow-2xl">
                                    <i class="fas fa-shield-halved text-4xl text-white drop-shadow-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-4xl font-black tracking-tight" x-text="viewingItem ? viewingItem.display_name : 'Role Details'"></h3>
                                    <div class="flex items-center gap-4 mt-3">
                                        <span class="px-3 py-1 bg-white/20 rounded-lg text-[10px] font-black uppercase tracking-widest border border-white/20" x-text="viewingItem ? viewingItem.name : ''"></span>
                                        <div class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-pulse"></div>
                                        <p class="text-blue-100 text-xs font-bold uppercase tracking-widest" x-text="viewingItem ? (viewingItem.permissions ? viewingItem.permissions.length : 0) + ' active logic points' : ''"></p>
                                    </div>
                                </div>
                            </div>
                            <button @click="showViewModal = false" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white/10 hover:bg-white/20 transition-all border border-white/10 group">
                                <i class="fas fa-times text-xl group-hover:rotate-90 transition-transform duration-300"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-10 bg-slate-50/50">
                        <!-- Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Authority Bound</div>
                                <div class="text-xl font-bold text-blue-900 uppercase" x-text="viewingItem ? viewingItem.scope_level : 'N/A'"></div>
                            </div>
                            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Integration State</div>
                                <div :class="viewingItem && viewingItem.status === 'active' ? 'text-emerald-600' : 'text-rose-600'" class="text-xl font-bold uppercase" x-text="viewingItem ? viewingItem.status : 'N/A'"></div>
                            </div>
                            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Direct Bindings</div>
                                <div class="text-xl font-bold text-indigo-600" x-text="viewingItem ? (viewingItem.permissions ? viewingItem.permissions.length : 0) + ' Points' : '0'"></div>
                            </div>
                        </div>

                        <label class="block text-xs font-black text-blue-900 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                            <i class="fas fa-microchip text-blue-500"></i> Logic Matrix Distribution
                        </label>

                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-h-[500px] overflow-y-auto pr-4 custom-scrollbar">
                            <template x-for="(perms, module) in (viewingItem ? groupPermissions(viewingItem.permissions) : {})" :key="module">
                                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:border-blue-200 transition-colors">
                                    <h5 class="text-[10px] font-black text-blue-800 uppercase tracking-[0.15em] mb-5 pb-3 border-b border-slate-100" x-text="module + ' Module'"></h5>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="perm in perms" :key="perm.id">
                                            <span class="px-3 py-2 bg-blue-50/50 border border-blue-100/50 text-blue-700 font-bold text-[10px] rounded-xl flex items-center gap-2 transition-all hover:bg-blue-100">
                                                <i class="fas fa-check-circle text-blue-500 text-[8px]"></i>
                                                <span x-text="perm.name.split('.')[1] || perm.name"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <div class="mt-10 pt-8 border-t border-slate-200 flex justify-end">
                            <button @click="showViewModal = false" class="px-12 py-5 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:shadow-2xl hover:shadow-blue-500/30 transition-all active:scale-95 shadow-xl">Close Definition</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Details Modal (Focused for Add/Edit Basic Info) -->
        <div x-show="showDetailsModal" class="fixed inset-0 z-[10000] overflow-y-auto px-4 py-6" x-transition.opacity x-cloak>
            <div class="flex items-center justify-center min-h-screen">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[9999]" @click="showDetailsModal = false"></div>
                
                <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-xl z-[10000] overflow-hidden relative border border-slate-100" x-transition.scale.origin.center>
                    <div class="bg-gradient-to-br from-blue-600 to-indigo-800 p-8 text-white relative">
                        <div class="absolute inset-0 bg-grid-white/[0.08] bg-[size:16px_16px]"></div>
                        <div class="flex items-center justify-between relative z-10">
                            <div class="flex items-center gap-5">
                                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 shadow-xl">
                                    <i class="fas fa-id-card text-2xl text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-black tracking-tight" x-text="modalMode === 'add' ? 'Establish New Identity' : 'Update Identity Parameters'"></h3>
                                    <p class="text-blue-100 text-[10px] font-black uppercase tracking-[0.2em] mt-1" x-text="modalMode === 'add' ? 'Initializing role protocol' : 'Refining authority bounds'"></p>
                                </div>
                            </div>
                            <button @click="showDetailsModal = false" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 transition-all border border-white/10">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-8 space-y-6">
                        <div class="space-y-6">
                            <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3 ml-1">Identity Display Name</label>
                                <input type="text" x-model="form.display_name" class="w-full bg-white border-slate-200 rounded-2xl py-4 px-6 text-sm font-bold text-slate-700 focus:ring-blue-600 focus:border-blue-600 transition-all shadow-sm" placeholder="e.g. Operation Commander">
                            </div>
                            <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3 ml-1">System Machine Name</label>
                                <input type="text" x-model="form.name" :disabled="modalMode === 'edit'" class="w-full bg-white border-slate-200 rounded-2xl py-4 px-6 text-sm font-bold text-slate-700 focus:ring-blue-600 focus:border-blue-600 transition-all shadow-sm disabled:opacity-50" placeholder="e.g. operation_commander">
                            </div>
                            <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3 ml-1">Geospatial Scope</label>
                                <select x-model="form.scope_level" class="w-full bg-white border-slate-200 rounded-2xl py-4 px-6 text-sm font-bold text-slate-700 focus:ring-blue-600 focus:border-blue-600 transition-all shadow-sm appearance-none cursor-pointer">
                                    <option value="national">National (Full Access)</option>
                                    <option value="zone">Zone (Regional)</option>
                                    <option value="sector">Sector (Granular)</option>
                                    <option value="beat">Beat (Operational)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
                            <button @click="showDetailsModal = false" class="px-8 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-200 transition-all">Cancel</button>
                            <button @click="submitForm()" :disabled="saving" class="px-10 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:shadow-lg hover:shadow-blue-500/30 transition-all active:scale-95 flex items-center gap-2">
                                <span x-show="!saving" x-text="modalMode === 'add' ? 'Create Role' : 'Save Details'"></span>
                                <span x-show="saving"><i class="fas fa-circle-notch animate-spin"></i> Processing...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('components.confirm-modal')
        </div>

    </div>

    @push('scripts')
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc; 
            border-radius: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1; 
            border-radius: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8; 
        }
    </style>
    <script>
        (function() {
            window.roleManager = function(config) {
                return {
                    // Core State
                    items: config.items || [],
                    permissions: config.permissions || {},
                    pageMode: 'index', // 'index' or 'permissions'
                    
                    // UI State
                    search: '',
                    filterStatus: '',
                    density: 'spacious',
                    viewMode: 'table',
                    sortBy: 'display_name',
                    sortDirection: 'asc',
                    page: 1,
                    perPage: 9,
                    showSidebar: false,
                    saving: false,
                    
                    // Modals State
                    showViewModal: false,
                    showDetailsModal: false,
                    showConfirmModal: false,
                    modalMode: 'add', // 'add' or 'edit'
                    viewingItem: null,
                    selectedItem: null,
                    confirmLoading: false,
                    confirmConfig: { title: '', message: '', icon: '', isDanger: false, action: null },
                    
                    // Form Data
                    form: {
                        id: null,
                        name: '',
                        display_name: '',
                        scope_level: 'national',
                        permissions: []
                    },

                    // Computed Getters
                    get filteredItems() {
                        let filtered = this.items.filter(item => {
                            const searchLower = (this.search || '').toLowerCase();
                            const matchesSearch = (item.display_name || '').toLowerCase().includes(searchLower) || 
                                                 (item.name || '').toLowerCase().includes(searchLower);
                            
                            let matchesStatus = true;
                            if (this.filterStatus === 'active') {
                                matchesStatus = item.status === 'active';
                            } else if (this.filterStatus === 'matrix') {
                                matchesStatus = item.permissions && item.permissions.length > 0;
                            }
                            
                            return matchesSearch && matchesStatus;
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

                    // Helper Methods
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

                    resetForm() {
                        this.form = { id: null, name: '', display_name: '', scope_level: 'national', permissions: [] };
                    },

                    groupPermissions(perms) {
                        if (!perms) return {};
                        return perms.reduce((acc, p) => {
                            const mod = p.name.split('.')[0] || 'other';
                            if (!acc[mod]) acc[mod] = [];
                            acc[mod].push(p);
                            return acc;
                        }, {});
                    },

                    allModuleSelected(perms) {
                        return perms.every(p => this.form.permissions.includes(p.id));
                    },

                    toggleModule(perms) {
                        const allSelected = this.allModuleSelected(perms);
                        const permIds = perms.map(p => p.id);
                        if (allSelected) {
                            this.form.permissions = this.form.permissions.filter(id => !permIds.includes(id));
                        } else {
                            permIds.forEach(id => {
                                if (!this.form.permissions.includes(id)) this.form.permissions.push(id);
                            });
                        }
                    },

                    // Action Triggers
                    addRole() {
                        this.resetForm();
                        this.modalMode = 'add';
                        this.showDetailsModal = true;
                    },

                    editDetails(item) {
                        this.form = { 
                            id: item.id,
                            name: item.name,
                            display_name: item.display_name,
                            scope_level: item.scope_level || 'national',
                            permissions: item.permissions ? item.permissions.map(p => p.id) : [] 
                        };
                        this.modalMode = 'edit';
                        this.showDetailsModal = true;
                    },

                    configurePermissions(item) {
                        this.form = { 
                            id: item.id,
                            name: item.name,
                            display_name: item.display_name,
                            scope_level: item.scope_level || 'national',
                            permissions: item.permissions ? item.permissions.map(p => p.id) : [] 
                        };
                        this.pageMode = 'permissions';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    },

                    viewItem(item) {
                        this.viewingItem = item;
                        this.showViewModal = true;
                    },

                    confirmStatus(item) {
                        this.selectedItem = item;
                        const willBeActive = item.status !== 'active';
                        this.confirmConfig = {
                            title: willBeActive ? 'Activate Role Point' : 'Deactivate Role Point',
                            message: willBeActive 
                                ? `Are you sure you want to activate the <strong>${item.display_name}</strong> authority layer? Linked identities will regain capabilities.`
                                : `Are you sure you want to lock the <strong>${item.display_name}</strong> authority layer? All identities utilizing this role will immediately lose associated capabilities.`,
                            icon: 'fa-power-off',
                            isDanger: !willBeActive,
                            action: async () => {
                                try {
                                    const response = await fetch(`/mgmt/roles/${item.id}/toggle-status`, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                        }
                                    });
                                    const result = await response.json();
                                    if (result.success) {
                                        item.status = result.status;
                                        showSuccess(`Role module state updated to ${result.status}`);
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
                            title: 'Purge Role Point',
                            message: `WARNING: You are about to completely purge the role <strong>${item.display_name}</strong>.<br><br>Any identities still bound to this role will lose their granted capabilities. This action cannot be undone.`,
                            icon: 'fa-trash-alt',
                            isDanger: true,
                            action: async () => {
                                try {
                                    const response = await fetch(`/mgmt/roles/${item.id}`, {
                                        method: 'DELETE',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                        }
                                    });
                                    const result = await response.json();
                                    if (response.ok || (result && result.success)) {
                                        this.items = this.items.filter(i => i.id !== item.id);
                                        showSuccess('Role execution matrix purged from root memory.');
                                    } else {
                                        showError("Matrix deletion failure");
                                    }
                                } catch (error) {
                                    showError("Matrix deletion failure");
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
                        // Force the data arrays to be numeric if possible.
                        this.form.permissions = (this.form.permissions || []).map(x => parseInt(x));
                        
                        const isModalSubmission = this.showDetailsModal;
                        const url = (this.modalMode === 'add' && isModalSubmission) ? '/mgmt/roles' : `/mgmt/roles/${this.form.id}`;
                        const method = (this.modalMode === 'add' && isModalSubmission) ? 'POST' : 'PUT';
                        
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
                                if (this.modalMode === 'add' && isModalSubmission) {
                                    this.items.unshift(result.role);
                                    showSuccess("Role successfully created");
                                } else {
                                    const index = this.items.findIndex(i => i.id === this.form.id);
                                    if (index !== -1) this.items[index] = result.role;
                                    showSuccess(isModalSubmission ? "Role details updated" : "Role configuration updated");
                                }
                                this.pageMode = 'index';
                                this.showDetailsModal = false;
                                this.resetForm();
                            } else {
                                if (result.errors) {
                                    Object.values(result.errors).forEach(err => showError(err[0]));
                                } else {
                                    showError(result.message || "Logic binding error");
                                }
                            }
                        } catch (error) {
                            showError("Grid communication failure");
                        } finally {
                            this.saving = false;
                        }
                    }
                };
            };
        })();
    </script>
    @endpush
@endsection
