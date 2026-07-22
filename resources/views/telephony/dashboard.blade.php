@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-slate-800 leading-tight">
        {{ __('Telephony Command Centre') }}
    </h2>
@endsection

@section('content')
<div class="py-6 px-4 max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="telephonyDashboard()">
    
    <!-- Header Controls / Connection Indicator -->
    <div class="flex justify-between items-center mb-6 bg-white/40 backdrop-blur-md border border-white/60 p-4 rounded-2xl shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">ZIWO Integration Dashboard</h1>
            <p class="text-sm text-slate-500">Live agent monitoring, active queues, and communication statistics.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
            <span class="text-sm font-semibold text-emerald-600">Gateway Online</span>
            <button @click="refreshData(true)" class="ml-4 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition active:scale-95 flex items-center gap-1">
                <i class="fa-solid fa-arrows-rotate" :class="refreshing ? 'animate-spin' : ''"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Live Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Active Calls Card -->
        <div class="bg-gradient-to-br from-indigo-500/10 to-purple-500/10 backdrop-blur-md border border-indigo-500/20 p-6 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 text-indigo-500/5 text-8xl font-bold">
                <i class="fa-solid fa-phone-volume"></i>
            </div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wide">Active Calls</span>
                <span class="p-2 bg-indigo-500/10 text-indigo-600 rounded-xl">
                    <i class="fa-solid fa-phone-flip animate-pulse"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-slate-800" x-text="liveStats.active_calls_count">0</div>
            <p class="text-xs text-slate-500 mt-2">Current concurrent active streams</p>
        </div>

        <!-- Live Agents -->
        <div class="bg-gradient-to-br from-emerald-500/10 to-teal-500/10 backdrop-blur-md border border-emerald-500/20 p-6 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 text-emerald-500/5 text-8xl font-bold">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wide">Online Agents</span>
                <span class="p-2 bg-emerald-500/10 text-emerald-600 rounded-xl">
                    <i class="fa-solid fa-user-tie"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-slate-800" x-text="onlineAgentsCount">0</div>
            <p class="text-xs text-slate-500 mt-2">Active sessions registered</p>
        </div>

        <!-- Total Calls Today -->
        <div class="bg-gradient-to-br from-cyan-500/10 to-sky-500/10 backdrop-blur-md border border-cyan-500/20 p-6 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 text-cyan-500/5 text-8xl font-bold">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-cyan-600 uppercase tracking-wide">Total Calls Today</span>
                <span class="p-2 bg-cyan-500/10 text-cyan-600 rounded-xl">
                    <i class="fa-solid fa-phone"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-slate-800" x-text="liveStats.total_calls">0</div>
            <p class="text-xs text-slate-500 mt-2">Inbound & Outbound combined</p>
        </div>

        <!-- SLA Compliance -->
        <div class="bg-gradient-to-br from-amber-500/10 to-orange-500/10 backdrop-blur-md border border-amber-500/20 p-6 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 text-amber-500/5 text-8xl font-bold">
                <i class="fa-solid fa-star-half-stroke"></i>
            </div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-amber-600 uppercase tracking-wide">SLA Completion</span>
                <span class="p-2 bg-amber-500/10 text-amber-600 rounded-xl">
                    <i class="fa-solid fa-percent"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-slate-800" x-text="liveStats.sla_percentage + '%'">100%</div>
            <p class="text-xs text-slate-500 mt-2">Percent of answered calls</p>
        </div>

    </div>

    <!-- Agent Statuses and Live Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <!-- Live Agent Monitor Panel -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 lg:col-span-1 flex flex-col h-[400px]">
            <h3 class="font-bold text-lg text-slate-800 mb-4 flex items-center justify-between">
                <span>Agent Status Directory</span>
                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full" x-text="liveStats.live_agents.length + ' Total'"></span>
            </h3>
            
            <div class="overflow-y-auto flex-1 pr-1 space-y-3">
                <template x-for="agent in liveStats.live_agents" :key="agent.username">
                    <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-100 rounded-xl hover:border-slate-200 transition">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 bg-indigo-50 text-indigo-600 rounded-xl font-bold flex items-center justify-center text-sm" x-text="agent.agent_name.substring(0,2).toUpperCase()">
                                AG
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-800" x-text="agent.agent_name"></div>
                                <div class="text-xs text-slate-400" x-text="'@' + agent.username"></div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-xs px-2.5 py-1 rounded-full font-bold uppercase tracking-wider"
                                  :class="{
                                      'bg-emerald-100 text-emerald-700': agent.status === 'online',
                                      'bg-amber-100 text-amber-700': agent.status === 'pause',
                                      'bg-blue-100 text-blue-700': agent.status === 'speaking',
                                      'bg-rose-100 text-rose-700': agent.status === 'ringing',
                                      'bg-slate-100 text-slate-600': agent.status === 'offline'
                                  }"
                                  x-text="agent.status">
                            </span>
                            <span class="text-[10px] text-slate-400 mt-1" x-text="agent.last_change"></span>
                        </div>
                    </div>
                </template>
                <div x-show="liveStats.live_agents.length === 0" class="text-center py-12 text-slate-400 text-sm">
                    <i class="fa-solid fa-headset text-4xl mb-3 block text-slate-200"></i>
                    No agent configs found.
                </div>
            </div>
        </div>

        <!-- Historic Volume Chart -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 lg:col-span-2 flex flex-col h-[400px]">
            <h3 class="font-bold text-lg text-slate-800 mb-4">Hourly Call Traffic Today</h3>
            <div class="flex-1 relative">
                <canvas id="hourlyCallChart" class="w-full h-full"></canvas>
            </div>
        </div>

    </div>

    <!-- Call Log History & Webhooks Audit -->
    <div class="grid grid-cols-1 gap-8">
        
        <!-- Live Call Logs Table -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <h3 class="font-bold text-lg text-slate-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-indigo-500"></i> Unified Call Registry
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 text-xs font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Date/Time</th>
                            <th class="py-3 px-4">Call ID</th>
                            <th class="py-3 px-4">Agent</th>
                            <th class="py-3 px-4">Caller Number</th>
                            <th class="py-3 px-4">Direction</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Duration</th>
                            <th class="py-3 px-4">Recording</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600 text-sm divide-y divide-slate-100">
                        @forelse($callLogs as $log)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3 px-4 whitespace-nowrap text-xs">
                                    {{ $log->created_at->setTimezone('Asia/Karachi')->format('Y-m-d h:i:s A') }}
                                </td>
                                <td class="py-3 px-4 font-mono text-xs text-slate-500">
                                    {{ $log->call_id ?? $log->call_uuid ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-700">
                                    {{ $log->agent->full_name ?? 'Unassigned' }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-700">
                                    {{ $log->caller_number }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2 py-0.5 rounded-full {{ $log->direction === 'inbound' ? 'bg-indigo-50 text-indigo-600' : 'bg-amber-50 text-amber-600' }}">
                                        @if($log->direction === 'inbound')
                                            <i class="fa-solid fa-arrow-down-left"></i> Inbound
                                        @else
                                            <i class="fa-solid fa-arrow-up-right"></i> Outbound
                                        @endif
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-xs px-2.5 py-1 rounded-full font-bold uppercase tracking-wider
                                        {{ $log->status === 'finished' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                        {{ $log->status === 'missed' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}
                                        {{ $log->status === 'active' ? 'bg-blue-50 text-blue-700 border border-blue-200 animate-pulse' : '' }}
                                        {{ $log->status === 'ringing' ? 'bg-yellow-50 text-yellow-700 border border-yellow-200' : '' }}
                                    ">
                                        {{ $log->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs font-mono">
                                    {{ gmdate('H:i:s', $log->duration_seconds) }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($log->recording_url)
                                        <div class="flex items-center gap-3">
                                            <audio src="{{ $log->recording_url }}" controls class="h-8 max-w-[200px]"></audio>
                                            <a href="{{ $log->recording_url }}" target="_blank" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg hover:scale-105 active:scale-95 transition" title="Download">
                                                <i class="fa-solid fa-download text-xs"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">No recording</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-400">
                                    <i class="fa-solid fa-phone-slash text-4xl mb-3 block text-slate-200"></i>
                                    No telephony logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $callLogs->links() }}
            </div>
        </div>

        <!-- Webhook Audit Log -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <h3 class="font-bold text-lg text-slate-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-network-wired text-indigo-500"></i> Webhook Event Stream (Audit Logs)
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 text-xs font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Timestamp</th>
                            <th class="py-3 px-4">Event Type</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Details</th>
                            <th class="py-3 px-4">Payload</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600 text-sm divide-y divide-slate-100">
                        @forelse($webhookLogs as $webhook)
                            <tr class="hover:bg-slate-50/50 transition" x-data="{ open: false }">
                                <td class="py-3 px-4 whitespace-nowrap text-xs">
                                    {{ $webhook->created_at->setTimezone('Asia/Karachi')->format('Y-m-d h:i:s A') }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800 text-xs">
                                    {{ $webhook->event_type }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-xs px-2 py-0.5 rounded-full font-bold uppercase tracking-wide {{ $webhook->processed ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $webhook->processed ? 'Processed' : 'Failed' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs max-w-xs truncate text-slate-400">
                                    @if($webhook->error_message)
                                        <span class="text-rose-600" title="{{ $webhook->error_message }}">{{ $webhook->error_message }}</span>
                                    @else
                                        Success
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <button @click="open = !open" class="px-2 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-xs font-semibold rounded-lg transition active:scale-95">
                                        View Payload
                                    </button>
                                    <div x-show="open" @click.away="open = false" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
                                        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-2xl w-full p-6 flex flex-col max-h-[80vh]">
                                            <div class="flex justify-between items-center mb-4">
                                                <h4 class="font-bold text-lg text-slate-900">Webhook Raw Payload</h4>
                                                <button @click="open = false" class="text-slate-400 hover:text-slate-600 transition">
                                                    <i class="fa-solid fa-xmark text-xl"></i>
                                                </button>
                                            </div>
                                            <div class="overflow-auto flex-1 bg-slate-900 text-emerald-400 font-mono text-xs p-4 rounded-xl">
                                                <pre x-text="JSON.stringify({{ json_encode($webhook->payload) }}, null, 4)"></pre>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-slate-400">
                                    <i class="fa-solid fa-code-fork text-4xl mb-3 block text-slate-200"></i>
                                    No webhook payloads registered.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<script>
    (function() {
        function registerTelephonyDashboard() {
            Alpine.data('telephonyDashboard', () => ({
                refreshing: false,
                liveStats: {
                    active_calls_count: {{ $analytics['active_calls_count'] ?? 0 }},
                    live_agents: @json($analytics['live_agents'] ?? []),
                    total_calls: {{ $analytics['total_calls'] ?? 0 }},
                    completed_calls: {{ $analytics['completed_calls'] ?? 0 }},
                    missed_calls: {{ $analytics['missed_calls'] ?? 0 }},
                    sla_percentage: {{ $analytics['sla_percentage'] ?? 100 }},
                },
                hourlyTraffic: @json($analytics['hourly_distribution'] ?? []),
                chartInstance: null,

                init() {
                    this.renderChart();

                    setInterval(() => {
                        this.refreshData(false);
                    }, 5000);
                },

                get onlineAgentsCount() {
                    return this.liveStats.live_agents.filter(a => a.status !== 'offline').length;
                },

                async refreshData(showIndicator = false) {
                    if (showIndicator) this.refreshing = true;
                    try {
                        const response = await fetch('{{ route("admin.telephony.live-stats") }}');
                        const data = await response.json();
                        if (data.status === 'success') {
                            this.liveStats.active_calls_count = data.active_calls_count;
                            this.liveStats.live_agents = data.live_agents;
                            this.liveStats.total_calls = data.total_calls;
                            this.liveStats.completed_calls = data.completed_calls;
                            this.liveStats.missed_calls = data.missed_calls;
                            this.liveStats.sla_percentage = data.sla_percentage;
                        }
                    } catch (e) {
                        console.error("Dashboard Polling Error: ", e);
                    } finally {
                        if (showIndicator) this.refreshing = false;
                    }
                },

                renderChart() {
                    const ctx = document.getElementById('hourlyCallChart');
                    if (!ctx) return;
                    const ctx2d = ctx.getContext('2d');
                    const labels = Array.from({ length: 24 }, (_, i) => `${i}:00`);
                    const dataValues = Array.from({ length: 24 }, (_, i) => this.hourlyTraffic[i] || 0);

                    if (this.chartInstance) {
                        this.chartInstance.destroy();
                    }

                    this.chartInstance = new Chart(ctx2d, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Call Volume',
                                data: dataValues,
                                backgroundColor: 'rgba(99, 102, 241, 0.25)',
                                borderColor: 'rgba(99, 102, 241, 1)',
                                borderWidth: 2,
                                borderRadius: 6,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9' },
                                    ticks: { stepSize: 1, color: '#94a3b8' }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { color: '#94a3b8' }
                                }
                            }
                        }
                    });
                }
            }));
        }

        if (window.Alpine) {
            registerTelephonyDashboard();
        } else {
            document.addEventListener('alpine:init', registerTelephonyDashboard);
        }
    })();
</script>
@endsection
