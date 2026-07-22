{{--
    Premium active call screen. Hero with caller + timer + 6-button control
    grid (mute/keypad/record/hold/transfer/add) + big red end-call CTA.
    Shown when phoneStatus === 'active' || 'speaking'.
--}}
<div class="absolute inset-0 bg-gradient-to-b from-indigo-900/40 via-slate-950 to-[#0A0612] flex flex-col">

    {{-- ── ATTENDED TRANSFER BANNER ─────────────────────────────────────────
         Shown while agent is consulting the transfer destination.
         The first call is on hold. Agent can complete (bridge) or cancel.
    --}}
    <template x-if="attendedTransferPending">
        <div class="shrink-0 w-full bg-amber-600/20 border-b border-amber-500/30 px-4 py-3 z-20">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                <span class="text-[11px] font-bold text-amber-300 uppercase tracking-wider">Warm Transfer — Consulting</span>
            </div>
            <p class="text-[10px] text-slate-400 mb-3">First caller is on hold. Talk to the destination, then complete or cancel.</p>
            <div class="flex gap-2">
                <button type="button"
                    @click="phoneMergeCalls()"
                    class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold rounded-lg transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-check text-[10px]"></i>
                    Complete Transfer
                </button>
                <button type="button"
                    @click="phoneCancelAttendedTransfer()"
                    class="flex-1 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-[11px] font-bold rounded-lg transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-xmark text-[10px]"></i>
                    Cancel
                </button>
            </div>
        </div>
    </template>

    {{-- ── TOP BAR: Held Participant ─────────────────────────────────────────
         Shown when there is a held participant (multi-party call in progress).
         Only visible when NOT in attended transfer mode.
    --}}
    <template x-if="heldParticipants.length > 0 && !attendedTransferPending">
        <div class="shrink-0 w-full bg-[#1E1B4B]/80 border-b border-indigo-500/20 p-3 flex items-center justify-between gap-3 shadow-md z-10">
            <div class="flex items-center gap-2 min-w-0">
                <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 grid place-items-center text-xs font-bold text-slate-300">
                    <span x-text="(heldParticipants[0].name || '?').slice(0,2).toUpperCase()"></span>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-bold text-white truncate" x-text="heldParticipants[0].name"></div>
                    <div class="text-[9px] text-indigo-300 font-bold uppercase tracking-wider flex items-center gap-1">
                        <span x-text="formattedHeldDuration(heldParticipants[0])"></span>
                        <span>| On hold</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="phoneExecuteTransfer('blind', heldParticipants[0].number)"
                    class="flex flex-col items-center justify-center p-1 hover:text-indigo-400 transition" title="Transfer Call">
                    <i class="fa-solid fa-arrow-right-arrow-left text-[11px]"></i>
                    <span class="text-[8px] font-bold uppercase mt-0.5">Transfer</span>
                </button>
                <button type="button" @click="phoneMergeCalls()"
                    class="flex flex-col items-center justify-center p-1 hover:text-emerald-400 transition" title="Complete Transfer">
                    <i class="fa-solid fa-code-merge text-[11px]"></i>
                    <span class="text-[8px] font-bold uppercase mt-0.5">Merge</span>
                </button>
            </div>
        </div>
    </template>

    {{-- Hero: caller + live timer pill --}}
    <div class="shrink-0 px-6 pt-6 pb-4 text-center bg-gradient-to-b from-indigo-950/20 to-transparent border-b border-indigo-500/10">
        <div class="mx-auto w-16 h-16 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 grid place-items-center text-xl font-black text-white shadow-xl shadow-indigo-500/40 mb-3">
            <span x-text="(currentCall.caller_name || currentCall.caller_number || '?').toString().replace(/[^a-zA-Z]/g,'').slice(0,2).toUpperCase() || '?'"></span>
        </div>
        <h2 class="text-base font-bold text-white tracking-tight" x-text="currentCall.caller_name || currentCall.caller_number || 'Unknown'"></h2>
        <p class="text-xs text-indigo-300 font-mono mt-0.5 tabular-nums" x-text="currentCall.caller_number"></p>

        {{-- Live timer pill --}}
        <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/15 border border-indigo-500/30 text-indigo-300">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
            <span class="font-mono font-bold tabular-nums text-xs" x-text="formattedCallDuration"></span>
        </div>
    </div>

    {{-- 6-button control grid (3x2) --}}
    <div class="flex-1 px-6 py-5 grid grid-cols-3 gap-y-5 gap-x-3 place-items-center content-center">
        {{-- Mute --}}
        <button type="button" @click="currentCall.is_muted ? phoneUnmute() : phoneMute()" class="flex flex-col items-center gap-1.5 group w-20">
            <div class="w-11 h-11 rounded-full grid place-items-center transition-all duration-200 border"
                 :class="currentCall.is_muted ? 'bg-rose-500 border-rose-400 text-white shadow-lg shadow-rose-500/40' : 'bg-slate-800/60 border-slate-700 text-slate-300 group-hover:bg-slate-700 group-hover:border-slate-600'">
                <i class="fa-solid text-base" :class="currentCall.is_muted ? 'fa-microphone-slash' : 'fa-microphone'"></i>
            </div>
            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400" x-text="currentCall.is_muted ? 'Muted' : 'Mute'"></span>
        </button>

        {{-- Hold / Resume --}}
        <button type="button" @click="currentCall.is_held ? phoneResume() : phoneHold()" class="flex flex-col items-center gap-1.5 group w-20">
            <div class="w-11 h-11 rounded-full grid place-items-center transition-all duration-200 border"
                 :class="currentCall.is_held ? 'bg-amber-500 border-amber-400 text-white shadow-lg shadow-amber-500/40' : 'bg-slate-800/60 border-slate-700 text-slate-300 group-hover:bg-slate-700 group-hover:border-slate-600'">
                <i class="fa-solid text-base" :class="currentCall.is_held ? 'fa-play' : 'fa-pause'"></i>
            </div>
            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400" x-text="currentCall.is_held ? 'Resume' : 'Hold'"></span>
        </button>

        {{-- Keypad --}}
        <button type="button" @click="keypadPanelOpen = !keypadPanelOpen" class="flex flex-col items-center gap-1.5 group w-20">
            <div class="w-11 h-11 rounded-full grid place-items-center transition-all duration-200 border"
                 :class="keypadPanelOpen ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-slate-800/60 border-slate-700 text-slate-300 group-hover:bg-slate-700 group-hover:border-slate-600'">
                <i class="fa-solid fa-keyboard text-base"></i>
            </div>
            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Keypad</span>
        </button>

        {{-- Record --}}
        <button type="button" @click="phoneToggleRecording()" class="flex flex-col items-center gap-1.5 group w-20">
            <div class="w-11 h-11 rounded-full grid place-items-center transition-all duration-200 border"
                 :class="currentCall.recording_paused ? 'bg-slate-800 border-slate-700 text-slate-500' : 'bg-slate-800/60 border-slate-700 text-slate-300 group-hover:bg-rose-600 group-hover:border-rose-500'">
                <i class="fa-solid fa-circle-dot text-base" :class="currentCall.recording_paused ? '' : 'text-rose-400 animate-pulse'"></i>
            </div>
            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Record</span>
        </button>

        {{-- Transfer --}}
        <button type="button" @click="openInlineTransfer()" class="flex flex-col items-center gap-1.5 group w-20">
            <div class="w-11 h-11 rounded-full bg-slate-800/60 border border-slate-700 grid place-items-center text-slate-300 group-hover:bg-indigo-600 group-hover:border-indigo-500 transition">
                <i class="fa-solid fa-arrow-right-arrow-left text-base"></i>
            </div>
            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Transfer</span>
        </button>

        {{-- Add Participant --}}
        <button type="button" @click="openAddOrCallPanel()" class="flex flex-col items-center gap-1.5 group w-20">
            <div class="w-11 h-11 rounded-full bg-slate-800/60 border border-slate-700 grid place-items-center text-slate-300 group-hover:bg-indigo-600 group-hover:border-indigo-500 transition">
                <i class="fa-solid fa-user-plus text-base"></i>
            </div>
            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Add</span>
        </button>
    </div>

    {{-- End call CTA --}}
    <div class="shrink-0 px-6 pb-6 pt-2">
        <button
            type="button"
            @click="phoneHangup()"
            class="w-full py-3 bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-400 text-white font-bold rounded-xl shadow-lg shadow-rose-500/30 transition active:scale-[0.98] flex items-center justify-center gap-2"
        >
            <i class="fa-solid fa-phone-slash"></i>
            <span>End Call</span>
        </button>
    </div>

    {{-- ── DTMF KEYPAD OVERLAY ──────────────────────────────────────────────
         Slides up from bottom when keypadPanelOpen is true.
         Provides in-call digit entry (IVR, voicemail PIN, etc.)
    --}}
    <template x-if="keypadPanelOpen">
        <div class="absolute inset-0 bg-[#0B1220]/98 flex flex-col z-40"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4">

            {{-- Header --}}
            <div class="shrink-0 h-12 px-4 border-b border-slate-800 flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">Keypad</span>
                <button type="button" @click="keypadPanelOpen = false; dtmfInput = ''"
                        class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white border border-slate-700 transition grid place-items-center">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            {{-- DTMF input display --}}
            <div class="shrink-0 px-6 py-3 text-center">
                <div class="font-mono text-2xl font-bold text-white tracking-[0.3em] min-h-[2.5rem]"
                     x-text="dtmfInput || '&nbsp;'">&nbsp;</div>
            </div>

            {{-- Digit grid --}}
            <div class="flex-1 grid grid-cols-3 gap-3 px-8 pb-4 content-center">
                <template x-for="key in ['1','2','3','4','5','6','7','8','9','*','0','#']" :key="key">
                    <button type="button"
                            @click="phoneSendDtmf(key)"
                            class="h-14 rounded-2xl bg-slate-800/70 border border-slate-700 hover:bg-indigo-600 hover:border-indigo-500 text-white font-bold text-lg transition active:scale-95 shadow-sm"
                            x-text="key">
                    </button>
                </template>
            </div>

            {{-- Clear + End --}}
            <div class="shrink-0 px-6 pb-5 flex gap-3">
                <button type="button" @click="dtmfInput = dtmfInput.slice(0, -1)"
                        class="flex-1 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-sm font-bold transition">
                    <i class="fa-solid fa-delete-left mr-1"></i> Delete
                </button>
                <button type="button" @click="phoneHangup()"
                        class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-bold transition">
                    <i class="fa-solid fa-phone-slash mr-1"></i> End
                </button>
            </div>
        </div>
    </template>
</div>
