{{--
    Premium tab bar. AT THE TOP of the softphone idle area (right under the header).
    3-column grid with icon + label. Active tab has accent left border + bg tint.
--}}
<nav
    x-show="phoneAuthenticated"
    class="shrink-0 h-12 px-2 pt-2 border-b border-slate-800 bg-[#0F172A]/60 backdrop-blur-sm"
>
    <div class="grid grid-cols-3 gap-1 relative">

        {{-- Animated sliding underline (positioned absolutely) --}}
        <div
            class="absolute bottom-0 h-0.5 bg-gradient-to-r from-indigo-500 to-violet-500 rounded-full transition-all duration-300 ease-out"
            :style="`width: 33%; left: ${phoneTab === 'dialer' ? 0 : phoneTab === 'phonebook' ? 33.333 : 66.666}%;`"
        ></div>

        <button
            type="button"
            @click="phoneTab = 'dialer'"
            class="flex flex-col items-center justify-center py-1.5 rounded-lg transition-all duration-200"
            :class="phoneTab === 'dialer' ? 'bg-indigo-500/10 text-white' : 'text-slate-500 hover:text-slate-300 hover:bg-white/[0.03]'"
        >
            <i class="fa-solid fa-keyboard text-sm"></i>
            <span class="text-[9px] font-bold uppercase tracking-wider mt-0.5">Dialer</span>
        </button>

        <button
            type="button"
            @click="phoneTab = 'phonebook'; $nextTick(() => phoneSearchContacts())"
            class="flex flex-col items-center justify-center py-1.5 rounded-lg transition-all duration-200"
            :class="phoneTab === 'phonebook' ? 'bg-indigo-500/10 text-white' : 'text-slate-500 hover:text-slate-300 hover:bg-white/[0.03]'"
        >
            <i class="fa-solid fa-address-book text-sm"></i>
            <span class="text-[9px] font-bold uppercase tracking-wider mt-0.5">Directory</span>
        </button>

        <button
            type="button"
            @click="phoneTab = 'history'"
            class="flex flex-col items-center justify-center py-1.5 rounded-lg transition-all duration-200 relative"
            :class="phoneTab === 'history' ? 'bg-indigo-500/10 text-white' : 'text-slate-500 hover:text-slate-300 hover:bg-white/[0.03]'"
        >
            <i class="fa-solid fa-clock-rotate-left text-sm"></i>
            <span class="text-[9px] font-bold uppercase tracking-wider mt-0.5">Recent</span>

            {{-- Unread missed-call badge --}}
            <span
                x-show="recentCallLogs.some(h => ['missed','no-answer','cancel'].includes(h.status))"
                x-cloak
                class="absolute top-0.5 right-2 inline-flex items-center justify-center min-w-[14px] h-3.5 px-1 text-[8px] font-black rounded-full bg-rose-500 text-white shadow-lg shadow-rose-500/40"
                x-text="recentCallLogs.filter(h => ['missed','no-answer','cancel'].includes(h.status)).length"
            ></span>
        </button>
    </div>
</nav>
