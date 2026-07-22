@php
    $agents = $agents ?? [];
    $callHistory = $callHistory ?? [];
    $queues = $queues ?? [];
    $totalCalls = $totalCalls ?? 0;
    $totalDuration = $totalDuration ?? 0;
@endphp

<div x-data="telephonyStats()">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Call Statistics</h1>
            <p class="text-sm text-slate-500 mt-1">Agent performance, queue insights & call history</p>
        </div>
        <button @click="refreshAll()" :disabled="loading" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
            <i class="fa-solid fa-rotate" :class="loading ? 'animate-spin' : ''"></i>
            Refresh
        </button>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Agents</p>
                    <p class="text-2xl font-bold text-slate-800" x-text="stats.total_agents">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <i class="fa-solid fa-phone"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Calls</p>
                    <p class="text-2xl font-bold text-slate-800" x-text="stats.total_calls">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Avg Talk Time</p>
                    <p class="text-2xl font-bold text-slate-800" x-text="stats.avg_talk_time">0s</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-rose-100 flex items-center justify-center text-rose-600">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">SLA</p>
                    <p class="text-2xl font-bold text-slate-800" x-text="stats.sla + '%'">0%</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{--Agent Performance Table --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-800"><i class="fa-solid fa-users mr-2 text-indigo-500"></i>Agent Performance</h2>
                <span class="text-xs text-slate-400 bg-slate-50 px-2 py-1 rounded-full" x-text="agents.length + ' agents'"></span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="px-5 py-3">Agent</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Calls</th>
                            <th class="px-5 py-3 text-right">Avg Talk</th>
                            <th class="px-5 py-3 text-right">Occupancy</th>
                            <th class="px-5 py-3 text-right">Satisfaction</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="agent in agents" :key="agent.username">
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-semibold text-indigo-600" x-text="agent.agent_name?.charAt(0) || '?'"></div>
                                        <span class="font-medium text-slate-700" x-text="agent.agent_name || agent.username"></span>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="{
                                              'bg-emerald-50 text-emerald-700': agent.status === 'online' || agent.status === 'speaking',
                                              'bg-amber-50 text-amber-700': agent.status === 'pause',
                                              'bg-slate-100 text-slate-500': agent.status === 'offline'
                                          }">
                                        <span class="w-1.5 h-1.5 rounded-full"
                                              :class="{
                                                  'bg-emerald-500': agent.status === 'online',
                                                  'bg-emerald-400 animate-pulse': agent.status === 'speaking',
                                                  'bg-amber-400': agent.status === 'pause',
                                                  'bg-slate-300': agent.status === 'offline'
                                              }"></span>
                                        <span x-text="agent.status"></span>
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right font-medium text-slate-700" x-text="agent.total_calls || '-'"></td>
                                <td class="px-5 py-3 text-right text-slate-600" x-text="agent.avg_talk_time ? agent.avg_talk_time + 's' : '-'"></td>
                                <td class="px-5 py-3 text-right text-slate-600" x-text="agent.occupancy ? (agent.occupancy * 100).toFixed(0) + '%' : '-'"></td>
                                <td class="px-5 py-3 text-right">
                                    <span x-show="agent.satisfaction" class="inline-flex items-center gap-1 text-amber-500">
                                        <i class="fa-solid fa-star text-[10px]"></i>
                                        <span class="font-medium text-slate-700" x-text="agent.satisfaction.toFixed(1)"></span>
                                    </span>
                                    <span x-show="!agent.satisfaction" class="text-slate-300">—</span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div x-show="agents.length === 0" class="text-center py-12 text-slate-400 text-sm">
                    <i class="fa-solid fa-inbox text-2xl mb-2 block text-slate-200"></i>
                    No agent data available
                </div>
            </div>
        </div>

        {{--Queue Status Panel --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800"><i class="fa-solid fa-tray mr-2 text-amber-500"></i>Queue Status</h2>
            </div>
            <div class="divide-y divide-slate-100">
                <template x-for="q in queues" :key="q.id">
                    <div class="px-5 py-4 hover:bg-slate-50/50 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-slate-700 text-sm" x-text="q.name"></span>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                                  :class="q.active_calls > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400'"
                                  x-text="q.active_calls + ' active'"></span>
                        </div>
                        <div class="flex gap-4 text-xs text-slate-500">
                            <span><i class="fa-regular fa-clock mr-1"></i>Waiting: <strong x-text="q.waiting">0</strong></span>
                            <span><i class="fa-solid fa-user mr-1"></i>Agents: <strong x-text="q.agents">0</strong></span>
                        </div>
                    </div>
                </template>
                <div x-show="queues.length === 0" class="text-center py-12 text-slate-400 text-sm">
                    <i class="fa-solid fa-tray text-2xl mb-2 block text-slate-200"></i>
                    No queue data
                </div>
            </div>
        </div>
    </div>

    {{--Call History Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800"><i class="fa-solid fa-clock-rotate-left mr-2 text-slate-400"></i>Recent Call History</h2>
            <div class="flex items-center gap-2">
                <select x-model="filterDirection" @change="fetchCallHistory()" class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 bg-white text-slate-600">
                    <option value="">All</option>
                    <option value="inbound">Inbound</option>
                    <option value="outbound">Outbound</option>
                </select>
                <select x-model="filterStatus" @change="fetchCallHistory()" class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 bg-white text-slate-600">
                    <option value="">All Status</option>
                    <option value="finished">Finished</option>
                    <option value="missed">Missed</option>
                    <option value="active">Active</option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3">Call ID</th>
                        <th class="px-5 py-3">Agent</th>
                        <th class="px-5 py-3">Number</th>
                        <th class="px-5 py-3">Direction</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Duration</th>
                        <th class="px-5 py-3 text-right">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="call in callHistory" :key="call.call_id">
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3 font-mono text-xs text-slate-500" x-text="call.call_id"></td>
                            <td class="px-5 py-3 text-slate-700" x-text="call.agent"></td>
                            <td class="px-5 py-3 font-mono text-sm text-slate-600" x-text="call.caller_number"></td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-1 text-xs font-medium"
                                      :class="call.direction === 'inbound' ? 'text-blue-600' : 'text-purple-600'">
                                    <i class="fa-solid" :class="call.direction === 'inbound' ? 'fa-phone-incoming' : 'fa-phone-outgoing'"></i>
                                    <span x-text="call.direction"></span>
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium"
                                      :class="{
                                          'bg-emerald-50 text-emerald-700': call.status === 'finished',
                                          'bg-rose-50 text-rose-600': call.status === 'missed',
                                          'bg-blue-50 text-blue-600': call.status === 'active'
                                      }">
                                    <span x-text="call.status"></span>
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-slate-600 font-mono text-xs" x-text="call.duration_sec ? call.duration_sec + 's' : '—'"></td>
                            <td class="px-5 py-3 text-right text-slate-400 text-xs" x-text="formatTime(call.created_at)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div x-show="callHistory.length === 0" class="text-center py-12 text-slate-400 text-sm">
                <i class="fa-solid fa-clock-rotate-left text-2xl mb-2 block text-slate-200"></i>
                No call history found
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function registerStats() {
        Alpine.data('telephonyStats', () => ({
            loading: false,
            agents: @json($agents),
            callHistory: @json($callHistory),
            queues: @json($queues),
            filterDirection: '',
            filterStatus: '',
            stats: {
                total_agents: {{ count($agents) }},
                total_calls: {{ $totalCalls }},
                avg_talk_time: '{{ gmdate("i:s", $totalDuration > 0 ? intdiv($totalDuration, ($totalCalls ?: 1)) : 0) }}',
                sla: {{ $sla ?? 100 }},
            },

            async refreshAll() {
                this.loading = true;
                await Promise.all([this.fetchAgents(), this.fetchCallHistory(), this.fetchQueues()]);
                this.loading = false;
            },

            async fetchAgents() {
                try {
                    const r = await fetch('/mgmt/telephony/agents');
                    const d = await r.json();
                    if (d.status === 'success' && d.agents) {
                        this.agents = d.agents;
                        this.stats.total_agents = d.total || d.agents.length;
                    }
                } catch(e) { console.error('fetch agents', e); }
            },

            async fetchCallHistory() {
                try {
                    const params = new URLSearchParams();
                    if (this.filterDirection) params.set('direction', this.filterDirection);
                    if (this.filterStatus) params.set('status', this.filterStatus);
                    const r = await fetch('/mgmt/telephony/calls/history?' + params.toString());
                    const d = await r.json();
                    if (d.status === 'success' && d.calls) {
                        this.callHistory = d.calls;
                        this.stats.total_calls = d.total || d.calls.length;
                    }
                } catch(e) { console.error('fetch history', e); }
            },

            async fetchQueues() {
                try {
                    const r = await fetch('/mgmt/telephony/queues');
                    const d = await r.json();
                    if (d.status === 'success' && d.queues) this.queues = d.queues;
                } catch(e) { console.error('fetch queues', e); }
            },

            formatTime(ts) {
                if (!ts) return '—';
                try {
                    return new Date(ts).toLocaleString('en-PK', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                } catch { return ts; }
            },
        }));
    }

    if (window.Alpine) registerStats();
    else document.addEventListener('alpine:init', registerStats);
})();
</script>
