<section>
    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div class="space-y-6">
            <div class="group">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2 transition-colors group-focus-within:text-rose-600">Current Authorization Key</label>
                <div class="relative">
                    <input type="password" name="current_password" required
                        class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-rose-500 outline-none font-bold text-navy-900 transition-all shadow-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="group">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2 transition-colors group-focus-within:text-rose-600">New Secure Hex</label>
                    <input type="password" name="password" required
                        class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-rose-500 outline-none font-bold text-navy-900 transition-all shadow-sm">
                </div>

                <div class="group">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2 transition-colors group-focus-within:text-rose-600">Confirm Sequence</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-rose-500 outline-none font-bold text-navy-900 transition-all shadow-sm">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-6 pt-4">
            <button type="submit" class="px-10 py-4 bg-rose-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 hover:-translate-y-1 transition-all active:scale-95">
                Rotate Credentials
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-xs font-bold text-emerald-600 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved italic"></i> Security Rotation Complete
                </p>
            @endif
        </div>
    </form>
</section>
