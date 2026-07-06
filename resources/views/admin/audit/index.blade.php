@extends('layouts.app')

@section('title', 'Security Audit Vault - NHMP 130')

@section('page-title', 'Security Audit Vault')

@section('content')
<div x-data="auditManager(@js([
    'items' => $logs->items(),
    'pagination' => [
        'current_page' => $logs->currentPage(),
        'last_page' => $logs->lastPage(),
        'total' => $logs->total(),
        'per_page' => $logs->perPage()
    ]
]))" class="space-y-8 relative mt-4 max-w-[1700px] mx-auto" x-cloak>

    <!-- Floating Sidebar Toggle -->
    <button @click="showSidebar = true"
        x-show="!showSidebar"
        x-transition:enter="transition ease-out duration-500 delay-100"
        x-transition:enter-start="translate-x-full opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        class="fixed top-1/2 right-0 -translate-y-1/2 z-40 bg-gradient-to-b from-blue-600 to-blue-800 text-white p-2.5 py-6 rounded-l-2xl shadow-[0_0_30px_-5px_rgba(59,130,246,0.4)] hover:shadow-[-5px_0_40px_-5px_rgba(59,130,246,0.7)] hover:pr-4 transition-all duration-300 flex flex-col items-center gap-4 cursor-pointer" title="Open Filters">
        <i class="fas fa-sliders-h drop-shadow-lg text-sm"></i>
        <span style="writing-mode: vertical-rl;" class="text-[9px] font-black uppercase tracking-[0.3em] rotate-180 drop-shadow-md text-blue-50">Audit Filters</span>
    </button>

    <!-- STATS CARDS -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div @click="filterStatus = ''" :class="filterStatus === '' ? 'card-3d-active blue' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
            <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-blue-600 shadow-[0_8px_16px_rgba(37,99,235,0.2)]">
                <i class="fas fa-fingerprint text-xs text-white"></i>
            </div>
            <div class="p-5 text-right pt-4">
                <p class="text-[8px] font-black tracking-widest text-blue-500 uppercase">Total Events</p>
                <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="pagination.total"></h4>
            </div>
        </div>

        <div @click="filterStatus = 'created'" :class="filterStatus === 'created' ? 'card-3d-active emerald' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
            <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-emerald-500 shadow-[0_8px_16px_rgba(16,185,129,0.2)]">
                <i class="fas fa-plus-circle text-xs text-white"></i>
            </div>
            <div class="p-5 text-right pt-4">
                <p class="text-[8px] font-black tracking-widest text-emerald-500 uppercase">Creations</p>
                <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.filter(i => i.event === 'created').length + '*'"></h4>
            </div>
        </div>

        <div @click="filterStatus = 'updated'" :class="filterStatus === 'updated' ? 'card-3d-active amber' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
            <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-amber-500 shadow-[0_8px_16px_rgba(245,158,11,0.2)]">
                <i class="fas fa-pen-nib text-xs text-white"></i>
            </div>
            <div class="p-5 text-right pt-4">
                <p class="text-[8px] font-black tracking-widest text-amber-500 uppercase">Updates</p>
                <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.filter(i => i.event === 'updated').length + '*'"></h4>
            </div>
        </div>

        <div @click="filterStatus = 'deleted'" :class="filterStatus === 'deleted' ? 'card-3d-active rose' : ''" class="relative flex flex-col bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-all duration-300 cursor-pointer">
            <div class="absolute -top-4 left-4 h-10 w-10 grid place-items-center rounded-xl bg-rose-600 shadow-[0_8px_16px_rgba(225,29,72,0.2)]">
                <i class="fas fa-trash-can text-xs text-white"></i>
            </div>
            <div class="p-5 text-right pt-4">
                <p class="text-[8px] font-black tracking-widest text-rose-500 uppercase">Deletions</p>
                <h4 class="text-2xl font-extrabold text-blue-900 mt-1" x-text="items.filter(i => i.event === 'deleted').length + '*'"></h4>
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
                                <i class="fas fa-fingerprint text-3xl text-blue-600"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-extrabold text-blue-900 tracking-tight flex items-center gap-3">
                                     Security Audit Vault <span class="text-lg font-bold text-slate-400" x-text="'(' + pagination.total + ' events)'"></span>
                                </h2>
                                <p class="text-slate-500 text-sm font-bold mt-1">Real-time surveillance and integrity logging across national nodes</p>
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap gap-4 items-center">
                            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-1.5 shadow-sm">
                                <span class="text-[9px] font-black text-slate-400 border-r border-slate-100 pr-2 uppercase font-mono">Row Density</span>
                                <select x-model="density" class="bg-transparent text-blue-600 text-[10px] font-black uppercase cursor-pointer outline-none focus:ring-0 border-none p-0 pr-4">
                                    <option value="spacious">Spacious</option>
                                    <option value="condensed">Condensed</option>
                                </select>
                            </div>

                            <button @click="showSidebar = !showSidebar" class="w-12 h-12 flex items-center justify-center bg-white border border-slate-200 text-blue-600 rounded-xl hover:bg-blue-50 transition-colors shadow-sm" :title="showSidebar ? 'Hide Filters' : 'Show Filters'">
                                <i class="fas" :class="showSidebar ? 'fa-eye-slash' : 'fa-filter'"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div class="overflow-x-auto shadow-inner bg-white rounded-3xl border border-slate-100">
                    <table class="w-full text-left" :class="density === 'condensed' ? 'condensed-table' : 'spacious-table'">
                        <thead class="bg-blue-50 border-b border-blue-100">
                            <tr>
                                <th class="px-4 py-4 border-b border-slate-50">
                                    <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                            <i class="fas fa-clock text-[10px]"></i>
                                        </div>
                                        <span>Timestamp</span>
                                    </div>
                                </th>
                                <th class="px-4 py-4 border-b border-slate-50">
                                    <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                            <i class="fas fa-user-shield text-[10px]"></i>
                                        </div>
                                        <span>Identity</span>
                                    </div>
                                </th>
                                <th class="px-4 py-4 border-b border-slate-50">
                                    <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                            <i class="fas fa-shield-halved text-[10px]"></i>
                                        </div>
                                        <span>Event</span>
                                    </div>
                                </th>
                                <th class="px-4 py-4 border-b border-slate-50">
                                    <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 shadow-sm border border-blue-100">
                                            <i class="fas fa-cube text-[10px]"></i>
                                        </div>
                                        <span>Target Entity</span>
                                    </div>
                                </th>
                                <th class="px-4 py-4 border-b border-slate-50 text-center">
                                    <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Inspection</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="item in filteredItems" :key="item.id">
                                <tr class="hover:bg-blue-50/40 transition-colors group">
                                    <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                        <div class="flex flex-col">
                                            <span class="font-black text-blue-900 text-sm uppercase tracking-wider" x-text="formatDate(item.created_at)"></span>
                                            <span class="text-[9px] font-black text-blue-500 uppercase tracking-widest" x-text="formatTime(item.created_at)"></span>
                                        </div>
                                    </td>
                                    <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 font-black text-[10px] uppercase" x-text="(item.causer ? item.causer.username : 'SYS').substring(0, 2)"></div>
                                            <div class="flex flex-col">
                                                <span class="font-black text-navy-900 text-xs uppercase" x-text="item.causer ? item.causer.username : 'System Process'"></span>
                                                <span class="text-[8px] font-bold text-slate-400 italic" x-text="'IP: ' + (item.properties.ip || '127.0.0.1')"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                        <template x-if="item.event === 'created'">
                                            <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-widest border border-emerald-100 flex items-center gap-2 w-fit">
                                                <i class="fa-solid fa-plus-circle"></i> Created
                                            </span>
                                        </template>
                                        <template x-if="item.event === 'updated'">
                                            <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-600 text-[9px] font-black uppercase tracking-widest border border-amber-100 flex items-center gap-2 w-fit">
                                                <i class="fa-solid fa-pen-nib"></i> Updated
                                            </span>
                                        </template>
                                        <template x-if="item.event === 'deleted'">
                                            <span class="px-3 py-1 rounded-full bg-rose-50 text-rose-600 text-[9px] font-black uppercase tracking-widest border border-rose-100 flex items-center gap-2 w-fit">
                                                <i class="fa-solid fa-trash-can"></i> Deleted
                                            </span>
                                        </template>
                                    </td>
                                    <td class="px-6" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-blue-900 uppercase" x-text="item.subject_type.split('\\').pop()"></span>
                                            <span class="text-[9px] font-bold text-slate-400" x-text="'UID: ' + item.subject_id"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 text-center" :class="density === 'condensed' ? 'py-3' : 'py-5'">
                                        <button @click="viewDetails(item)" title="Inspect Event" class="w-9 h-9 bg-blue-500 border border-blue-600 text-white hover:bg-blue-600 transition-colors flex items-center justify-center rounded-xl shadow-sm active:scale-95 mx-auto">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div x-show="pagination.last_page > 1" class="p-8 border-t border-slate-100 bg-slate-50/30 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs font-bold text-slate-400">
                        Showing <span class="text-blue-900" x-text="((pagination.current_page - 1) * pagination.per_page) + 1"></span> to <span class="text-blue-900" x-text="Math.min(pagination.current_page * pagination.per_page, pagination.total)"></span> of <span class="text-blue-900" x-text="pagination.total"></span> events
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- First/Next -->
                        <button @click="changePage(1)" :disabled="pagination.current_page === 1" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-400 disabled:opacity-30 hover:bg-slate-50 transition-all font-black text-[10px] uppercase">First</button>
                        <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition-all"><i class="fa-solid fa-chevron-right"></i></button>
                        
                        <!-- Sliding Window -->
                        <template x-for="p in pagesToShow()" :key="p">
                            <div class="flex items-center">
                                <template x-if="p === '...'">
                                    <span class="px-2 text-slate-300 font-bold">...</span>
                                </template>
                                <template x-if="p !== '...'">
                                    <button @click="changePage(p)" :class="pagination.current_page === p ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/30' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" class="w-10 h-10 rounded-xl border font-black text-xs transition-all" x-text="p"></button>
                                </template>
                            </div>
                        </template>

                        <!-- Prev/Last -->
                        <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 disabled:opacity-30 hover:bg-slate-50 transition-all"><i class="fa-solid fa-chevron-left"></i></button>
                        <button @click="changePage(pagination.last_page)" :disabled="pagination.current_page === pagination.last_page" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-400 disabled:opacity-30 hover:bg-slate-50 transition-all font-black text-[10px] uppercase">Last</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Operational Filters -->
        <div class="lg:col-span-3 pb-8 transition-all duration-300" x-show="showSidebar" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-4">
            <div class="lg:sticky lg:top-8 bg-white rounded-[2rem] shadow-xl border border-slate-100 overflow-hidden flex flex-col">
                <div class="p-6 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-blue-600 shadow-sm">
                            <i class="fas fa-filter"></i>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-blue-900 text-lg tracking-tight">Intelligence</h2>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Surveillance Scope</p>
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
                            <i class="fas fa-search text-blue-500"></i> Entity Search
                        </label>
                        <div class="relative">
                            <input type="text" x-model="search" placeholder="Filter by Entity (User, Call)..." class="w-full pl-11 pr-4 py-3 bg-white border-2 border-slate-100 rounded-xl focus:border-blue-500 outline-none font-bold text-slate-700 text-sm transition-all focus:shadow-lg focus:shadow-blue-500/10">
                            <i class="fas fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                        </div>
                    </div>

                    <!-- Event Filter -->
                    <div class="space-y-4 pt-4 border-t border-slate-100">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-shield-virus text-emerald-500"></i> Event State
                        </label>
                        <div class="grid grid-cols-1 gap-2">
                            <button @click="filterStatus = ''" :class="filterStatus === '' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-2 border-slate-100'" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-left flex items-center justify-between group">
                                <span>Global Log</span>
                                <i class="fas fa-globe-americas transition-opacity" :class="filterStatus === '' ? 'opacity-100' : 'opacity-40'"></i>
                            </button>
                            <button @click="filterStatus = 'created'" :class="filterStatus === 'created' ? 'bg-emerald-600 text-white shadow-md' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-2 border-slate-100'" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-left flex items-center justify-between group">
                                <span>Creations</span>
                                <i class="fas fa-plus-circle transition-opacity" :class="filterStatus === 'created' ? 'opacity-100' : 'opacity-40'"></i>
                            </button>
                            <button @click="filterStatus = 'updated'" :class="filterStatus === 'updated' ? 'bg-amber-500 text-white shadow-md' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-2 border-slate-100'" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-left flex items-center justify-between group">
                                <span>Updates</span>
                                <i class="fas fa-pen-nib transition-opacity" :class="filterStatus === 'updated' ? 'opacity-100' : 'opacity-40'"></i>
                            </button>
                            <button @click="filterStatus = 'deleted'" :class="filterStatus === 'deleted' ? 'bg-rose-600 text-white shadow-md' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border-2 border-slate-100'" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-left flex items-center justify-between group">
                                <span>Deletions</span>
                                <i class="fas fa-trash-can transition-opacity" :class="filterStatus === 'deleted' ? 'opacity-100' : 'opacity-40'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Date Filters -->
                    <div class="space-y-4 pt-4 border-t border-slate-100">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-blue-500"></i> Time Window
                        </label>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase ml-1 tracking-widest">From</label>
                            <input type="date" x-model="filterFrom" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase ml-1 tracking-widest">To</label>
                            <input type="date" x-model="filterTo" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl outline-none font-bold text-slate-600 text-xs shadow-inner">
                        </div>
                    </div>

                    <!-- Density switcher -->
                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-compress-alt text-blue-500"></i> Layout Density
                        </label>
                        <div class="grid grid-cols-2 gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200/50">
                            <button @click="density = 'condensed'" :class="density === 'condensed' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="py-2 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all">Condensed</button>
                            <button @click="density = 'spacious'" :class="density === 'spacious' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="py-2 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all">Spacious</button>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 space-y-4">
                        <button @click="clearFilters()" class="w-full py-5 bg-rose-600 text-white hover:bg-rose-700 rounded-3xl text-[10px] font-black uppercase tracking-[0.3em] transition-all duration-300 flex items-center justify-center gap-3 active:scale-95 shadow-lg shadow-rose-600/20">
                            <i class="fas fa-broom"></i> Reset Filters
                        </button>
                        <button @click="showSidebar = false" class="w-full py-4 bg-blue-600 text-white rounded-3xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-700 transition-all flex items-center justify-between px-6 shadow-md shadow-blue-600/20">
                            <span>Hide Filters</span><i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div x-show="showModal" class="fixed inset-0 z-[100] overflow-y-auto px-4 py-6" x-transition.opacity>
        <div class="flex items-center justify-center min-h-screen">
            <div class="fixed inset-0 bg-white/30 backdrop-blur-sm z-[90]" @click="showModal = false"></div>
            
            <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl z-[100] overflow-hidden relative border border-slate-100" x-transition.scale.origin.center>
                <div :class="getEventColor(selectedItem?.event)" class="bg-gradient-to-br p-6 text-white relative">
                    <div class="flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-xl border border-white/20">
                                <i class="fa-solid fa-eye text-xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-extrabold tracking-tight">Event Investigation</h3>
                                <p class="text-white/70 text-[9px] font-black uppercase tracking-widest mt-0.5" x-text="'Tracking ID: ' + (selectedItem?.id || '')"></p>
                            </div>
                        </div>
                        <button @click="showModal = false" class="absolute top-6 right-6 w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                            <i class="fa-solid fa-times text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="p-8 space-y-6 bg-slate-50/50">
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Identity Metadata -->
                        <div class="col-span-2 bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col gap-5">
                            <h4 class="text-xs font-black text-blue-900 uppercase tracking-widest flex items-center gap-2 mb-2"><i class="fas fa-fingerprint text-blue-500"></i> Event Metadata</h4>
                            <div class="grid grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Executor</label>
                                    <p class="text-sm font-black text-navy-900 uppercase" x-text="selectedItem?.causer?.username || 'System'"></p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Source IP</label>
                                    <p class="text-sm font-black text-slate-600" x-text="selectedItem?.properties?.ip || '127.0.0.1'"></p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Entity Type</label>
                                    <p class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded inline-block" x-text="selectedItem?.subject_type"></p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Entity UID</label>
                                    <p class="text-sm font-black text-navy-900" x-text="selectedItem?.subject_id"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Data Diff Section -->
                        <div class="col-span-2 bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col gap-5">
                            <h4 class="text-xs font-black text-blue-900 uppercase tracking-widest flex items-center gap-2 mb-2"><i class="fas fa-code-compare text-blue-500"></i> Modification Ledger</h4>
                            
                            <template x-if="selectedItem?.event === 'updated'">
                                <div class="space-y-3">
                                    <template x-for="key in Object.keys(selectedItem.properties.attributes || {})" :key="key">
                                        <template x-if="key !== 'updated_at'">
                                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl border border-slate-100">
                                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest" x-text="key"></span>
                                                <div class="flex items-center gap-4">
                                                    <span class="text-xs font-bold text-rose-500 line-through" x-text="selectedItem.properties.old[key] ?? 'N/A'"></span>
                                                    <i class="fa-solid fa-arrow-right text-[10px] text-slate-300"></i>
                                                    <span class="text-xs font-black text-emerald-600" x-text="selectedItem.properties.attributes[key] ?? 'N/A'"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </template>
                                </div>
                            </template>

                            <template x-if="selectedItem?.event === 'created'">
                                <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 text-emerald-700 font-bold text-xs text-center">
                                    Entity successfully initialized into system registry.
                                </div>
                            </template>

                            <template x-if="selectedItem?.event === 'deleted'">
                                <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100 text-rose-700 font-bold text-xs text-center">
                                    Entity permanently purged from system vault.
                                </div>
                            </template>
                        </div>
                    </div>

                    <button @click="showModal = false" class="w-full py-5 bg-blue-600 text-white rounded-3xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-700 transition-all active:scale-95 shadow-lg shadow-blue-600/20">
                        Close Investigation
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function auditManager(config) {
    return {
        items: config.items || [],
        pagination: config.pagination || { current_page: 1, last_page: 1, total: 0, per_page: 20 },
        showSidebar: false,
        showModal: false,
        selectedItem: null,
        density: 'spacious',
        search: '',
        filterStatus: '',
        filterFrom: '',
        filterTo: '',
        
        get filteredItems() {
            return this.items.filter(item => {
                const searchLower = this.search.toLowerCase();
                const matchesSearch = !this.search || 
                                     (item.subject_type || '').toLowerCase().includes(searchLower) ||
                                     (item.subject_id || '').toString().includes(searchLower) ||
                                     (item.causer && item.causer.username ? item.causer.username.toLowerCase().includes(searchLower) : false);
                const matchesStatus = !this.filterStatus || item.event === this.filterStatus;
                
                let matchesDate = true;
                if (this.filterFrom) {
                    matchesDate = matchesDate && new Date(item.created_at) >= new Date(this.filterFrom);
                }
                if (this.filterTo) {
                    const toDate = new Date(this.filterTo);
                    toDate.setHours(23, 59, 59);
                    matchesDate = matchesDate && new Date(item.created_at) <= toDate;
                }

                return matchesSearch && matchesStatus && matchesDate;
            });
        },

        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
        },

        formatTime(dateStr) {
            return new Date(dateStr).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
        },

        getEventColor(event) {
            return {
                'created': 'from-emerald-600 to-emerald-800',
                'updated': 'from-amber-500 to-amber-700',
                'deleted': 'from-rose-600 to-rose-800'
            }[event] || 'from-blue-600 to-blue-800';
        },

        viewDetails(item) {
            this.selectedItem = item;
            this.showModal = true;
        },

        clearFilters() {
            this.search = '';
            this.filterStatus = '';
            this.filterFrom = '';
            this.filterTo = '';
        },

        changePage(p) {
            if (p < 1 || p > this.pagination.last_page) return;
            // For now, since we handle server-side pagination, we'll redirect
            const url = new URL(window.location.href);
            url.searchParams.set('page', p);
            window.location.href = url.toString();
        },

        pagesToShow() {
            const pages = [];
            const delta = 2;
            const left = this.pagination.current_page - delta;
            const right = this.pagination.current_page + delta + 1;
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
        }
    }
}
</script>

@endsection
