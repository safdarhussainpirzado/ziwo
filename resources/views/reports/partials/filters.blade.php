<div @if(!request()->routeIs(['reports.call-type-summary', 'reports.agent-wise', 'reports.junk-calls-frequency'])) x-data="reportFilters" @endif class="bg-white rounded-[2rem] shadow-xl border border-slate-100 p-8 mb-8">
    <form @submit.prevent="fetchReport" action="{{ url()->current() }}" method="GET" id="filterForm" class="space-y-8" data-no-pjax>
        <!-- ── Hierarchy & Personnel ─────────────────────────────────── -->
        @php
            $isCallTypeSummary = request()->routeIs('reports.call-type-summary');
            $isAgentWise = request()->routeIs('reports.agent-wise');
            $isJunkCalls = request()->routeIs('reports.junk-calls-frequency');
            
            $isAgent = auth()->user()?->role?->name === 'agent';
        @endphp

        @php
            if ($isCallTypeSummary) {
                $gridCols = $isAgent ? 'hidden' : 'md:grid-cols-3';
            } elseif ($isAgentWise || $isJunkCalls) {
                $gridCols = 'md:grid-cols-1';
            } else {
                $gridCols = $isAgent ? 'md:grid-cols-1' : 'md:grid-cols-4';
            }
        @endphp
        <div class="grid grid-cols-1 {{ $gridCols }} gap-6">
            @if(!$isAgent && !$isAgentWise && !$isJunkCalls)
                <!-- Zone -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Zone</label>
                    <select name="zone_id" x-model="selectedZone" @change="updateSectors()" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-navy-900 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                        <option value="">All Zones</option>
                        <template x-for="zone in zones" :key="zone.id">
                            <option :value="zone.id" x-text="zone.name" :selected="selectedZone == zone.id"></option>
                        </template>
                    </select>
                </div>

                <!-- Sector -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sector</label>
                    <select name="sector_id" x-model="selectedSector" @change="updateBeats()" :disabled="!selectedZone" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-navy-900 focus:ring-2 focus:ring-emerald-500/20 transition-all disabled:opacity-50">
                        <option value="">All Sectors</option>
                        <template x-for="sector in filteredSectors" :key="sector.id">
                            <option :value="sector.id" x-text="sector.name" :selected="selectedSector == sector.id"></option>
                        </template>
                    </select>
                </div>

                <!-- Beat -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Beat</label>
                    <select name="beat_id" x-model="selectedBeat" :disabled="!selectedSector" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-navy-900 focus:ring-2 focus:ring-emerald-500/20 transition-all disabled:opacity-50">
                        <option value="">All Beats</option>
                        <template x-for="beat in filteredBeats" :key="beat.id">
                            <option :value="beat.id" x-text="beat.name" :selected="selectedBeat == beat.id"></option>
                        </template>
                    </select>
                </div>
                @endif

                @if($isAgentWise || $isJunkCalls)
                <!-- Agent (Multiple Selection) -->
                <div class="space-y-2 relative" x-data='agentMultiSelect(@json($agents ?? []), @json(request("agent_ids", [])))' @click.outside="open = false">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 block">{{ $isJunkCalls ? 'Select Agents' : 'Agent Performance Group' }}</label>
                    
                    <!-- Trigger Button -->
                    <button type="button" @click="open = !open" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-navy-900 focus:ring-2 focus:ring-emerald-500/20 transition-all text-left flex justify-between items-center min-h-[52px]">
                        <span x-text="selectedLabel"></span>
                        <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    
                    <!-- Hidden inputs for form submission -->
                    <template x-for="id in selectedAgents" :key="id">
                        <input type="hidden" name="agent_ids[]" :value="id">
                    </template>
                    
                    <!-- Floating Dropdown Box -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute left-0 right-0 mt-2 bg-white rounded-2xl border border-slate-100 shadow-2xl z-[999] overflow-hidden p-4 space-y-3"
                         style="display: none;">
                        
                        <!-- Search Field -->
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" x-model="search" placeholder="Search agents..." class="w-full bg-slate-50 border-none rounded-xl pl-9 pr-4 py-2 text-xs font-semibold text-navy-900 focus:ring-2 focus:ring-emerald-500/20 placeholder:text-slate-400">
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="flex gap-2 justify-between border-b border-slate-100 pb-2">
                            <button type="button" @click="selectedAgents = []" class="text-[9px] font-black uppercase text-rose-500 tracking-wider hover:brightness-95">Clear All</button>
                            <button type="button" @click="selectedAgents = agents.map(a => a.id)" class="text-[9px] font-black uppercase text-emerald-500 tracking-wider hover:brightness-95">Select All</button>
                        </div>
                        
                        <!-- List with scrollbar -->
                        <div class="max-h-48 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                            <template x-for="agent in filteredAgents()" :key="agent.id">
                                <label class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 cursor-pointer transition-colors select-none">
                                    <input type="checkbox" :checked="selectedAgents.includes(agent.id)" @change="toggle(agent.id)" class="rounded border-slate-200 text-emerald-600 focus:ring-emerald-500/20">
                                    <span class="text-xs font-bold text-navy-900 uppercase" x-text="agent.full_name || agent.username"></span>
                                </label>
                            </template>
                            <div x-show="filteredAgents().length === 0" class="text-center py-4 text-[10px] font-bold text-slate-400 uppercase">
                                No agents found
                            </div>
                        </div>
                    </div>
                </div>
                @elseif(!$isCallTypeSummary)
                <!-- Agent -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Agent</label>
                    <select name="agent_id" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-navy-900 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                        <option value="">All Agents</option>
                        @foreach($agents ?? [] as $agent)
                            <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->full_name ?: $agent->username }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

        <!-- ── Temporal Controls ───────────────────────────────────── -->
        <!-- Row 2: Date & Time Ranges (Fully Responsive) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-50 pt-8">
            <!-- Date Range -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Date Range</label>
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative w-full">
                        <input type="date" name="date_from" value="{{ request('date_from', now()->toDateString()) }}" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-navy-900 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>
                    <span class="text-slate-300 font-black px-1 uppercase text-[10px] tracking-wider">to</span>
                    <div class="relative w-full">
                        <input type="date" name="date_to" value="{{ request('date_to', now()->toDateString()) }}" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-navy-900 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>
                </div>
            </div>

            <!-- Time Range -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Time Range</label>
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative w-full">
                        <input type="time" name="time_from" value="{{ request('time_from') }}" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-navy-900 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>
                    <span class="text-slate-300 font-black px-1 uppercase text-[10px] tracking-wider">to</span>
                    <div class="relative w-full">
                        <input type="time" name="time_to" value="{{ request('time_to') }}" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-navy-900 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Shifts, Grouping, and Actions (Responsive Grid) -->
        @php
            $showQuickShifts = !request()->routeIs('reports.junk-calls-frequency');
            $showGroupBy = request()->routeIs(['reports.call-type-summary', 'reports.junk-calls-frequency', 'reports.agent-wise']);
            
            $colsCount = 1;
            if ($showQuickShifts) $colsCount++;
            if ($showGroupBy) $colsCount++;
            
            $gridClass = 'md:grid-cols-' . $colsCount;
        @endphp
        <div class="grid grid-cols-1 {{ $gridClass }} gap-6 items-end pt-6">
            @if($showQuickShifts)
            <!-- Shift Shortcuts -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Quick Shifts</label>
                <div class="flex gap-2">
                    <button type="button" @click="setShift('06:00', '14:00')" class="flex-1 bg-gradient-to-b from-emerald-500 to-emerald-600 border-b-4 border-emerald-800 text-white rounded-2xl py-4 font-black text-xs uppercase tracking-widest hover:brightness-110 hover:border-b-[3px] hover:translate-y-[1px] active:border-b-0 active:translate-y-1 transition-all shadow-lg shadow-emerald-500/30 flex justify-center items-center">Morning</button>
                    <button type="button" @click="setShift('14:00', '22:00')" class="flex-1 bg-gradient-to-b from-amber-500 to-amber-600 border-b-4 border-amber-800 text-white rounded-2xl py-4 font-black text-xs uppercase tracking-widest hover:brightness-110 hover:border-b-[3px] hover:translate-y-[1px] active:border-b-0 active:translate-y-1 transition-all shadow-lg shadow-amber-500/30 flex justify-center items-center">Evening</button>
                    <button type="button" @click="setShift('22:00', '06:00')" class="flex-1 bg-gradient-to-b from-indigo-500 to-indigo-600 border-b-4 border-indigo-800 text-white rounded-2xl py-4 font-black text-xs uppercase tracking-widest hover:brightness-110 hover:border-b-[3px] hover:translate-y-[1px] active:border-b-0 active:translate-y-1 transition-all shadow-lg shadow-indigo-500/30 flex justify-center items-center">Night</button>
                </div>
            </div>
            @endif

            @if($showGroupBy)
            <!-- Group By -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Group By</label>
                <div class="flex gap-3">
                    <label class="flex-1 cursor-pointer select-none">
                        <input type="checkbox" name="group_by[]" value="month" x-model="groupBy" class="sr-only">
                        <div class="py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-center transition-all flex justify-center items-center gap-2 border border-slate-200/10"
                             :class="groupBy.includes('month') 
                                ? 'bg-gradient-to-b from-sky-400 to-sky-500 border-b-4 border-sky-700 text-white shadow-lg shadow-sky-500/30' 
                                : 'bg-gradient-to-b from-slate-50 to-slate-100 border-b-4 border-slate-300 text-slate-500 shadow-md hover:brightness-105 active:translate-y-[2px] active:border-b-2'">
                            <i class="fa-solid fa-calendar-days text-xs" :class="groupBy.includes('month') ? 'text-white' : 'text-slate-400'"></i>
                            Month
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer select-none">
                        <input type="checkbox" name="group_by[]" value="date" x-model="groupBy" class="sr-only">
                        <div class="py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-center transition-all flex justify-center items-center gap-2 border border-slate-200/10"
                             :class="groupBy.includes('date') 
                                ? 'bg-gradient-to-b from-sky-400 to-sky-500 border-b-4 border-sky-700 text-white shadow-lg shadow-sky-500/30' 
                                : 'bg-gradient-to-b from-slate-50 to-slate-100 border-b-4 border-slate-300 text-slate-500 shadow-md hover:brightness-105 active:translate-y-[2px] active:border-b-2'">
                            <i class="fa-solid fa-calendar-day text-xs" :class="groupBy.includes('date') ? 'text-white' : 'text-slate-400'"></i>
                            Date
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer select-none">
                        <input type="checkbox" name="group_by[]" value="time" x-model="groupBy" class="sr-only">
                        <div class="py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-center transition-all flex justify-center items-center gap-2 border border-slate-200/10"
                             :class="groupBy.includes('time') 
                                ? 'bg-gradient-to-b from-sky-400 to-sky-500 border-b-4 border-sky-700 text-white shadow-lg shadow-sky-500/30' 
                                : 'bg-gradient-to-b from-slate-50 to-slate-100 border-b-4 border-slate-300 text-slate-500 shadow-md hover:brightness-105 active:translate-y-[2px] active:border-b-2'">
                            <i class="fa-solid fa-clock text-xs" :class="groupBy.includes('time') ? 'text-white' : 'text-slate-400'"></i>
                            Time
                        </div>
                    </label>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button type="submit" :disabled="loading" class="flex-1 bg-gradient-to-b from-blue-500 to-blue-600 border-b-4 border-blue-800 text-white rounded-2xl py-4 font-black text-xs uppercase tracking-widest hover:brightness-110 hover:border-b-[3px] hover:translate-y-[1px] active:border-b-0 active:translate-y-1 transition-all shadow-lg shadow-blue-600/30 flex justify-center items-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-spinner fa-spin" x-show="loading"></i>
                    <span x-text="loading ? 'Generating...' : 'Generate Report'"></span>
                </button>
                <button type="button" @click="window.location.href=window.location.pathname" class="w-14 bg-gradient-to-b from-slate-300 to-slate-400 border-b-4 border-slate-500 text-slate-700 rounded-2xl flex items-center justify-center hover:brightness-110 hover:border-b-[3px] hover:translate-y-[1px] active:border-b-0 active:translate-y-1 transition-all shadow-lg shadow-slate-400/30">
                    <i class="fa-solid fa-rotate text-sm"></i>
                </button>
            </div>
        </div>

        <!-- ── Column Visibility ───────────────────────────────────── -->
        <div class="border-t border-slate-50 pt-8">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 block mb-4">Visible Columns</label>
            <div class="flex flex-wrap gap-3">
                <template x-for="(label, col) in availableColumns" :key="col">
                    <label x-show="!['month', 'date', 'time'].includes(col)" class="relative flex items-center group cursor-pointer">
                        <input type="checkbox" :name="'cols['+col+']'" value="1" x-model="visibleColumns[col]" class="sr-only">
                        <div class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border border-slate-100"
                             :class="visibleColumns[col] ? 'bg-sky-50 border-sky-200 text-sky-700 shadow-sm' : 'bg-white text-slate-400 opacity-60 hover:opacity-100'">
                            <span x-text="label"></span>
                        </div>
                    </label>
                </template>
            </div>
        </div>
    </form>
