{{--
    Premium softphone header bar. 56px tall, fixed at top of panel.
    Shows: presence status dot + agent name + presence selector + log out + collapse.
--}}
<header class="shrink-0 h-14 px-3 border-b border-slate-800/80 bg-[#0F172A]/80 backdrop-blur-md flex items-center justify-between gap-2">

    {{-- LEFT: avatar + agent label --}}
    <div class="flex items-center gap-2.5 min-w-0">
        {{-- Avatar circle: gradient based on presence (grey when offline/not authed) --}}
        <div class="relative shrink-0">
            <div class="w-9 h-9 rounded-full grid place-items-center text-[11px] font-black text-white shadow-lg"
                 :class="{
                     'bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-emerald-500/30': phoneAuthenticated && phoneAgentStatus === 'available',
                     'bg-gradient-to-br from-amber-500 to-amber-600 shadow-amber-500/30':  phoneAuthenticated && phoneAgentStatus === 'break',
                     'bg-gradient-to-br from-blue-500 to-blue-600 shadow-blue-500/30':     phoneAuthenticated && phoneAgentStatus === 'meeting',
                     'bg-gradient-to-br from-violet-500 to-violet-600 shadow-violet-500/30': phoneAuthenticated && phoneAgentStatus === 'outgoing',
                     'bg-gradient-to-br from-slate-600 to-slate-700': !phoneAuthenticated || phoneAgentStatus === 'offline'
                 }">
                <span x-text="(ziwoUsername || 'JD').slice(0,2).toUpperCase()"></span>
            </div>
            {{-- Active connection dot — only shown when authenticated and non-offline --}}
            <span
                x-show="phoneAuthenticated && phoneAgentStatus !== 'offline'"
                class="absolute -bottom-0.5 -right-0.5 block w-3 h-3 rounded-full border-2 border-[#0B1220]"
                :class="{
                    'bg-emerald-500 animate-pulse': phoneAgentStatus === 'available',
                    'bg-amber-500':                   phoneAgentStatus === 'break',
                    'bg-blue-500':                     phoneAgentStatus === 'meeting',
                    'bg-violet-500':                   phoneAgentStatus === 'outgoing'
                }"
            ></span>
        </div>

        {{-- Agent name + presence label --}}
        <div class="min-w-0">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-100 truncate leading-tight">
                <span x-text="phoneAuthenticated ? (ziwoUsername || 'Agent').split('@')[0] : 'Sign In'"></span>
            </div>
            <div class="text-[9px] font-semibold uppercase tracking-wider leading-tight mt-0.5"
                 :class="{
                     'text-emerald-400': phoneAuthenticated && phoneAgentStatus === 'available',
                     'text-amber-400':   phoneAuthenticated && phoneAgentStatus === 'break',
                     'text-blue-400':     phoneAuthenticated && phoneAgentStatus === 'meeting',
                     'text-violet-400':   phoneAuthenticated && phoneAgentStatus === 'outgoing',
                     'text-slate-500':    !phoneAuthenticated || phoneAgentStatus === 'offline'
                 }">
                <span x-text="phoneAuthenticated ? ({
                    'available': '● Available',
                    'break':     '☕ On Break',
                    'meeting':   '📅 In Meeting',
                    'outgoing':  '📞 Outgoing',
                    'offline':   '○ Offline'
                }[phoneAgentStatus] || '○ ' + phoneAgentStatus) : '○ Offline'"></span>
            </div>
        </div>
    </div>

    {{-- RIGHT: presence dropdown + log out --}}
    <div class="flex items-center gap-1 shrink-0">
        <select
            x-model="phoneAgentStatus"
            @change="phoneUpdateAgentStatus($event.target.value)"
            :disabled="phoneUpdatingStatus"
            class="appearance-none bg-[#1E293B] border border-slate-700 rounded-lg pl-2 pr-6 py-1 text-[10px] font-bold uppercase tracking-wider cursor-pointer focus:outline-none focus:border-indigo-500 transition"
            :class="{
                'text-emerald-400 border-emerald-500/40': phoneAgentStatus === 'available',
                'text-amber-400 border-amber-500/40':   phoneAgentStatus === 'break',
                'text-blue-400 border-blue-500/40':     phoneAgentStatus === 'meeting',
                'text-violet-400 border-violet-500/40': phoneAgentStatus === 'outgoing'
            }"
            title="Set your availability"
        >
            <option value="available">Available</option>
            <option value="meeting">Meeting</option>
            <option value="break">On Break</option>
            <option value="outgoing">Outgoing</option>
        </select>

        <button
            type="button"
            @click="phoneDisconnect()"
            class="w-7 h-7 rounded-lg bg-[#1E293B] hover:bg-rose-600 text-slate-400 hover:text-white border border-slate-700 hover:border-rose-500 transition flex items-center justify-center"
            title="Log out"
        >
            <i class="fa-solid fa-power-off text-[10px]"></i>
        </button>
    </div>
</header>
