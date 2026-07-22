{{--
    Professional Active Call Screen — v3.0
    Features:
    • Caller info + live duration timer
    • 6-button control grid: Mute · Hold · Keypad · Record · Transfer · Add
    • Conference participant list (when in conference mode)
    • Inline transfer panel (blind + attended)
    • Add-participant panel
    • DTMF keypad overlay
    • All controls wired to state machine
--}}
<div class="absolute inset-0 flex flex-col bg-gradient-to-b from-[#0B1220] via-[#0D1528] to-[#0B1220]">

    {{-- ── TOP: CALLER INFO ─────────────────────────────────────────────────── --}}
    <div class="shrink-0 pt-4 pb-3 px-5 flex flex-col items-center border-b border-slate-800/60">

        {{-- Avatar --}}
        <div class="relative mb-2">
            <div class="w-14 h-14 rounded-2xl grid place-items-center text-lg font-black text-white shadow-xl"
                 :class="currentCall.is_held
                     ? 'bg-gradient-to-br from-amber-500 to-amber-600 shadow-amber-500/30'
                     : 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-emerald-500/30 animate-[pulse_3s_ease-in-out_infinite]'">
                <span x-text="(currentCall.caller_name || currentCall.caller_number || 'C').slice(0,2).toUpperCase()"></span>
            </div>
            {{-- Status ring --}}
            <span class="absolute -inset-1 rounded-2xl border-2 opacity-40 animate-ping"
                  :class="currentCall.is_held ? 'border-amber-400' : 'border-emerald-400'"
                  x-show="!currentCall.is_held"></span>
        </div>

        {{-- Name --}}
        <div class="text-center mt-1">
            <div class="text-[15px] font-bold text-white leading-tight"
                 x-text="currentCall.caller_name || currentCall.caller_number || 'Unknown'">
            </div>
            <template x-if="currentCall.caller_name && currentCall.caller_number">
                <div class="text-[11px] font-mono text-slate-400 mt-0.5"
                     x-text="currentCall.caller_number"></div>
            </template>
        </div>

        {{-- Status + timer row --}}
        <div class="flex items-center gap-2 mt-2">
            <span class="inline-flex items-center gap-1 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border"
                  :class="currentCall.is_held
                      ? 'bg-amber-500/15 text-amber-400 border-amber-500/30'
                      : 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30'">
                <span class="w-1.5 h-1.5 rounded-full"
                      :class="currentCall.is_held ? 'bg-amber-400' : 'bg-emerald-400 animate-pulse'"></span>
                <span x-text="currentCall.is_held ? 'On Hold' : (currentCall.is_muted ? 'Muted' : 'Connected')"></span>
            </span>
            <span class="font-mono text-sm font-bold tabular-nums"
                  :class="currentCall.is_held ? 'text-amber-300' : 'text-white'"
                  x-text="formattedCallDuration"></span>
        </div>
    </div>

    {{-- ── CONFERENCE PARTICIPANT LIST (shown when in conference) ───────────── --}}
    <template x-if="phoneStatus === 'conference' || (phoneStatus === 'active' && heldParticipants.length > 0)">
        <div class="shrink-0 border-b border-slate-800/60 px-3 py-2 space-y-1.5 max-h-28 overflow-y-auto">
            <div class="text-[8px] uppercase tracking-wider text-slate-500 font-bold px-1 mb-1">
                <i class="fa-solid fa-users mr-1"></i> Conference Participants
            </div>
            <template x-for="(p, pi) in heldParticipants" :key="pi">
                <div class="flex items-center gap-2 bg-slate-800/50 rounded-lg px-2 py-1.5 border border-slate-700/50">
                    <div class="w-6 h-6 rounded-lg bg-indigo-500/20 grid place-items-center text-[9px] font-bold text-indigo-300"
                         x-text="(p.name || p.number || '?').slice(0,2).toUpperCase()"></div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] font-bold text-white truncate" x-text="p.name || p.number"></div>
                        <template x-if="p.name">
                            <div class="text-[9px] font-mono text-slate-500" x-text="p.number"></div>
                        </template>
                    </div>
                    <div class="text-[9px] font-mono text-slate-400 tabular-nums" x-text="formattedHeldDuration(p)"></div>
                    <div class="flex items-center gap-1">
                        {{-- Resume/bring back to main --}}
                        <button type="button" @click="phoneResumeHeldParticipant(p)"
                                class="w-6 h-6 rounded-md bg-emerald-600/20 hover:bg-emerald-600 text-emerald-400 hover:text-white transition grid place-items-center"
                                title="Resume / merge">
                            <i class="fa-solid fa-play text-[8px]"></i>
                        </button>
                        {{-- Hangup this participant --}}
                        <button type="button" @click="Alpine.store('softphone').send?.({ type: 'HANGUP_PARTICIPANT', id: p.id })"
                                class="w-6 h-6 rounded-md bg-rose-600/20 hover:bg-rose-600 text-rose-400 hover:text-white transition grid place-items-center"
                                title="Disconnect participant">
                            <i class="fa-solid fa-phone-slash text-[8px]"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </template>

    {{-- ── CONTROL GRID (3×2) ────────────────────────────────────────────────── --}}
    <div class="flex-1 px-4 py-3 grid grid-cols-3 gap-y-4 gap-x-2 place-items-center content-center">

        {{-- MUTE --}}
        <button type="button"
                @click="currentCall.is_muted ? phoneUnmute() : phoneMute()"
                class="flex flex-col items-center gap-1.5 group w-20 select-none">
            <div class="w-12 h-12 rounded-full grid place-items-center transition-all duration-200 border shadow-sm"
                 :class="currentCall.is_muted
                     ? 'bg-rose-500 border-rose-400 text-white shadow-rose-500/40'
                     : 'bg-slate-800/60 border-slate-700 text-slate-300 group-hover:bg-slate-700 group-hover:border-slate-600'">
                <i class="fa-solid text-base" :class="currentCall.is_muted ? 'fa-microphone-slash' : 'fa-microphone'"></i>
            </div>
            <span class="text-[9px] font-bold uppercase tracking-wider"
                  :class="currentCall.is_muted ? 'text-rose-400' : 'text-slate-400'"
                  x-text="currentCall.is_muted ? 'Unmute' : 'Mute'"></span>
        </button>

        {{-- HOLD / RESUME --}}
        <button type="button"
                @click="currentCall.is_held ? phoneResume() : phoneHold()"
                class="flex flex-col items-center gap-1.5 group w-20 select-none">
            <div class="w-12 h-12 rounded-full grid place-items-center transition-all duration-200 border shadow-sm"
                 :class="currentCall.is_held
                     ? 'bg-amber-500 border-amber-400 text-white shadow-amber-500/40'
                     : 'bg-slate-800/60 border-slate-700 text-slate-300 group-hover:bg-slate-700 group-hover:border-slate-600'">
                <i class="fa-solid text-base" :class="currentCall.is_held ? 'fa-play' : 'fa-pause'"></i>
            </div>
            <span class="text-[9px] font-bold uppercase tracking-wider"
                  :class="currentCall.is_held ? 'text-amber-400' : 'text-slate-400'"
                  x-text="currentCall.is_held ? 'Resume' : 'Hold'"></span>
        </button>

        {{-- KEYPAD --}}
        <button type="button"
                @click="keypadPanelOpen = !keypadPanelOpen"
                class="flex flex-col items-center gap-1.5 group w-20 select-none">
            <div class="w-12 h-12 rounded-full grid place-items-center transition-all duration-200 border shadow-sm"
                 :class="keypadPanelOpen
                     ? 'bg-indigo-600 border-indigo-500 text-white'
                     : 'bg-slate-800/60 border-slate-700 text-slate-300 group-hover:bg-slate-700 group-hover:border-slate-600'">
                <i class="fa-solid fa-keyboard text-base"></i>
            </div>
            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Keypad</span>
        </button>

        {{-- RECORD --}}
        <button type="button"
                @click="phoneToggleRecording()"
                class="flex flex-col items-center gap-1.5 group w-20 select-none">
            <div class="w-12 h-12 rounded-full grid place-items-center transition-all duration-200 border shadow-sm"
                 :class="currentCall.recording_paused
                     ? 'bg-slate-800 border-slate-700 text-slate-500'
                     : 'bg-slate-800/60 border-slate-700 text-rose-400 group-hover:bg-rose-600/20 group-hover:border-rose-500/50'">
                <i class="fa-solid fa-circle-dot text-base"
                   :class="currentCall.recording_paused ? '' : 'animate-pulse'"></i>
            </div>
            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400"
                  x-text="currentCall.recording_paused ? 'Paused' : 'Record'"></span>
        </button>

        {{-- TRANSFER --}}
        <button type="button"
                @click="openInlineTransfer()"
                class="flex flex-col items-center gap-1.5 group w-20 select-none">
            <div class="w-12 h-12 rounded-full grid place-items-center transition-all duration-200 border shadow-sm bg-slate-800/60 border-slate-700 text-slate-300 group-hover:bg-indigo-600 group-hover:border-indigo-500 group-hover:text-white">
                <i class="fa-solid fa-arrow-right-arrow-left text-base"></i>
            </div>
            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Transfer</span>
        </button>

        {{-- ADD / CONFERENCE --}}
        <button type="button"
                @click="openAddOrCallPanel()"
                class="flex flex-col items-center gap-1.5 group w-20 select-none">
            <div class="w-12 h-12 rounded-full grid place-items-center transition-all duration-200 border shadow-sm bg-slate-800/60 border-slate-700 text-slate-300 group-hover:bg-emerald-600 group-hover:border-emerald-500 group-hover:text-white">
                <i class="fa-solid fa-user-plus text-base"></i>
            </div>
            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Add / Conf</span>
        </button>
    </div>

    {{-- ── END CALL CTA ──────────────────────────────────────────────────────── --}}
    <div class="shrink-0 px-5 pb-5 pt-1">
        <button type="button"
                @click="phoneHangup()"
                class="w-full py-3.5 bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-400 text-white font-bold rounded-2xl shadow-lg shadow-rose-500/30 transition active:scale-[0.98] flex items-center justify-center gap-2 text-sm">
            <i class="fa-solid fa-phone-slash"></i>
            <span>End Call</span>
        </button>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════
         ── DTMF KEYPAD OVERLAY ─────────────────────────────────────────────
    ════════════════════════════════════════════════════════════════════════ --}}
    <template x-if="keypadPanelOpen">
        <div class="absolute inset-0 bg-[#0B1220]/98 backdrop-blur-sm flex flex-col z-40 rounded-[inherit]"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4">

            <div class="shrink-0 h-12 px-4 border-b border-slate-800 flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">
                    <i class="fa-solid fa-keyboard mr-1.5 text-indigo-400"></i>DTMF Keypad
                </span>
                <button type="button" @click="keypadPanelOpen = false; dtmfInput = ''"
                        class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white border border-slate-700 transition grid place-items-center">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <div class="shrink-0 px-5 py-3">
                <div class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 min-h-[2.5rem] flex items-center">
                    <span class="font-mono text-xl font-bold text-white tracking-[0.25em] flex-1 min-w-0 truncate"
                          x-text="dtmfInput || ''">&nbsp;</span>
                    <button type="button" @click="dtmfInput = dtmfInput.slice(0, -1)"
                            x-show="dtmfInput.length > 0"
                            class="text-slate-500 hover:text-white transition p-1">
                        <i class="fa-solid fa-delete-left text-sm"></i>
                    </button>
                </div>
            </div>

            <div class="flex-1 px-5 pb-3 grid grid-cols-3 gap-2.5 content-center">
                <template x-for="key in ['1','2','3','4','5','6','7','8','9','*','0','#']" :key="key">
                    <button type="button"
                            @click="phoneSendDtmf(key)"
                            class="h-14 rounded-2xl bg-slate-800/80 border border-slate-700 hover:bg-indigo-600 hover:border-indigo-500 text-white font-bold text-xl transition active:scale-95 shadow-sm"
                            x-text="key">
                    </button>
                </template>
            </div>

            <div class="shrink-0 px-5 pb-5">
                <button type="button" @click="phoneHangup()"
                        class="w-full py-3 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-bold transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-phone-slash"></i> End Call
                </button>
            </div>
        </div>
    </template>

    {{-- ════════════════════════════════════════════════════════════════════════
         ── TRANSFER PANEL OVERLAY ──────────────────────────────────────────
    ════════════════════════════════════════════════════════════════════════ --}}
    <template x-if="inlineTransferOpen">
        <div class="absolute inset-0 bg-[#0B1220]/98 backdrop-blur-sm flex flex-col z-40 rounded-[inherit]"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4">

            {{-- Header --}}
            <div class="shrink-0 h-12 px-4 border-b border-slate-800 flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">
                    <i class="fa-solid fa-arrow-right-arrow-left mr-1.5 text-indigo-400"></i>Transfer Call
                </span>
                <button type="button" @click="inlineTransferOpen = false"
                        class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white border border-slate-700 transition grid place-items-center">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            {{-- Transfer type tabs --}}
            <div class="shrink-0 flex gap-1 px-4 pt-3 pb-2">
                <button type="button"
                        @click="transferType = 'blind'"
                        class="flex-1 py-2 rounded-xl text-[10px] font-bold uppercase tracking-wider transition border"
                        :class="transferType === 'blind'
                            ? 'bg-indigo-600 border-indigo-500 text-white shadow-md shadow-indigo-500/30'
                            : 'bg-slate-800/60 border-slate-700 text-slate-400 hover:bg-slate-700'">
                    <i class="fa-solid fa-forward mr-1"></i>Blind
                </button>
                <button type="button"
                        @click="transferType = 'attended'"
                        class="flex-1 py-2 rounded-xl text-[10px] font-bold uppercase tracking-wider transition border"
                        :class="transferType === 'attended'
                            ? 'bg-indigo-600 border-indigo-500 text-white shadow-md shadow-indigo-500/30'
                            : 'bg-slate-800/60 border-slate-700 text-slate-400 hover:bg-slate-700'">
                    <i class="fa-solid fa-handshake mr-1"></i>Attended
                </button>
            </div>

            {{-- Type description --}}
            <div class="shrink-0 px-4 pb-2">
                <p class="text-[9px] text-slate-500 leading-relaxed"
                   x-text="transferType === 'blind'
                       ? 'Blind: Immediately transfers the call. You are disconnected.'
                       : 'Attended: You speak with the target first, then hand over.'">
                </p>
            </div>

            {{-- Number input --}}
            <div class="shrink-0 px-4 pb-3">
                <label class="block text-[9px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Transfer to</label>
                <div class="relative">
                    <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input type="text"
                           id="transfer-number-input"
                           x-model="transferTarget"
                           @keyup.enter="phoneExecuteTransfer()"
                           placeholder="Number or extension…"
                           class="w-full pl-8 pr-3 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm font-bold text-white placeholder-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
                </div>
            </div>

            {{-- Teammate quick-select --}}
            <div class="flex-1 overflow-y-auto px-4 pb-2 space-y-1">
                <div class="text-[9px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                    <i class="fa-solid fa-users mr-1"></i> Quick select
                </div>
                <template x-for="t in teammates" :key="t.id || t.extension">
                    <button type="button"
                            @click="transferTarget = t.extension || t.number || ''; $nextTick(() => $el.closest('.absolute').querySelector('#transfer-number-input').focus())"
                            class="w-full flex items-center gap-2.5 p-2 rounded-xl bg-slate-800/50 border border-slate-700/50 hover:bg-slate-700 hover:border-slate-600 transition text-left">
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/20 grid place-items-center text-[10px] font-bold text-indigo-300 shrink-0"
                             x-text="(t.name || '?').slice(0,2).toUpperCase()"></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[11px] font-bold text-white truncate" x-text="t.name"></div>
                            <div class="text-[9px] font-mono text-slate-500" x-text="t.extension || t.number || ''"></div>
                        </div>
                        <span class="shrink-0 w-2 h-2 rounded-full"
                              :class="{
                                  'bg-emerald-500': t.status === 'online',
                                  'bg-amber-500':   t.status === 'away',
                                  'bg-rose-500':    t.status === 'busy',
                                  'bg-slate-600':   !t.status || t.status === 'offline',
                              }"></span>
                    </button>
                </template>
                {{-- Queue quick-select --}}
                <template x-if="queues && queues.length > 0">
                    <div>
                        <div class="text-[9px] font-bold uppercase tracking-wider text-slate-500 mt-2 mb-1.5">
                            <i class="fa-solid fa-sitemap mr-1"></i> Queues
                        </div>
                        <template x-for="q in queues" :key="q.id">
                            <button type="button"
                                    @click="transferTarget = q.number || q.id || ''"
                                    class="w-full flex items-center gap-2.5 p-2 rounded-xl bg-slate-800/50 border border-slate-700/50 hover:bg-slate-700 hover:border-slate-600 transition text-left">
                                <div class="w-7 h-7 rounded-lg bg-violet-500/20 grid place-items-center text-violet-400 shrink-0">
                                    <i class="fa-solid fa-headset text-[11px]"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[11px] font-bold text-white truncate" x-text="q.name"></div>
                                    <div class="text-[9px] font-mono text-slate-500" x-text="'Ext ' + (q.number || q.id)"></div>
                                </div>
                            </button>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Action button --}}
            <div class="shrink-0 px-4 pb-5 pt-2">
                <button type="button"
                        @click="phoneExecuteTransfer()"
                        :disabled="!transferTarget"
                        class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-30 disabled:cursor-not-allowed text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/20 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    <span x-text="transferType === 'blind' ? 'Transfer Now' : 'Start Consult'"></span>
                </button>
            </div>
        </div>
    </template>

    {{-- ════════════════════════════════════════════════════════════════════════
         ── ADD PARTICIPANT / CONFERENCE PANEL ─────────────────────────────
    ════════════════════════════════════════════════════════════════════════ --}}
    <template x-if="addOrCallOpen">
        <div class="absolute inset-0 bg-[#0B1220]/98 backdrop-blur-sm flex flex-col z-40 rounded-[inherit]"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">

            {{-- Header --}}
            <div class="shrink-0 h-12 px-4 border-b border-slate-800 flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">
                    <i class="fa-solid fa-user-plus mr-1.5 text-emerald-400"></i>Add to Call
                </span>
                <button type="button" @click="closeAddOrCallPanel()"
                        class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white border border-slate-700 transition grid place-items-center">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            {{-- Number input --}}
            <div class="shrink-0 px-4 pt-3 pb-2">
                <div class="relative">
                    <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input type="text"
                           id="add-call-input"
                           x-model="addOrCallInput"
                           @keyup.enter="if (addOrCallInput.length >= 3) phoneAddConferenceParticipant(addOrCallInput)"
                           placeholder="Enter number to add…"
                           class="w-full pl-8 pr-3 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm font-bold text-white placeholder-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition">
                </div>
            </div>

            {{-- Quick-select from phonebook / teammates --}}
            <div class="shrink-0 flex gap-1 px-4 pb-2">
                <button type="button"
                        @click="addOrCallTab = 'phonebook'"
                        class="flex-1 py-1.5 rounded-lg text-[9px] font-bold uppercase tracking-wider border transition"
                        :class="addOrCallTab === 'phonebook'
                            ? 'bg-emerald-600/20 border-emerald-500/40 text-emerald-400'
                            : 'bg-slate-800 border-slate-700 text-slate-500 hover:text-slate-300'">
                    <i class="fa-solid fa-address-book mr-1"></i>Contacts
                </button>
                <button type="button"
                        @click="addOrCallTab = 'teammates'"
                        class="flex-1 py-1.5 rounded-lg text-[9px] font-bold uppercase tracking-wider border transition"
                        :class="addOrCallTab === 'teammates'
                            ? 'bg-emerald-600/20 border-emerald-500/40 text-emerald-400'
                            : 'bg-slate-800 border-slate-700 text-slate-500 hover:text-slate-300'">
                    <i class="fa-solid fa-users mr-1"></i>Agents
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-4 pb-2 space-y-1">
                <template x-if="addOrCallTab === 'phonebook'">
                    <div class="space-y-1">
                        <template x-for="c in filteredPhonebook.slice(0, 20)" :key="c.id">
                            <button type="button"
                                    @click="phoneAddConferenceParticipant(c.phone_number || c.phone)"
                                    class="w-full flex items-center gap-2.5 p-2 rounded-xl bg-slate-800/50 border border-slate-700/50 hover:bg-emerald-600/10 hover:border-emerald-500/30 transition text-left">
                                <div class="w-7 h-7 rounded-lg bg-slate-700 grid place-items-center text-[10px] font-bold text-slate-300 shrink-0"
                                     x-text="(c.name || '?').slice(0,2).toUpperCase()"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[11px] font-bold text-white truncate" x-text="c.name"></div>
                                    <div class="text-[9px] font-mono text-slate-500" x-text="c.phone_number || c.phone"></div>
                                </div>
                                <i class="fa-solid fa-phone-plus text-emerald-500 text-[10px] shrink-0"></i>
                            </button>
                        </template>
                        <template x-if="filteredPhonebook.length === 0">
                            <p class="text-center text-[10px] text-slate-600 py-4">No contacts found</p>
                        </template>
                    </div>
                </template>
                <template x-if="addOrCallTab === 'teammates'">
                    <div class="space-y-1">
                        <template x-for="t in teammates" :key="t.id">
                            <button type="button"
                                    @click="phoneAddConferenceParticipant(t.extension || t.number)"
                                    :disabled="t.status === 'busy'"
                                    class="w-full flex items-center gap-2.5 p-2 rounded-xl border transition text-left"
                                    :class="t.status === 'busy'
                                        ? 'bg-slate-800/30 border-slate-800 opacity-50 cursor-not-allowed'
                                        : 'bg-slate-800/50 border-slate-700/50 hover:bg-emerald-600/10 hover:border-emerald-500/30'">
                                <div class="w-7 h-7 rounded-lg bg-indigo-500/20 grid place-items-center text-[10px] font-bold text-indigo-300 shrink-0"
                                     x-text="(t.name || '?').slice(0,2).toUpperCase()"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[11px] font-bold text-white truncate" x-text="t.name"></div>
                                    <div class="text-[9px] font-mono text-slate-500" x-text="t.extension || t.number || ''"></div>
                                </div>
                                <span class="shrink-0 text-[8px] font-bold uppercase px-1.5 py-0.5 rounded"
                                      :class="{
                                          'bg-emerald-500/20 text-emerald-400': t.status === 'online',
                                          'bg-amber-500/20 text-amber-400':   t.status === 'away',
                                          'bg-rose-500/20 text-rose-400':    t.status === 'busy',
                                          'bg-slate-700 text-slate-500':   !t.status || t.status === 'offline',
                                      }"
                                      x-text="t.status || 'offline'"></span>
                            </button>
                        </template>
                        <template x-if="!teammates || teammates.length === 0">
                            <p class="text-center text-[10px] text-slate-600 py-4">No agents available</p>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Add button --}}
            <div class="shrink-0 px-4 pb-5 pt-2">
                <button type="button"
                        @click="phoneAddConferenceParticipant(addOrCallInput)"
                        :disabled="!addOrCallInput || addOrCallInput.length < 3"
                        class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-30 disabled:cursor-not-allowed text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/20 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Add to Conference</span>
                </button>
            </div>
        </div>
    </template>

    {{-- ════════════════════════════════════════════════════════════════════════
         ── ATTENDED TRANSFER IN-PROGRESS BANNER ────────────────────────────
    ════════════════════════════════════════════════════════════════════════ --}}
    <template x-if="attendedTransferPending">
        <div class="absolute bottom-20 left-4 right-4 bg-indigo-900/90 border border-indigo-500/40 rounded-2xl p-3 z-30 backdrop-blur-sm shadow-xl">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/20 grid place-items-center shrink-0">
                    <i class="fa-solid fa-handshake text-indigo-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[11px] font-bold text-white">Consulting…</div>
                    <div class="text-[9px] text-indigo-300 truncate"
                         x-text="transferTarget || 'Transfer destination'"></div>
                </div>
                <div class="flex gap-1.5">
                    <button type="button"
                            @click="Alpine.store('softphone').send?.({ type: 'COMPLETE_TRANSFER', callId: currentCall.id })"
                            class="px-2 py-1 bg-emerald-600 hover:bg-emerald-500 text-white text-[9px] font-bold rounded-lg transition">
                        Complete
                    </button>
                    <button type="button"
                            @click="Alpine.store('softphone').send?.({ type: 'CANCEL_TRANSFER', callId: currentCall.id })"
                            class="px-2 py-1 bg-rose-600/80 hover:bg-rose-600 text-white text-[9px] font-bold rounded-lg transition">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
