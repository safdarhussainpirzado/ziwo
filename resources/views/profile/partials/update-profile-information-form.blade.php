<section>
    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div class="space-y-6">
            <div class="group">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2 transition-colors group-focus-within:text-blue-600">Full Operational Name</label>
                <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" required
                    class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-navy-900 transition-all shadow-sm">
            </div>

            <div class="group">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2 transition-colors group-focus-within:text-blue-600">System Username / UID</label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                    class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-mono font-bold text-navy-900 transition-all shadow-sm">
            </div>

            <div class="group">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2 transition-colors group-focus-within:text-blue-600">Electronic Mail</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-navy-900 transition-all shadow-sm">
            </div>
        </div>

        <div class="flex items-center gap-6 pt-4">
            <button type="submit" class="px-10 py-4 bg-blue-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 hover:-translate-y-1 transition-all active:scale-95">
                Commit Changes
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-xs font-bold text-emerald-600 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-check-double italic"></i> Synchronized
                </p>
            @endif
        </div>
    </form>
</section>
