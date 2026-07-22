{{--
    Premium outbound ringing screen. Violet/purple gradient.
    Single red cancel button that ACTUALLY hangs up via phoneHangup().
    Shown when phoneStatus === 'ringing_outbound'.
--}}
<div class="absolute inset-0 bg-gradient-to-b from-violet-500/40 via-violet-950/60 to-[#100625] flex flex-col items-center justify-between py-6 px-6">

    {{-- Top Bar: Held Participant --}}
    <template x-if="heldParticipants.length > 0">
        <div class="shrink-0 w-full bg-[#0F172A]/80 border border-slate-800 rounded-xl p-3 flex items-center justify-between gap-3 shadow-lg -mt-3 mb-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 grid place-items-center text-xs font-black text-slate-300">
                    <span x-text="(heldParticipants[0].name || '?').slice(0,2).toUpperCase()"></span>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-bold text-white truncate" x-text="heldParticipants[0].name"></div>
                    <div class="text-[9px] text-amber-400 font-bold uppercase tracking-wider">On Hold</div>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" @click="phoneResumeHeldParticipant(heldParticipants[0].id)" class="w-7 h-7 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition grid place-items-center" title="Resume Held Call">
                    <i class="fa-solid fa-phone text-[10px]"></i>
                </button>
            </div>
        </div>
    </template>

    {{-- Top: Outbound Call label + caller --}}
    <div class="flex flex-col items-center text-center w-full pt-4">
        <p class="text-[10px] uppercase tracking-[0.2em] font-bold text-violet-300 animate-pulse mb-2">
            <i class="fa-solid fa-circle-notch fa-spin mr-1"></i>Outbound Call
        </p>

        <h1 class="text-xl font-bold text-white tracking-tight" x-text="currentCall.caller_name || currentCall.caller_number || dialNumberInput || 'Calling...'"></h1>

        {{-- Flag + local time pill --}}
        <div class="mt-2 inline-flex items-center gap-1.5 bg-black/30 border border-white/10 rounded-full px-3 py-1 text-[11px] text-white"
             x-show="currentCall.caller_number">
            <template x-if="['PK','US','GB','AE','SA'].includes(getCountryFlagAndLocalTime(currentCall.caller_number).code)">
                <img :src="'/images/flags/' + getCountryFlagAndLocalTime(currentCall.caller_number).code.toLowerCase() + '.svg'"
                     class="w-4 h-3 rounded-sm object-cover">
            </template>
            <template x-if="!['PK','US','GB','AE','SA'].includes(getCountryFlagAndLocalTime(currentCall.caller_number).code)">
                <span x-text="getCountryFlagAndLocalTime(currentCall.caller_number).flag"></span>
            </template>
            <span class="font-mono font-bold tabular-nums" x-text="getCountryFlagAndLocalTime(currentCall.caller_number).time"></span>
            <span class="opacity-70">local time</span>
        </div>
    </div>

    {{-- Middle: avatar + timer --}}
    <div class="flex flex-col items-center">
        <div class="relative w-28 h-28 mb-3">
            <div class="absolute inset-0 rounded-full bg-violet-500/20 animate-ping"></div>
            <div class="absolute inset-1.5 rounded-full bg-violet-500/30 animate-pulse"></div>
            <div class="relative w-full h-full rounded-full bg-gradient-to-br from-violet-500 to-purple-600 grid place-items-center text-2xl font-black text-white shadow-2xl shadow-violet-500/40">
                <span x-text="(currentCall.caller_name || '?').slice(0,1).toUpperCase()"></span>
            </div>
        </div>
        <div class="font-mono text-2xl font-bold text-white tabular-nums" x-text="formattedCallDuration"></div>
        <div class="text-[10px] uppercase tracking-[0.15em] text-slate-300 mt-1">Ringing</div>
    </div>

    {{-- Bottom: Cancel button that ACTUALLY hangs up --}}
    <div class="w-full px-4 pb-2">
        <button
            type="button"
            @click="phoneHangup()"
            data-testid="outbound-cancel"
            class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-rose-500 to-rose-600 hover:from-rose-400 hover:to-rose-500 grid place-items-center text-white shadow-2xl shadow-rose-500/50 transition active:scale-90 ring-4 ring-rose-500/20"
            title="Cancel outbound call"
        >
            <i class="fa-solid fa-phone-slash text-2xl"></i>
        </button>
        <p class="text-[10px] uppercase tracking-[0.15em] text-slate-400 text-center mt-2 font-bold">Cancel</p>
    </div>
</div>
