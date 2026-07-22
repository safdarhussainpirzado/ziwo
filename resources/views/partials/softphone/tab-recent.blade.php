{{--
    Premium Recent tab. Scrollable call log with direction + status badges + redial.
    Shown when phoneTab === 'history'.
--}}
<template x-if="phoneTab === 'history'">
    <div
        class="absolute inset-0 flex flex-col"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
    >
        <div class="shrink-0 px-3 pt-3 pb-2 border-b border-slate-800 flex items-center justify-between">
            <div class="text-[9px] uppercase tracking-wider text-slate-500 font-bold">Call History</div>
            <button type="button" @click="phoneLoadRecentLogs()" class="text-[10px] text-indigo-400 hover:text-indigo-300 font-bold uppercase tracking-wider flex items-center gap-1 transition">
                <i class="fa-solid fa-arrows-rotate"></i>
                Refresh
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-3 py-2 space-y-1.5">
            <template x-for="h in recentCallLogs" :key="h.call_id || h.id">
                <div class="flex items-center gap-2 p-2 bg-[#0F172A]/50 border border-slate-800 rounded-xl hover:border-slate-700 transition">
                    {{-- Direction icon --}}
                    <div class="shrink-0 w-7 h-7 rounded-full grid place-items-center"
                         :class="{
                             'bg-blue-500/15 text-blue-400':    h.direction === 'inbound',
                             'bg-violet-500/15 text-violet-400': h.direction === 'outbound',
                             'bg-rose-500/15 text-rose-400':    h.direction === 'conference'
                         }">
                        <i class="fa-solid text-[10px]"
                           :class="{
                               'fa-phone-incoming': h.direction === 'inbound',
                               'fa-phone-outgoing': h.direction === 'outbound',
                               'fa-users':          h.direction === 'conference'
                           }"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] font-bold text-slate-100 truncate tabular-nums"
                             x-text="(h.direction === 'outbound' ? (h.dialed_number || h.caller_number || h.number) : (h.caller_number || h.number)) || 'Unknown'"></div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[8px] text-slate-500 tabular-nums" x-text="h.time_ago || (h.started_at_iso ? new Date(h.started_at_iso).toLocaleString() : '')"></span>
                            <span class="text-[8px] font-mono text-slate-400 tabular-nums" x-show="h.duration_sec" x-text="'• ' + h.duration_sec + 's'"></span>
                        </div>
                    </div>

                    {{-- Status badge --}}
                    <span class="shrink-0 inline-flex items-center gap-0.5 text-[8px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded"
                          :class="{
                              'bg-emerald-500/15 text-emerald-400': ['finished','answered'].includes(h.status),
                              'bg-rose-500/15 text-rose-400':       ['missed','no-answer','cancel'].includes(h.status),
                              'bg-blue-500/15 text-blue-400':         h.status === 'active'
                          }">
                        <i class="fa-solid text-[7px]"
                           :class="{
                               'fa-check':    ['finished','answered'].includes(h.status),
                               'fa-xmark':    ['missed','no-answer','cancel'].includes(h.status),
                               'fa-circle-dot': h.status === 'active'
                           }"></i>
                        <span x-text="h.status"></span>
                    </span>

                    <button type="button" @click="phoneTriggerQuickDial(h.direction === 'outbound' ? (h.dialed_number || h.caller_number || '') : (h.caller_number || ''))" class="shrink-0 w-7 h-7 rounded-lg bg-slate-800 hover:bg-indigo-600 text-slate-400 hover:text-white transition grid place-items-center" title="Redial">
                        <i class="fa-solid fa-phone text-[10px]"></i>
                    </button>
                </div>
            </template>

            <div x-show="recentCallLogs.length === 0" class="text-center py-12 text-slate-500 text-[10px]">
                <i class="fa-solid fa-clock-rotate-left text-3xl mb-2 block opacity-40"></i>
                <p class="font-bold uppercase tracking-wider">No recent calls</p>
                <p class="mt-1 text-[9px] text-slate-600 normal-case font-normal">Outbound and answered calls will appear here.</p>
            </div>
        </div>
    </div>
</template>
