@extends('layouts.app')

@section('title', 'Help Management - NHMP 130')

@section('page-title', 'Help Details')

@section('content')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    }
    .neon-border-blue { border-left: 4px solid #3b82f6; }
    .neon-border-amber { border-left: 4px solid #f59e0b; }
    .neon-border-emerald { border-left: 4px solid #10b981; }
    
    .bg-grid-slate-100 {
        background-image: radial-gradient(circle, #f1f5f9 1px, transparent 1px);
        background-size: 24px 24px;
    }
</style>

<script>
    window._showData = @js([
        'id' => $call->id,
        'call_number' => $call->call_number,
        'status' => $call->status,
        'priority' => (int) $call->priority,
        'details' => $call->details,
        'tiger_id' => $call->tiger_id,
        'beat_id' => $call->beat_id,
        'tiger' => $call->tiger ? [
            'tiger_code' => $call->tiger->tiger_code,
        ] : null,
        'inprogress_remarks' => $call->inprogress_remarks,
        'history' => $call->statusHistory()->with('changedBy.role')->orderBy('created_at', 'asc')->get()
            ->unique(function ($h) {
                return $h->new_status . $h->remarks . $h->created_at->format('Y-m-d H:i:s');
            })
            ->map(function($h) {
                $role = $h->changedBy->role?->name;
                $isCallCenter = in_array($role, ['agent', 'agent_supervisor', 'super_admin']);
                return [
                    'new_status' => $h->new_status,
                    'remarks' => $h->remarks,
                    'changed_by' => $isCallCenter ? $h->changedBy->full_name : 'Beat Operator',
                    'time' => $h->created_at->format('H:i:s'),
                    'date' => $h->created_at->format('d M')
                ];
            }),
        'permissions' => [
            'transition_to_inprogress' => auth()->user()->can('calls.manage_status'),
            'transition_to_completed' => auth()->user()->can('calls.manage_status'),
        ]
    ]);
</script>

<div class="min-h-screen bg-[#f8fafc] rounded-[3rem] -mt-4 shadow-2xl relative overflow-hidden pb-12" x-data="commandControl(window._showData)">
    {{-- Top Decorative Layer --}}
    <div class="absolute top-0 inset-x-0 h-96 bg-gradient-to-b from-blue-50 to-transparent pointer-events-none"></div>
    <div class="absolute inset-0 bg-grid-slate-100 pointer-events-none opacity-50"></div>

    {{-- Main Content Space --}}
    <div class="relative z-10 p-6 md:p-10 max-w-[1600px] mx-auto space-y-8">
        
        {{-- HEADER BANNER --}}

        {{-- PRIMARY DATA MATRIX --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- LEFT COLUMN: INTEL & CONTEXT --}}
            <div class="lg:col-span-8 space-y-8">
                
                {{-- TAC-MAP & SPATIAL INTEL --}}
                <div class="glass-panel rounded-[2.5rem] p-10 overflow-hidden relative group">
                    <div class="absolute right-10 top-10 opacity-[0.03] group-hover:opacity-[0.05] transition-opacity">
                        <i class="fa-solid fa-map-location-dot text-[15rem]"></i>
                    </div>
                    
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-10 flex items-center gap-3">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        Area of Responsibility and Location
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                        <div class="space-y-2">
                             <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Beat</span>
                             <div class="text-2xl font-black text-navy-900 tracking-tight">{{ $call->office->name ?? 'Global' }}</div>
                             <div class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                                {{ $call->office->parent->parent->name ?? '---' }} / {{ $call->office->parent->name ?? '---' }}
                             </div>
                        </div>
                        <div class="space-y-2">
                             <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Assistance Details</span>
                             <div class="text-2xl font-black text-navy-900 tracking-tight">{{ $call->created_at->format('d-m-Y') }} </div>
                             <div class="text-xs font-bold text-slate-500 font-mono uppercase tracking-widest">Time: {{ $call->created_at->format('Hi') }} HRS</div>
                        </div>
                        <div class="space-y-2">
                             <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Physical Location</span>
                             <div class="text-2xl font-black text-blue-600 tracking-tight">{{ $call->location_details ?? 'No details' }}</div>
                             <!-- <div class="text-xs font-bold text-slate-500 uppercase tracking-widest">Coordinates: {{ $call->caller_lat ?? '0' }}, {{ $call->caller_lng ?? '0' }}</div> -->
                        </div>
                    </div>
                </div>

                {{-- INTEL LOGS --}}
                <div class="glass-panel rounded-[2.5rem] p-10 space-y-10 group">
                    <div>
                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-8 flex items-center gap-3">
                             <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                             Details and Notes
                        </h4>
                        <div class="p-10 bg-slate-50 rounded-[2.5rem] border border-slate-100 min-h-[150px] relative">
                            <i class="fa-solid fa-quote-left absolute left-6 top-6 text-slate-200 text-3xl"></i>
                            <p class="text-lg font-bold text-navy-900 leading-relaxed italic relative z-10 px-4">
                                {{ $call->details ?? 'Commuter notes are not provided...' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="p-8 bg-blue-50/50 rounded-3xl border border-blue-100 flex items-center gap-6">
                            <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg">
                                <i class="fa-solid fa-tag text-xl"></i>
                            </div>
                            <div>
                                <span class="text-[9px] font-black text-blue-400 uppercase tracking-wider">Acquisition Category</span>
                                <div class="text-xl font-black text-navy-900">{{ $call->callType->name ?? 'UNCATEGORIZED' }}</div>
                            </div>
                        </div>
                        <div class="p-8 bg-indigo-50/50 rounded-3xl border border-indigo-100 flex items-center gap-6">
                            <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-lg">
                                <i class="fa-solid fa-list-check text-xl"></i>
                            </div>
                            <div>
                                <span class="text-[9px] font-black text-indigo-400 uppercase tracking-wider">Tactical Sub-type</span>
                                <div class="text-xl font-black text-navy-900">{{ $call->callSubType->name ?? 'GENERAL RESOLUTION' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- AUDIT TIMELINE --}}
                <div class="glass-panel rounded-[3rem] p-12 space-y-10">
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] flex items-center gap-4">
                         <span class="w-2 h-2 rounded-full bg-slate-900"></span>
                         Operational Lifecycle
                    </h4>
                    
                    <div class="relative space-y-8">
                        <div class="absolute left-10 top-0 bottom-0 w-1 bg-slate-100 rounded-full"></div>
                        
                        <template x-for="(log, idx) in history" :key="idx">
                            <div class="relative pl-24 group">
                                <div class="absolute left-8 top-0 w-5 h-5 rounded-full border-4 border-white bg-navy-900 shadow-lg z-10 group-hover:scale-125 transition-transform"></div>
                                <div class="p-8 glass-panel rounded-3xl group-hover:bg-white transition-colors duration-300">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest" :class="getStatusClass(log.new_status)" x-text="log.new_status"></div>
                                        <div class="text-xs font-bold text-slate-400 font-mono" x-text="log.date + ' @ ' + log.time"></div>
                                    </div>
                                    <p class="text-sm font-bold text-navy-900 leading-relaxed italic" x-text="'&quot;' + log.remarks + '&quot;'"></p>
                                    <div class="mt-6 pt-6 border-t border-slate-50 flex items-center justify-between">
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Authenticated By</span>
                                        <span class="text-[10px] font-black text-navy-900 uppercase" x-text="log.changed_by"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: TELEMETRY & ASSETS --}}
            <div class="lg:col-span-4 space-y-8">
                
                {{-- STATUS TELEMETRY TILE --}}
                <div class="glass-panel rounded-[2.5rem] p-10 space-y-10 group">
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-3">
                         <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                         Commuter Details
                    </h4>
                    
                    <div class="flex items-center gap-8">
                        <div class="w-20 h-20 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center text-3xl shadow-inner border border-blue-100 group-hover:rotate-6 transition-transform">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div>
                            <div class="text-3xl font-black text-navy-900 tracking-tighter uppercase mb-1">{{ $call->caller_name ?? 'UNIDENTIFIED' }}</div>
                            <div class="text-lg font-black text-blue-600 tracking-[0.2em] font-mono">{{ $call->caller_number }}</div>
                        </div>
                    </div>
                </div>

                {{-- PERFORMANCE & SLA --}}
                <div class="bg-white rounded-[2.5rem] p-10 shadow-2xl border border-slate-100 space-y-8 relative overflow-hidden group">
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-emerald-50 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest">Response time</h4>
                        <div x-html="getPriorityBadge(item.priority)"></div>
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-end justify-between">
                            <div class="text-5xl font-black text-navy-900 tracking-tighter italic scale-y-110">{{ format_duration($call->response_time_sec ?? 0) }}</div>
                            <div class="text-[9px] font-black text-emerald-500 uppercase tracking-widest pb-1">Response Clock</div>
                        </div>
                        
                        <div class="h-4 w-full bg-slate-100 rounded-full overflow-hidden shadow-inner flex">
                            @php 
                                $perc = min(($call->response_time_sec / 300) * 100, 100);
                                $color = $call->response_time_sec > 300 ? 'bg-rose-500' : 'bg-emerald-500';
                            @endphp
                            <div class="{{ $color }} h-full rounded-full shadow-lg" style="width: {{ $perc ?? 0 }}%"></div>
                        </div> 
                        
                        <div class="flex items-center justify-between text-[8px] font-black uppercase tracking-widest text-slate-400">
                             <span>Operational Start</span>
                             <span>Target SLA (05:00)</span>
                        </div>
                    </div>
                </div>

                {{-- ALLOCATED UNIT (TIGER) --}}
                <div class="glass-panel rounded-[2.5rem] p-10 space-y-10 group overflow-hidden relative">
                    <div class="absolute -right-10 -top-10 scale-150 rotate-12 opacity-[0.03] group-hover:opacity-[0.06] transition-opacity">
                         <i class="fa-solid fa-truck-monster text-[10rem]"></i>
                    </div>

                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-3">
                         <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                         Allocated Tiger Unit
                    </h4>

                    <!-- @php
                        $assetCode = null;
                        if ($call->inprogress_remarks && preg_match('/\[Allocated (?:Static )?Asset: (.*?)\]/', $call->inprogress_remarks, $matches)) {
                            $assetCode = $matches[1];
                        }
                    @endphp -->

                    @php
                    $displayAsset = null;
                        if ($call->inprogress_remarks && preg_match('/\[Allocated (?:Static )?Asset: (.*?)\]/i', $call->inprogress_remarks, $matches)) {
                            $rawCode = $matches[1]; // e.g., "T1" or "ST1"

                            if (str_starts_with($rawCode, 'ST')) {
                                // Replaces "ST" with "Special Tiger-"
                                $displayAsset = "Special Tiger-" . substr($rawCode, 2);
                            } elseif (str_starts_with($rawCode, 'T')) {
                                // Replaces "T" with "Tiger-"
                                $displayAsset = "Tiger-" . substr($rawCode, 1);
                            } else {
                                $displayAsset = $rawCode; // Fallback
                            }
                        }
                    @endphp

                    @if($displayAsset)
                        <div class="space-y-10">
                            <div class="p-8 bg-amber-50 rounded-3xl border border-amber-100 flex items-center gap-8">
                                <div class="w-20 h-20 rounded-2xl bg-white border border-amber-200 flex items-center justify-center text-indigo-700 text-3xl shadow-sm">
                                    <i class="fa-solid fa-car-on"></i>
                                </div>
                                <div>
                                    <div class="text-3xl font-black text-navy-900 italic tracking-tighter">{{ $displayAsset }}</div>
                                    <div class="text-[10px] font-bold text-amber-600 uppercase tracking-widest italic">Allocated from available tigers</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="py-20 border-2 border-dashed border-slate-200 rounded-[2.5rem] flex flex-col items-center justify-center text-center px-10">
                            <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 mb-6">
                                <i class="fa-solid fa-link-slash text-2xl"></i>
                            </div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Null Asset Allocation</span>
                            <p class="text-[9px] font-bold text-slate-300 mt-2 uppercase tracking-tighter">Requires transition authorization</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- OPERATIONAL TRANSITION MODAL (VIBRANT) --}}
    <div x-show="showModal" 
         class="fixed inset-0 z-[1001] flex items-center justify-center p-8 bg-slate-900/30 backdrop-blur-2xl" 
         x-cloak x-transition>
        <div class="bg-white rounded-[3rem] shadow-[0_32px_64px_-12px_rgba(0,0,0,0.14)] w-full max-w-lg overflow-hidden border border-white transform transition-all" @click.away="showModal = false">
            
            {{-- Vibrant Dynamic Header --}}
            <div class="p-12 relative overflow-hidden" 
                 :class="targetStatus === 'in_progress' ? 'bg-gradient-to-br from-amber-400 via-orange-500 to-rose-500' : 'bg-gradient-to-br from-emerald-400 via-teal-500 to-cyan-500'">
                <div class="absolute inset-0 bg-grid-white/[0.1] bg-[size:16px_16px]"></div>
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/20 rounded-full blur-[40px]"></div>
                <div class="relative z-10 flex flex-col gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white border border-white/30 shadow-xl mb-2">
                        <i class="fa-solid" :class="targetStatus === 'in_progress' ? 'fa-bolt-lightning' : 'fa-check-double'"></i>
                    </div>
                    <h3 class="text-4xl font-black text-white tracking-widest italic scale-y-110 uppercase" x-text="targetStatus === 'in_progress' ? 'Initiate Mission' : 'Resolve Task'"></h3>
                    <p class="text-white/80 text-[10px] font-black uppercase tracking-[0.4em] flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                        Strategic Authorization Protocol
                    </p>
                </div>
                <button @click="showModal = false" class="absolute top-10 right-10 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white border border-white/20 transition-all backdrop-blur-md">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-12 space-y-10">
                <template x-if="targetStatus === 'in_progress'">
                    <div class="space-y-6">
                        <select x-model="selectedTigerId" 
                            class="w-full px-8 py-5 bg-white border-2 border-slate-100 rounded-[2rem] outline-none font-bold text-navy-900 text-xs focus:border-blue-500 transition-all shadow-xl">
                            <option value="">Select Operational Asset...</option>
                            <optgroup label="REGIONAL RESERVE (STATIC)">
                                <option value="T1">Tiger-1 (Reserve)</option>
                                <option value="T2">Tiger-2 (Reserve)</option>
                                <option value="T3">Tiger-3 (Reserve)</option>
                            </optgroup>
                            <optgroup label="SPECIAL RESPONSE UNIT">
                                <option value="ST1">Special Tiger-1</option>
                                <option value="ST2">Special Tiger-2</option>
                            </optgroup>
                        </select>
                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider ml-6">Accessing both dynamic beat assets and global reserves</p>
                    </div>
                </template>

                {{-- Intelligence Context --}}
                <div class="p-8 bg-slate-50 rounded-[2rem] border border-slate-100">
                    <label class="block text-[9px] font-black text-blue-500 uppercase tracking-widest mb-4 italic">Active Mission Intelligence</label>
                    <p class="text-sm font-bold text-navy-900 leading-relaxed italic" x-text="item.details || 'No intelligence remarks documented.'"></p>
                </div>

                <div class="space-y-6">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Tactical Log Remarks</label>
                    <textarea x-model="remarks" rows="4" required placeholder="Log mission-critical remarks and transition justifications..." 
                        class="w-full px-8 py-6 bg-slate-50 border-2 border-slate-100 rounded-3xl focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/5 outline-none font-bold text-navy-900 transition-all placeholder:text-slate-300"></textarea>
                </div>

                <div class="flex gap-4 pt-6">
                    <button type="button" @click="showModal = false" class="flex-1 py-5 bg-slate-50 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-all">Abstain</button>
                    <button type="button" @click="executeTransition()" 
                        :disabled="isLoading"
                        :class="targetStatus === 'in_progress' ? 'bg-gradient-to-r from-amber-500 to-orange-600 shadow-orange-500/30' : 'bg-gradient-to-r from-emerald-500 to-cyan-600 shadow-emerald-500/30'"
                        class="flex-[1.5] py-5 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-2xl hover:-translate-y-1 active:scale-95 transition-all flex items-center justify-center gap-3">
                        <template x-if="!isLoading">
                             <div class="flex items-center gap-3">
                                 <i class="fa-solid fa-bolt-lightning text-white"></i>
                                 <span>Authorize Protocol</span>
                             </div>
                        </template>
                        <template x-if="isLoading">
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
    function commandControl(config = {}) {
        return {
            item: {
                id: config.id || null,
                call_number: config.call_number || '--',
                status: config.status || 'pending',
                priority: config.priority || 3,
                details: config.details || '',
                tiger_id: config.tiger_id || null,
                beat_id: config.beat_id || null,
                tiger: config.tiger || null,
                inprogress_remarks: config.inprogress_remarks || '',
            },
            history: config.history || [],
            permissions: config.permissions || {
                transition_to_inprogress: false,
                transition_to_completed: false,
            },
            showModal: false,
            targetStatus: '',
            selectedTigerId: config.tiger_id || '',
            remarks: '',
            isLoading: false,

            openActionModal(status) {
                this.targetStatus = status;
                this.remarks = '';
                this.showModal = true;
            },

            async executeTransition() {
                if (!this.remarks) {
                    if (window.Notification) window.Notification.error('Log entries are required for mission transition.', 'Protocol Rejected');
                    return;
                }
                
                this.isLoading = true;
                try {
                    const response = await fetch(`/calls/${this.item.id}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            status: this.targetStatus,
                            remarks: this.remarks,
                            tiger_id: this.selectedTigerId
                        })
                    });

                    const result = await response.json();

                    if (response.ok) {
                        this.showModal = false;
                        if (window.Notification) window.Notification.success(result.message || 'Mission recalibrated.', 'Operation Confirmed');
                        
                        // Force refresh to get updated Tiger info and audit trail elegantly
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        if (window.Notification) window.Notification.error(result.message || 'Authorization rejected.', 'Operational Failure');
                    }
                } catch (e) {
                    console.error(e);
                    if (window.Notification) window.Notification.error('Comms frequency interference detected.', 'Connection Error');
                } finally {
                    this.isLoading = false;
                }
            },

            getStatusBadge(status) {
                const map = {
                    'pending': '<span class="px-5 py-2 rounded-xl bg-rose-500/20 text-white border border-rose-500/30 text-[10px] font-black uppercase tracking-[0.3em] backdrop-blur-md shadow-lg shadow-rose-500/20 italic">Awaiting Response</span>',
                    'in_progress': '<span class="px-5 py-2 rounded-xl bg-amber-500/20 text-white border border-amber-500/30 text-[10px] font-black uppercase tracking-[0.3em] backdrop-blur-md shadow-lg shadow-amber-500/20 italic">Mission Active</span>',
                    'completed': '<span class="px-5 py-2 rounded-xl bg-emerald-500/20 text-white border border-emerald-500/30 text-[10px] font-black uppercase tracking-[0.3em] backdrop-blur-md shadow-lg shadow-emerald-500/20 italic">Resolved/Secure</span>',
                    'forwarded': '<span class="px-5 py-2 rounded-xl bg-blue-500/20 text-white border border-blue-500/30 text-[10px] font-black uppercase tracking-[0.3em] backdrop-blur-md shadow-lg shadow-blue-500/20 italic">Intel Escalated</span>',
                    'junk': '<span class="px-5 py-2 rounded-xl bg-slate-500/20 text-white border border-slate-500/30 text-[10px] font-black uppercase tracking-[0.3em] backdrop-blur-md shadow-lg shadow-slate-500/20 italic">Trace Purged</span>'
                };
                return map[status] || status;
            },

            getStatusClass(status) {
                const map = {
                    'pending': 'bg-rose-50 text-rose-600 border border-rose-100',
                    'in_progress': 'bg-amber-50 text-amber-600 border border-amber-100',
                    'completed': 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                    'forwarded': 'bg-blue-50 text-blue-600 border border-blue-100',
                    'junk': 'bg-slate-50 text-slate-600 border border-slate-100'
                };
                return map[status] || 'bg-slate-50';
            },

            getPriorityBadge(priority) {
                const map = {
                    1: '<span class="px-3 py-1 bg-rose-600 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-lg shadow-rose-600/20 flex items-center gap-1.5 w-fit"><i class="fa-solid fa-bolt text-[9px]"></i>Critical</span>',
                    2: '<span class="px-3 py-1 bg-orange-500 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-lg shadow-orange-500/20 flex items-center gap-1.5 w-fit"><i class="fa-solid fa-triangle-exclamation text-[9px]"></i>Urgent</span>',
                    3: '<span class="px-3 py-1 bg-amber-500 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-lg shadow-amber-600/20 flex items-center gap-1.5 w-fit"><i class="fa-solid fa-clock text-[9px]"></i>Normal</span>',
                    4: '<span class="px-3 py-1 bg-slate-500 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-lg shadow-slate-400/20 flex items-center gap-1.5 w-fit"><i class="fa-solid fa-circle-info text-[9px]"></i>Info</span>',
                    5: '<span class="px-3 py-1 bg-slate-300 text-slate-700 rounded-lg text-[9px] font-black uppercase tracking-widest flex items-center gap-1.5 w-fit"><i class="fa-solid fa-arrow-down text-[9px]"></i>Low</span>'
                };
                return map[priority] || map[3];
            },  

        }
    }
</script>
@endpush
@endsection
