<section>
    <div class="space-y-6">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest leading-relaxed">
            Authorized personnel only. Terminating an operational node is irreversible and will purge all session telemetry and access tokens from the secure ledger.
        </p>

        <button 
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="px-10 py-4 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-lg shadow-slate-900/20 hover:shadow-slate-900/40 hover:-translate-y-1 transition-all active:scale-95">
            INITIATE NODE PURGE
        </button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-12 space-y-8">
            @csrf
            @method('delete')

            <div class="flex items-center gap-4 mb-2">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100 italic">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-navy-900 tracking-tight uppercase italic scale-y-110">Critical Deletion</h2>
                    <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mt-1">Operational Lockdown Protocol</p>
                </div>
            </div>

            <p class="text-sm font-bold text-slate-500 leading-relaxed">
                You are about to permanently decouple this node from the NHMP 130 Command Network. To proceed with the destruction of this identity, please enter your security sequence below.
            </p>

            <div class="group">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2 transition-colors group-focus-within:text-rose-600">Verification Key</label>
                <div class="relative">
                    <input type="password" name="password" placeholder="Admin Key Required"
                        class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-rose-500 outline-none font-bold text-navy-900 transition-all shadow-sm">
                </div>
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-[10px] font-black uppercase tracking-widest text-rose-600" />
            </div>

            <div class="flex justify-end gap-4 pt-4">
                <button type="button" x-on:click="$dispatch('close')" class="px-8 py-4 bg-slate-100 text-slate-500 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-200 transition-all">
                    Abort
                </button>

                <button type="submit" class="px-10 py-4 bg-rose-600 text-white rounded-xl font-black text-[10px] uppercase tracking-[0.2em] shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-skull-crossbones animate-pulse"></i> Terminate Node
                </button>
            </div>
        </form>
    </x-modal>
</section>
