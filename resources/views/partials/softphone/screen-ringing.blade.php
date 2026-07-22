{{--
    Premium inbound ringing screen. Full-bleed indigo gradient with
    pulsing caller avatar + Accept/Decline buttons.
    Shown when phoneStatus === 'ringing' || 'ringing_inbound'.
--}}
<div class="absolute inset-0 bg-gradient-to-b from-indigo-600/40 via-indigo-950/70 to-[#070B19] flex flex-col items-center justify-between py-6 px-6">

    {{-- Top: ringing label + caller info --}}
    <div class="flex flex-col items-center text-center w-full pt-4">
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 text-[10px] uppercase tracking-[0.2em] font-bold animate-pulse mb-2">
            <i class="fa-solid fa-phone-volume mr-1"></i>Inbound Call
        </div>

        {{-- Pulsing caller avatar --}}
        <div class="relative w-28 h-28 mb-3">
            <div class="absolute inset-0 rounded-full bg-indigo-500/20 animate-ping"></div>
            <div class="absolute inset-1.5 rounded-full bg-indigo-500/30 animate-pulse"></div>
            <div class="relative w-full h-full rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 grid place-items-center text-3xl font-black text-white shadow-2xl shadow-indigo-500/40">
                <span x-text="(currentCall.caller_name || currentCall.caller_number || '?').toString().replace(/[^a-zA-Z]/g,'').slice(0,2).toUpperCase() || '?'"></span>
            </div>
        </div>

        <h1 class="text-xl font-bold text-white tracking-tight" x-text="currentCall.caller_name || currentCall.caller_number || 'Unknown'"></h1>
        <p class="text-sm text-indigo-300 font-mono mt-1 tabular-nums" x-text="currentCall.caller_number"></p>

        {{-- Country flag + local time pill --}}
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

    {{-- Bottom: Accept / Decline actions --}}
    <div class="w-full grid grid-cols-2 gap-8 px-4 pb-2">
        <button
            type="button"
            @click="phoneHangup()"
            class="flex flex-col items-center gap-2 text-rose-400 group"
        >
            <div class="w-16 h-16 rounded-full bg-rose-500/15 border-2 border-rose-500/40 grid place-items-center group-hover:bg-rose-500/30 group-active:scale-90 transition shadow-lg shadow-rose-500/20">
                <i class="fa-solid fa-phone-slash text-2xl"></i>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-[0.15em]">Decline</span>
        </button>

        <button
            type="button"
            @click="phoneAnswer()"
            class="flex flex-col items-center gap-2 text-indigo-400 group"
        >
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 border-2 border-indigo-300 grid place-items-center text-white shadow-2xl shadow-indigo-500/60 group-hover:from-indigo-400 group-hover:to-blue-500 group-active:scale-90 transition animate-pulse">
                <i class="fa-solid fa-phone text-2xl"></i>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-[0.15em]">Accept</span>
        </button>
    </div>
</div>
