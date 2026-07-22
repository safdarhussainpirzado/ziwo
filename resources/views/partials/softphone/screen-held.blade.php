{{--
    Premium held screen. Muted slate gradient + "ON HOLD" badge with
    pulsing dots + Resume button. Shown when phoneStatus === 'held'.
--}}
<div class="absolute inset-0 bg-gradient-to-b from-amber-600/30 via-amber-950/40 to-[#1A1303] flex flex-col items-center justify-center px-6 text-center">

    <div class="relative mb-5">
        <div class="absolute inset-0 rounded-full bg-amber-500/15 animate-pulse"></div>
        <div class="relative w-20 h-20 rounded-full bg-slate-800/80 border-2 border-amber-500/40 grid place-items-center text-3xl text-slate-500 shadow-xl">
            <i class="fa-solid fa-pause"></i>
        </div>
    </div>

    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-300 text-[10px] uppercase tracking-[0.15em] font-bold mb-3">
        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
        On Hold
    </div>

    <h2 class="text-base font-bold text-white tracking-tight" x-text="currentCall.caller_name || currentCall.caller_number || 'Unknown'"></h2>
    <p class="text-xs text-slate-500 font-mono mt-1 tabular-nums" x-text="currentCall.caller_number"></p>

    <button
        type="button"
        @click="phoneResume()"
        class="mt-8 w-16 h-16 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 border-2 border-emerald-300 grid place-items-center text-white shadow-2xl shadow-emerald-500/60 hover:from-emerald-300 hover:to-emerald-500 active:scale-90 transition"
    >
        <i class="fa-solid fa-play text-2xl"></i>
    </button>
    <span class="mt-2 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">Resume</span>

    <button
        type="button"
        @click="phoneHangup()"
        class="mt-6 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-500 hover:text-rose-400 transition"
    >
        End call
    </button>
</div>
