{{--
    Premium transfer overlay. Back-arrow header + sticky search + scrollable
    filtered directory list with hover "Transfer to" CTA.
    Shown when transferPanelOpen === true.
--}}
<div class="absolute inset-0 bg-[#0B1220] flex flex-col">

    {{-- Header --}}
    <div class="shrink-0 h-14 px-4 border-b border-slate-800 flex items-center gap-3 bg-[#0F172A]/60">
        <button type="button" @click="transferPanelOpen = false" class="w-8 h-8 rounded-lg bg-slate-800/60 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition grid place-items-center">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </button>
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-white">Transfer Call</div>
            <div class="text-[9px] text-slate-500 uppercase tracking-wider font-bold mt-0.5" x-text="(currentCall.caller_name || currentCall.caller_number || '?')"></div>
        </div>
    </div>

    {{-- Mode tabs --}}
    <div class="shrink-0 px-3 pt-3 pb-2 grid grid-cols-3 gap-1.5 border-b border-slate-800">
        <button type="button" @click="transferTab = 'manual'" :class="transferTab === 'manual' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/40' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-slate-200'" class="py-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider transition">Manual</button>
        <button type="button" @click="transferTab = 'teammates'" :class="transferTab === 'teammates' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/40' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-slate-200'" class="py-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider transition">Teammates</button>
        <button type="button" @click="transferTab = 'queues'" :class="transferTab === 'queues' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/40' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-slate-200'" class="py-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider transition">Queues</button>
    </div>

    {{-- Search bar --}}
    <div class="shrink-0 px-3 py-2">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-[11px]"></i>
            <input
                type="text"
                x-model="transferSearch"
                placeholder="Search…"
                class="w-full pl-8 pr-3 py-2 bg-[#0F172A]/80 border border-slate-700 rounded-lg text-xs text-white placeholder-slate-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition"
            >
        </div>
    </div>

    {{-- Recipient list --}}
    <div class="flex-1 overflow-y-auto px-3 pb-3 space-y-1.5">

        {{-- Manual number input --}}
        <template x-if="transferTab === 'manual'">
            <div class="space-y-2">
                <input
                    type="text"
                    x-model="transferNumber"
                    placeholder="+92 300 1234567"
                    class="w-full px-3 py-2.5 bg-[#0F172A]/80 border border-slate-700 rounded-lg text-sm text-white font-mono placeholder-slate-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none"
                >
                <button
                    type="button"
                    @click="phoneExecuteTransfer('blind')"
                    :disabled="transferNumber.length < 3"
                    class="w-full py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 disabled:opacity-30 disabled:cursor-not-allowed text-white text-xs font-bold rounded-lg shadow-lg shadow-indigo-500/30 transition active:scale-[0.98] flex items-center justify-center gap-2"
                >
                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    Blind Transfer
                </button>
            </div>
        </template>

        {{-- Teammates --}}
        <template x-if="transferTab === 'teammates'">
            <template x-for="t in filteredTeammates" :key="t.id">
                <div class="flex items-center gap-2.5 p-2 bg-[#0F172A]/50 border border-slate-800 rounded-xl hover:border-indigo-500/40 hover:bg-[#0F172A] transition group cursor-pointer" @click="phoneExecuteTransfer('blind', t.number || t.ext)">
                    <div class="w-8 h-8 shrink-0 rounded-full bg-gradient-to-br from-indigo-500/30 to-violet-500/30 border border-indigo-500/30 grid place-items-center text-[10px] font-bold text-indigo-200">
                        <span x-text="(t.name || '?').slice(0,2).toUpperCase()"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] font-bold text-slate-100 truncate" x-text="t.name"></div>
                        <div class="text-[10px] text-slate-500 font-mono truncate" x-text="t.ext || t.number"></div>
                    </div>
                    <i class="fa-solid fa-arrow-right text-indigo-400 opacity-0 group-hover:opacity-100 transition"></i>
                </div>
            </template>
        </template>

        {{-- Queues --}}
        <template x-if="transferTab === 'queues'">
            <template x-for="q in filteredQueues" :key="q.id">
                <div class="flex items-center gap-2.5 p-2 bg-[#0F172A]/50 border border-slate-800 rounded-xl hover:border-indigo-500/40 hover:bg-[#0F172A] transition group cursor-pointer" @click="phoneExecuteTransfer('blind', q.number)">
                    <div class="w-8 h-8 shrink-0 rounded-full bg-gradient-to-br from-emerald-500/30 to-teal-500/30 border border-emerald-500/30 grid place-items-center text-[10px] font-bold text-emerald-200">
                        <i class="fa-solid fa-list-ul"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] font-bold text-slate-100 truncate" x-text="q.name"></div>
                        <div class="text-[9px] text-slate-500 font-mono truncate" x-text="q.number"></div>
                    </div>
                    <i class="fa-solid fa-arrow-right text-emerald-400 opacity-0 group-hover:opacity-100 transition"></i>
                </div>
            </template>
        </template>
    </div>
</div>
