{{--
    Premium Recent Calls tab.
    Enhanced: direction arrows, full status labels, caller names, color-coded items.
    Shown when phoneTab === 'history'.
--}}
<template x-if="phoneTab === 'history'">
    <div
        class="absolute inset-0 flex flex-col"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
    >
        {{-- Header --}}
        <div class="shrink-0 px-3 pt-3 pb-2 border-b border-slate-800 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-indigo-400 text-xs"></i>
                <span class="text-[10px] uppercase tracking-wider text-slate-300 font-bold">Call History</span>
                <span x-show="recentCallLogs.length > 0"
                      class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-300 text-[9px] font-bold"
                      x-text="recentCallLogs.length"></span>
            </div>
            <button type="button"
                    @click="phoneLoadRecentLogs()"
                    class="flex items-center gap-1.5 px-2.5 py-1 bg-indigo-600/15 hover:bg-indigo-600 text-indigo-400 hover:text-white rounded-lg transition text-[10px] font-bold uppercase tracking-wider border border-indigo-500/20 hover:border-indigo-500">
                <i class="fa-solid fa-arrows-rotate"></i>
                Refresh
            </button>
        </div>

        {{-- Call log list --}}
        <div class="flex-1 overflow-y-auto px-2 py-2 space-y-1 custom-scrollbar">

            {{-- Empty state --}}
            <template x-if="recentCallLogs.length === 0">
                <div class="flex flex-col items-center justify-center h-full py-12 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-800/60 border border-slate-700 grid place-items-center mb-3">
                        <i class="fa-solid fa-phone-slash text-slate-600 text-xl"></i>
                    </div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">No recent calls</p>
                    <p class="text-[9px] text-slate-600 mt-1">Your call history will appear here</p>
                    <button type="button" @click="phoneLoadRecentLogs()"
                            class="mt-3 px-3 py-1.5 bg-indigo-600/20 hover:bg-indigo-600 text-indigo-400 hover:text-white text-[10px] font-bold rounded-lg border border-indigo-500/20 transition">
                        Load History
                    </button>
                </div>
            </template>

            <template x-for="(h, idx) in recentCallLogs" :key="h.call_id || h.id || idx">
                <div class="group relative flex items-center gap-2.5 p-2.5 rounded-xl border transition cursor-default"
                     :class="{
                         'bg-rose-500/5 border-rose-500/20 hover:bg-rose-500/10 hover:border-rose-500/30':
                             ['missed','no-answer','cancel','busy'].includes(h.status),
                         'bg-emerald-500/5 border-emerald-500/15 hover:bg-emerald-500/10 hover:border-emerald-500/25':
                             ['finished','answered','completed'].includes(h.status),
                         'bg-blue-500/5 border-blue-500/15 hover:bg-blue-500/10 hover:border-blue-500/25':
                             h.status === 'active',
                         'bg-slate-800/40 border-slate-700/60 hover:bg-slate-800 hover:border-slate-600':
                             !['missed','no-answer','cancel','busy','finished','answered','completed','active'].includes(h.status)
                     }">

                    {{-- Direction avatar with call result icon --}}
                    <div class="relative shrink-0">
                        <div class="w-9 h-9 rounded-xl grid place-items-center text-[11px] font-black"
                             :class="{
                                 'bg-rose-500/20 text-rose-400':
                                     ['missed','no-answer','cancel','busy'].includes(h.status),
                                 'bg-emerald-500/20 text-emerald-300':
                                     ['finished','answered','completed'].includes(h.status) && h.direction === 'inbound',
                                 'bg-violet-500/20 text-violet-300':
                                     ['finished','answered','completed'].includes(h.status) && h.direction === 'outbound',
                                 'bg-blue-500/20 text-blue-400': h.status === 'active',
                                 'bg-slate-700 text-slate-400': !['missed','no-answer','cancel','busy','finished','answered','completed','active'].includes(h.status),
                             }">
                            <template x-if="h.caller_name">
                                <span x-text="(h.caller_name || '?').slice(0,2).toUpperCase()"></span>
                            </template>
                            <template x-if="!h.caller_name">
                                <i class="fa-solid text-sm"
                                   :class="{
                                       'fa-phone-missed text-rose-400': ['missed','no-answer','cancel'].includes(h.status),
                                       'fa-phone-slash text-rose-400': h.status === 'busy',
                                       'fa-phone-arrow-down-left text-emerald-400': ['finished','answered','completed'].includes(h.status) && h.direction === 'inbound',
                                       'fa-phone-arrow-up-right text-violet-400': ['finished','answered','completed'].includes(h.status) && h.direction === 'outbound',
                                       'fa-circle-dot text-blue-400 animate-pulse': h.status === 'active',
                                       'fa-phone text-slate-400': !['missed','no-answer','cancel','busy','finished','answered','completed','active'].includes(h.status),
                                   }"></i>
                            </template>
                        </div>

                        {{-- Small direction badge --}}
                        <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-md grid place-items-center border border-[#0B1220]"
                             :class="{
                                 'bg-rose-500': ['missed','no-answer','cancel','busy'].includes(h.status),
                                 'bg-emerald-500': ['finished','answered','completed'].includes(h.status),
                                 'bg-blue-500': h.status === 'active',
                                 'bg-slate-600': !['missed','no-answer','cancel','busy','finished','answered','completed','active'].includes(h.status),
                             }">
                            <i class="fa-solid text-[7px] text-white"
                               :class="{
                                   'fa-phone-arrow-down-left': h.direction === 'inbound',
                                   'fa-phone-arrow-up-right': h.direction === 'outbound',
                                   'fa-users': h.direction === 'conference',
                               }"></i>
                        </div>
                    </div>

                    {{-- Call info --}}
                    <div class="flex-1 min-w-0">
                        {{-- Name / number --}}
                        <div class="flex items-center gap-1.5 leading-tight">
                            <span class="text-[12px] font-bold truncate"
                                  :class="{
                                      'text-rose-300': ['missed','no-answer','cancel'].includes(h.status),
                                      'text-white': !['missed','no-answer','cancel'].includes(h.status)
                                  }"
                                  x-text="h.caller_name || (h.direction === 'outbound' ? (h.dialed_number || h.caller_number || h.number) : (h.caller_number || h.number)) || 'Unknown'">
                            </span>
                        </div>
                        {{-- Sub-number if name exists --}}
                        <template x-if="h.caller_name">
                            <div class="text-[10px] font-mono text-slate-500 truncate mt-0.5"
                                 x-text="h.direction === 'outbound' ? (h.dialed_number || h.caller_number || '') : (h.caller_number || '')">
                            </div>
                        </template>
                        {{-- Time + duration --}}
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="text-[9px] text-slate-500 tabular-nums"
                                  x-text="h.time_ago || (h.started_at_iso ? new Date(h.started_at_iso).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}) : '')">
                            </span>
                            <template x-if="h.talk_time > 0 || h.duration_sec > 0">
                                <span class="text-[9px] font-mono text-slate-500 tabular-nums"
                                      x-text="'· ' + (h.talk_time || h.duration_sec) + 's'">
                                </span>
                            </template>
                        </div>
                    </div>

                    {{-- Status badge --}}
                    <div class="shrink-0 flex flex-col items-end gap-1.5">
                        <span class="inline-flex items-center gap-1 text-[9px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-md"
                              :class="{
                                  'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20': ['finished','answered','completed'].includes(h.status),
                                  'bg-rose-500/15 text-rose-400 border border-rose-500/20': ['missed','no-answer','cancel'].includes(h.status),
                                  'bg-amber-500/15 text-amber-400 border border-amber-500/20': h.status === 'busy',
                                  'bg-blue-500/15 text-blue-400 border border-blue-500/20': h.status === 'active',
                                  'bg-slate-700/50 text-slate-400 border border-slate-700': !['finished','answered','completed','missed','no-answer','cancel','busy','active'].includes(h.status),
                              }">
                            <i class="fa-solid text-[7px]"
                               :class="{
                                   'fa-check': ['finished','answered','completed'].includes(h.status),
                                   'fa-phone-missed': ['missed','no-answer','cancel'].includes(h.status),
                                   'fa-ban': h.status === 'busy',
                                   'fa-circle-dot': h.status === 'active',
                                   'fa-question': !['finished','answered','completed','missed','no-answer','cancel','busy','active'].includes(h.status),
                               }"></i>
                            <span x-text="{
                                'finished':  'Done',
                                'answered':  'Answered',
                                'completed': 'Done',
                                'missed':    'Missed',
                                'no-answer': 'No Answer',
                                'cancel':    'Cancelled',
                                'busy':      'Busy',
                                'active':    'Live',
                                'failed':    'Failed',
                            }[h.status] || h.status"></span>
                        </span>

                        {{-- Redial button --}}
                        <button type="button"
                                @click="phoneTriggerQuickDial(h.direction === 'outbound' ? (h.dialed_number || h.caller_number || '') : (h.caller_number || ''), h.caller_name || '')"
                                class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-indigo-600 text-slate-400 hover:text-white transition grid place-items-center border border-slate-700 hover:border-indigo-500"
                                title="Redial">
                            <i class="fa-solid fa-phone text-[9px]"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
