{{--
    Premium Add Call overlay. Back-arrow header + dialpad option + scrollable
    filtered teammates & phonebook list with hover "Add to call" CTA.
    Shown when addOrCallOpen === true.
--}}
<div class="absolute inset-0 bg-[#0B1220] flex flex-col z-30">

    {{-- Header --}}
    <div class="shrink-0 h-14 px-4 border-b border-slate-800 flex items-center gap-3 bg-[#0F172A]/60">
        <button type="button" @click="closeAddOrCallPanel()" class="w-8 h-8 rounded-lg bg-slate-800/60 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition grid place-items-center">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </button>
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-white">Add to Call</div>
            <div class="text-[9px] text-slate-500 uppercase tracking-wider font-bold mt-0.5">Invite participant</div>
        </div>
    </div>

    {{-- Mode tabs --}}
    <div class="shrink-0 px-3 pt-3 pb-2 grid grid-cols-3 gap-1.5 border-b border-slate-800">
        <button type="button" @click="addOrCallTab = 'phonebook'" :class="addOrCallTab === 'phonebook' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/40' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-slate-200'" class="py-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider transition">Phonebook</button>
        <button type="button" @click="addOrCallTab = 'teammates'" :class="addOrCallTab === 'teammates' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/40' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-slate-200'" class="py-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider transition">Teammates</button>
        <button type="button" @click="addOrCallTab = 'dialpad'" :class="addOrCallTab === 'dialpad' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/40' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-slate-200'" class="py-1.5 rounded-md text-[10px] font-bold uppercase tracking-wider transition">Dialpad</button>
    </div>

    {{-- Search bar (only for lists) --}}
    <div class="shrink-0 px-3 py-2" x-show="addOrCallTab !== 'dialpad'">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-[11px]"></i>
            <input
                type="text"
                x-model="addOrCallSearch"
                placeholder="Search…"
                class="w-full pl-8 pr-3 py-2 bg-[#0F172A]/80 border border-slate-700 rounded-lg text-xs text-white placeholder-slate-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition"
            >
        </div>
    </div>

    {{-- Recipient list --}}
    <div class="flex-1 overflow-y-auto px-3 pb-3 space-y-1.5">

        {{-- Phonebook Contacts --}}
        <template x-if="addOrCallTab === 'phonebook'">
            <div class="space-y-1.5">
                <template x-for="c in (phonebookContacts || []).filter(c => !addOrCallSearch || (c.name || '').toLowerCase().includes(addOrCallSearch.toLowerCase()) || (c.phone_number || c.phone || '').includes(addOrCallSearch))" :key="c.id">
                    <div class="flex items-center gap-2.5 p-2 bg-[#0F172A]/50 border border-slate-800 rounded-xl hover:border-indigo-500/40 hover:bg-[#0F172A] transition group cursor-pointer" @click="phoneAddConferenceParticipant(c.phone_number || c.phone)">
                        <div class="w-8 h-8 shrink-0 rounded-full bg-gradient-to-br from-indigo-500/30 to-violet-500/30 border border-indigo-500/30 grid place-items-center text-[10px] font-bold text-indigo-200">
                            <span x-text="(c.name || '?').slice(0,2).toUpperCase()"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[11px] font-bold text-slate-100 truncate" x-text="c.name"></div>
                            <div class="text-[10px] text-indigo-300 font-mono truncate" x-text="c.phone_number || c.phone"></div>
                        </div>
                        <i class="fa-solid fa-user-plus text-indigo-400 opacity-0 group-hover:opacity-100 transition"></i>
                    </div>
                </template>
            </div>
        </template>

        {{-- Teammates --}}
        <template x-if="addOrCallTab === 'teammates'">
            <div class="space-y-1.5">
                <template x-for="t in filteredTeammates" :key="t.id">
                    <div class="flex items-center gap-2.5 p-2 bg-[#0F172A]/50 border border-slate-800 rounded-xl hover:border-indigo-500/40 hover:bg-[#0F172A] transition group cursor-pointer" @click="phoneAddConferenceParticipant(t.number || t.ext)">
                        <div class="w-8 h-8 shrink-0 rounded-full bg-gradient-to-br from-indigo-500/30 to-violet-500/30 border border-indigo-500/30 grid place-items-center text-[10px] font-bold text-indigo-200">
                            <span x-text="(t.name || '?').slice(0,2).toUpperCase()"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[11px] font-bold text-slate-100 truncate" x-text="t.name"></div>
                            <div class="text-[10px] text-slate-500 font-mono truncate" x-text="t.ext || t.number"></div>
                        </div>
                        <i class="fa-solid fa-user-plus text-indigo-400 opacity-0 group-hover:opacity-100 transition"></i>
                    </div>
                </template>
            </div>
        </template>

        {{-- Custom Dialpad --}}
        <template x-if="addOrCallTab === 'dialpad'">
            <div class="flex flex-col h-full justify-between gap-3">
                <div class="space-y-2">
                    <input
                        type="text"
                        x-model="addOrCallInput"
                        placeholder="Enter number…"
                        class="w-full px-3 py-2 bg-[#0F172A]/80 border border-slate-700 rounded-lg text-sm text-white font-mono text-center placeholder-slate-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none"
                    >
                    <button
                        type="button"
                        @click="phoneAddConferenceParticipant(addOrCallInput)"
                        :disabled="addOrCallInput.length < 3"
                        class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 text-white text-xs font-bold rounded-lg shadow-lg transition active:scale-[0.98]"
                    >
                        Call & Add
                    </button>
                </div>

                {{-- Dialpad Keys --}}
                <div class="grid grid-cols-3 gap-2 px-6">
                    <template x-for="k in ['1','2','3','4','5','6','7','8','9','*','0','#']" :key="k">
                        <button
                            type="button"
                            @click="addOrCallInput += k"
                            class="w-10 h-10 rounded-full bg-slate-800/40 hover:bg-slate-700/60 border border-slate-800 text-slate-200 text-sm font-bold transition active:scale-90 mx-auto grid place-items-center"
                            x-text="k"
                        ></button>
                    </template>
                    <button
                        type="button"
                        @click="addOrCallInput = addOrCallInput.slice(0, -1)"
                        class="w-10 h-10 rounded-full bg-slate-800/20 hover:bg-rose-950/30 text-rose-400 hover:text-rose-300 transition active:scale-90 mx-auto grid place-items-center col-span-3 mt-1"
                        title="Backspace"
                    >
                        <i class="fa-solid fa-backspace"></i>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>
