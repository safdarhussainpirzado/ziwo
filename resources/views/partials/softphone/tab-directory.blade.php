{{--
    Premium Directory tab. Search + filter chips + scrollable contact list.
    Shown when phoneTab === 'phonebook'.
--}}
<template x-if="phoneTab === 'phonebook'">
    <div
        class="absolute inset-0 flex flex-col"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
    >

        {{-- Search + filters --}}
        <div class="shrink-0 px-3 pt-3 pb-2 space-y-2 border-b border-slate-800">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-[11px]"></i>
                <input
                    type="text"
                    x-model="phoneSearchQuery"
                    @input.debounce.300ms="phoneSearchContacts()"
                    placeholder="Search directory…"
                    class="w-full pl-8 pr-3 py-2 bg-[#0F172A]/80 border border-slate-700 rounded-lg text-xs text-white placeholder-slate-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition"
                >
            </div>
            <div class="flex gap-1.5 overflow-x-auto pb-0.5 text-[8px] font-bold">
                <button type="button" @click="phoneCategoryFilter = ''; phoneSearchContacts()" :class="phoneCategoryFilter === '' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/40' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-slate-200'" class="px-2.5 py-1 rounded-md transition uppercase tracking-wider shrink-0">All</button>
                <button type="button" @click="phoneCategoryFilter = 'beat'; phoneSearchContacts()" :class="phoneCategoryFilter === 'beat' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/40' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-slate-200'" class="px-2.5 py-1 rounded-md transition uppercase tracking-wider shrink-0">Beats</button>
                <button type="button" @click="phoneCategoryFilter = 'emergency'; phoneSearchContacts()" :class="phoneCategoryFilter === 'emergency' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/40' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-slate-200'" class="px-2.5 py-1 rounded-md transition uppercase tracking-wider shrink-0">Emergency</button>
                <button type="button" @click="phoneCategoryFilter = 'custom'; phoneSearchContacts()" :class="phoneCategoryFilter === 'custom' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/40' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-slate-200'" class="px-2.5 py-1 rounded-md transition uppercase tracking-wider shrink-0">Custom</button>
                <button type="button" @click="openAddContactModal()" class="ml-auto px-2 py-1 bg-emerald-600/20 hover:bg-emerald-600 text-emerald-400 hover:text-white rounded-md transition shrink-0" title="Add contact">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
        </div>

        {{-- Contact list --}}
        <div class="flex-1 overflow-y-auto px-3 py-2 space-y-1.5">
            <template x-for="c in filteredPhonebook" :key="c.id">
                <div class="flex items-center gap-2.5 p-2 bg-[#0F172A]/50 border border-slate-800 rounded-xl hover:border-slate-700 hover:bg-[#0F172A] transition">
                    <div class="w-8 h-8 shrink-0 rounded-full bg-gradient-to-br from-indigo-500/30 to-violet-500/30 border border-indigo-500/30 grid place-items-center text-[10px] font-bold text-indigo-200">
                        <span x-text="(c.name || '?').slice(0,2).toUpperCase()"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-bold text-slate-100 truncate" x-text="c.name"></span>
                            <span class="text-[7px] px-1 bg-slate-800 text-slate-400 rounded uppercase font-black tracking-tight" x-text="c.category"></span>
                        </div>
                        <div class="text-[10px] font-mono text-indigo-300 truncate" x-text="c.phone_number || c.phone"></div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button" @click="phoneTriggerQuickDial(c.phone_number || c.phone || '', c.name)" class="w-7 h-7 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white transition active:scale-90 grid place-items-center" title="Call">
                            <i class="fa-solid fa-phone text-[10px]"></i>
                        </button>
                        <button
                            x-show="c.category === 'custom'"
                            type="button"
                            @click="phoneDeleteContact(c.id)"
                            class="w-7 h-7 rounded-lg bg-slate-800/60 hover:bg-rose-950 text-slate-400 hover:text-rose-500 transition grid place-items-center"
                            title="Delete"
                        >
                            <i class="fa-solid fa-trash-can text-[10px]"></i>
                        </button>
                    </div>
                </div>
            </template>

            <div x-show="filteredPhonebook.length === 0" class="text-center py-12 text-slate-500 text-[10px]">
                <i class="fa-solid fa-address-book text-3xl mb-2 block opacity-40"></i>
                <p class="font-bold uppercase tracking-wider">No directory contacts match</p>
            </div>
        </div>

        {{-- Add-contact overlay --}}
        <div
            x-show="addContactOpen"
            x-transition.opacity
            class="absolute inset-0 bg-[#0B1220]/95 backdrop-blur-sm p-4 flex flex-col justify-center gap-3 z-20"
        >
            <h4 class="font-bold text-xs text-slate-200 text-center uppercase tracking-wider mb-1">Add Phonebook Contact</h4>
            <input type="text" x-model="contactForm.name" placeholder="Contact Name" class="w-full px-3 py-2 bg-[#0F172A]/80 border border-slate-700 rounded-lg text-xs text-white placeholder-slate-600 focus:border-indigo-500 outline-none">
            <input type="text" x-model="contactForm.phone_number" placeholder="03001234567" class="w-full px-3 py-2 bg-[#0F172A]/80 border border-slate-700 rounded-lg text-xs text-white placeholder-slate-600 focus:border-indigo-500 outline-none">
            <select x-model="contactForm.category" class="w-full px-3 py-2 bg-[#0F172A]/80 border border-slate-700 rounded-lg text-xs text-white outline-none">
                <option value="custom">Custom (Private)</option>
                <option value="emergency">Emergency (Public)</option>
                <option value="beat">Beat (Public)</option>
            </select>
            <div class="grid grid-cols-2 gap-2 pt-2">
                <button type="button" @click="phoneSaveContact()" class="py-2 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white text-xs font-bold rounded-lg hover:from-emerald-500 transition active:scale-95">Save</button>
                <button type="button" @click="addContactOpen = false" class="py-2 bg-slate-800 text-slate-300 text-xs font-bold rounded-lg hover:bg-slate-700 transition active:scale-95">Cancel</button>
            </div>
        </div>
    </div>
</template>