</div>

<script>
(function() {
    const registerReportFilters = () => {
        Alpine.data('reportFilters', () => ({
            zones: @json($offices['zones'] ?? []),
            sectors: @json($offices['sectors'] ?? []),
            beats: @json($offices['beats'] ?? []),
            selectedZone: '{{ request('zone_id') }}',
            selectedSector: '{{ request('sector_id') }}',
            selectedBeat: '{{ request('beat_id') }}',
            filteredSectors: [],
            filteredBeats: [],
            availableColumns: @json($availableColumns ?? []),
            visibleColumns: @json($visibleColumns ?? []),
            loading: false,
            exporting: null,
            groupBy: (() => {
                let val = @json(request('group_by'));
                if (!val) return ['month'];
                if (typeof val === 'string') return val.split(',').filter(Boolean);
                return Array.isArray(val) ? val : [val];
            })(),
            reportData: @json($data ?? []),
            initialEmpty: {{ (isset($isInitial) && $isInitial) ? 'true' : 'false' }},

            init() {
                this.updateSectors(true);
                this.updateBeats(true);
                
                if (window.location.pathname.includes('/call-type-summary')) {
                    // Auto-load if URL has parameters
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.toString().length > 0) {
                        this.fetchReport({ target: document.getElementById('filterForm'), preventDefault: () => {} });
                    }
                }
            },

            updateSectors(initial = false) {
                this.filteredSectors = this.sectors.filter(s => s.parent_id == this.selectedZone);
                if (!initial) {
                    this.selectedSector = '';
                    this.selectedBeat = '';
                    this.filteredBeats = [];
                }
            },

            updateBeats(initial = false) {
                this.filteredBeats = this.beats.filter(b => b.parent_id == this.selectedSector);
                if (!initial) this.selectedBeat = '';
            },

            setShift(from, to) {
                document.getElementsByName('time_from')[0].value = from;
                document.getElementsByName('time_to')[0].value = to;
                // For overnight shift (Night: 22:00-06:00), set date_to = date_from + 1 day
                if (from > to) {
                    const dateFromInput = document.getElementsByName('date_from')[0];
                    const dateToInput = document.getElementsByName('date_to')[0];
                    const baseDate = dateFromInput.value ? new Date(dateFromInput.value + 'T00:00:00') : new Date();
                    const nextDay = new Date(baseDate);
                    nextDay.setDate(baseDate.getDate() + 1);
                    const fmt = d => [d.getFullYear(), String(d.getMonth()+1).padStart(2,'0'), String(d.getDate()).padStart(2,'0')].join('-');
                    dateToInput.value = fmt(nextDay);
                    // date_from stays unchanged
                }
            },

            async fetchReport(event) {
                this.loading = true;
                const form = event.target || document.getElementById('filterForm');
                const url = new URL(form.action || window.location.href);
                const formData = new FormData(form);
                const searchParams = new URLSearchParams();
                
                const groupByValues = [];
                for (const pair of formData.entries()) {
                    if (!pair[1]) continue;
                    if (pair[0] === 'group_by[]') {
                        groupByValues.push(pair[1]);
                    } else {
                        searchParams.append(pair[0], pair[1]);
                    }
                }
                if (groupByValues.length > 0) {
                    searchParams.set('group_by', groupByValues.join(','));
                }

                url.search = searchParams.toString();
                
                // Update history with the clean URL (without filter query parameters in the address bar)
                const cleanUrl = new URL(window.location.pathname, window.location.origin);
                window.history.pushState({}, '', cleanUrl);

                if (window.location.pathname.includes('/call-type-summary')) {
                    try {
                        const response = await fetch(url, {
                            headers: { 
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const json = await response.json();
                        if (json.success) {
                            this.reportData = json.data;
                            this.availableColumns = json.availableColumns;
                            this.visibleColumns = json.visibleColumns;
                            this.initialEmpty = false;
                            this.updateExportUrls(url);
                        }
                    } catch (error) {
                        console.error('Error fetching dynamic report data:', error);
                    } finally {
                        this.loading = false;
                    }
                    return;
                }

                // Fallback for HTML reports
                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const newContent = doc.getElementById('report-content');
                    const currentContent = document.getElementById('report-content');
                    if (newContent && currentContent) {
                        // Safe replacement of DOM children using replaceChildren
                        currentContent.replaceChildren(...newContent.childNodes);
                        
                        // Sync Alpine state for HTML reports
                        this.initialEmpty = false;
                        
                        // Determine if report has data based on the server-rendered indicator
                        const hasDataEl = currentContent.querySelector('#report-has-data');
                        const hasData = hasDataEl && hasDataEl.getAttribute('data-value') === 'true';
                        
                        if (hasData) {
                            const rows = currentContent.querySelectorAll('tbody tr');
                            this.reportData = new Array(rows.length || 1);
                        } else {
                            this.reportData = [];
                        }

                        // Re-initialize Alpine on the new DOM subtree
                        if (window.Alpine) {
                            setTimeout(() => {
                                window.Alpine.initTree(currentContent);
                            }, 0);
                        }
                    }
                    
                    this.updateExportUrls(url);
                } catch (error) {
                    console.error('Error fetching report:', error);
                } finally {
                    this.loading = false;
                }
            },

            updateExportUrls(url) {
                const exportExcel = document.getElementById('export-excel');
                if (exportExcel) {
                    const excelUrl = new URL(url);
                    excelUrl.searchParams.set('export', 'excel');
                    exportExcel.href = excelUrl.toString();
                }
                const exportPdf = document.getElementById('export-pdf');
                if (exportPdf) {
                    const pdfUrl = new URL(url);
                    pdfUrl.searchParams.set('export', 'pdf');
                    exportPdf.href = pdfUrl.toString();
                }
            }
        }));
    };

    const registerAgentMultiSelect = () => {
        Alpine.data('agentMultiSelect', (initialAgents, initialSelected) => ({
            open: false,
            search: '',
            agents: initialAgents || [],
            selectedAgents: (initialSelected || []).map(Number),
            
            get selectedLabel() {
                if (this.selectedAgents.length === 0) return 'All Agents';
                if (this.selectedAgents.length === 1) {
                    var self = this;
                    var a = this.agents.find(function(x) { return x.id === self.selectedAgents[0]; });
                    return a ? (a.full_name || a.username).toUpperCase() : '1 Agent';
                }
                return this.selectedAgents.length + ' Agents Selected';
            },
            
            toggle(id) {
                var idx = this.selectedAgents.indexOf(id);
                if (idx !== -1) {
                    this.selectedAgents.splice(idx, 1);
                } else {
                    this.selectedAgents.push(id);
                }
            },
            
            filteredAgents() {
                var term = this.search.toLowerCase();
                return this.agents.filter(function(a) {
                    return (a.full_name || '').toLowerCase().indexOf(term) !== -1 || 
                           a.username.toLowerCase().indexOf(term) !== -1;
                });
            }
        }));
    };

    if (window.Alpine && window.Alpine.version) {
        registerReportFilters();
        registerAgentMultiSelect();
    } else {
        document.addEventListener('alpine:init', () => {
            registerReportFilters();
            registerAgentMultiSelect();
        });
    }
})();
</script>
