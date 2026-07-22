{{--
    Premium Dialer tab. Bento-style stats ribbon + number display + 3x4 keypad + dial CTA.
    Shown when phoneTab === 'dialer'.
--}}
<template x-if="phoneTab === 'dialer'">
    <div
        class="absolute inset-0 flex flex-col"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
    >

        {{-- Stats ribbon (bento-style) --}}
        <div class="shrink-0 px-3 py-2 border-b border-slate-800 bg-[#0F172A]/40 flex items-center justify-around text-center">
            <div class="flex-1">
                <div class="text-sm font-bold text-white tabular-nums leading-tight" x-text="recentCallLogs.length || 0"></div>
                <div class="text-[8px] uppercase tracking-wider text-slate-500 font-bold">Calls</div>
            </div>
            <div class="w-px h-6 bg-slate-800"></div>
            <div class="flex-1">
                <div class="text-sm font-bold text-emerald-400 tabular-nums leading-tight" x-text="(recentCallLogs.filter(h => h.status === 'answered' || h.status === 'finished').length) || 0"></div>
                <div class="text-[8px] uppercase tracking-wider text-slate-500 font-bold">Answered</div>
            </div>
            <div class="w-px h-6 bg-slate-800"></div>
            <div class="flex-1">
                <div class="text-sm font-bold text-white tabular-nums leading-tight" x-text="formatSecondsToShort(recentCallLogs.reduce((a,h)=>a+(h.duration_sec||0),0))"></div>
                <div class="text-[8px] uppercase tracking-wider text-slate-500 font-bold">Talk</div>
            </div>
        </div>

        {{-- Number display --}}
        <div class="shrink-0 px-3 pt-3">
            <div class="relative bg-[#0F172A]/60 border border-slate-700 rounded-2xl px-4 py-3 flex items-center shadow-inner">
                <i class="fa-solid fa-phone text-indigo-400 text-xs"></i>
                <input
                    type="text"
                    id="dialer-input"
                    x-model="dialNumberInput"
                    placeholder="Enter number…"
                    @keydown="handleDialerKey($event)"
                    @keyup.enter="if (dialNumberInput.length >= 3) phoneDial()"
                    class="flex-1 ml-3 bg-transparent text-base font-bold tracking-wider text-white outline-none placeholder-slate-700"
                >
                <button
                    x-show="dialNumberInput.length > 0"
                    @click="dialNumberInput = dialNumberInput.slice(0,-1)"
                    class="text-slate-500 hover:text-white transition p-1"
                >
                    <i class="fa-solid fa-backspace text-sm"></i>
                </button>
            </div>
        </div>

        {{-- 3x4 keypad - fills remaining --}}
        <div class="flex-1 px-3 pt-3 pb-2 grid grid-cols-3 gap-1.5 min-h-0">
            <template x-for="k in ['1','2','3','4','5','6','7','8','9','*','0','#']" :key="k">
                <button
                    type="button"
                    @click="dialNumberInput += k"
                    class="rounded-xl bg-slate-800/40 border border-slate-700/40 hover:bg-slate-700/70 hover:border-slate-600 hover:text-white active:scale-95 active:bg-indigo-600/30 transition flex items-center justify-center font-mono font-bold text-lg text-slate-200 select-none"
                >
                    <span x-text="k"></span>
                </button>
            </template>
        </div>

        {{-- Dial CTA --}}
        <div class="shrink-0 px-3 pb-3">
            <button
                type="button"
                @click="phoneDial()"
                :disabled="dialNumberInput.length < 3 || phoneStatus === 'ringing_outbound'"
                class="w-full py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 disabled:opacity-30 disabled:cursor-not-allowed disabled:from-slate-700 disabled:to-slate-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/30 transition active:scale-[0.98] flex items-center justify-center gap-2"
            >
                <i class="fa-solid fa-phone"></i>
                <span x-text="phoneStatus === 'ringing_outbound' ? 'Calling…' : 'Place Outbound Call'"></span>
            </button>
        </div>
    </div>
</template>
