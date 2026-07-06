<div x-show="showConfirmModal" class="fixed inset-0 z-[11000] flex items-center justify-center p-4" x-transition x-cloak>
    <div class="fixed inset-0 bg-white-900/30 backdrop-blur-sm" @click="showConfirmModal = false"></div>
    <div class="bg-white rounded-[2.5rem] shadow-[0_32px_64px_-12px_rgba(0,0,0,0.14)] w-full max-w-sm overflow-hidden border border-slate-100 flex flex-col relative z-10 p-8 text-center" x-transition.scale>
        <div class="w-16 h-16 mx-auto rounded-2xl mb-6 flex items-center justify-center shadow-xl rotate-3" :class="confirmConfig.isDanger ? 'bg-rose-500 shadow-rose-500/20' : 'bg-amber-500 shadow-amber-500/20'">
            <i class="fa-solid text-2xl text-white" :class="confirmConfig.icon || 'fa-triangle-exclamation'"></i>
        </div>
        <h3 class="text-2xl font-black text-navy-900 tracking-tighter uppercase italic" x-text="confirmConfig.title"></h3>
        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-2 mb-8 leading-relaxed px-4" x-html="confirmConfig.message"></p>
        
        <div class="flex gap-4">
            <button @click="showConfirmModal = false" class="flex-1 py-4 bg-md-gray shadow-md-gray/20 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg hover:-translate-y-1 active:scale-95 transition-all flex items-center justify-center gap-2">Cancel</button>
            <button @click="executeConfirmAction()" 
                :disabled="confirmLoading"
                :class="confirmConfig.isDanger ? 'bg-rose-600 shadow-rose-600/20' : 'bg-amber-600 shadow-amber-600/20'" 
                class="flex-[1.5] py-4 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg hover:-translate-y-1 active:scale-95 transition-all flex items-center justify-center gap-2">
                <template x-if="!confirmLoading">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-bolt-lightning"></i>
                        <span x-text="confirmConfig.isDanger ? 'Confirm' : 'Authorize'"></span>
                    </span>
                </template>
                <template x-if="confirmLoading">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                </template>
            </button>
        </div>
    </div>
</div>
