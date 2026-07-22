{{--
    Premium login screen. Centered card with ZIWO branding + form + mic banner.
    Shows when `phoneAuthenticated === false`.
--}}
<div class="h-full flex flex-col items-center justify-center px-6 py-8 bg-gradient-to-b from-slate-900 via-[#0F172A] to-indigo-950/40">

    {{-- Brand mark with subtle glow --}}
    <div class="relative mb-5">
        <div class="absolute inset-0 bg-indigo-500/30 blur-2xl rounded-3xl"></div>
        <div class="relative w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 grid place-items-center shadow-2xl shadow-indigo-500/40">
            <i class="fa-solid fa-headset text-white text-2xl"></i>
        </div>
    </div>

    <h1 class="text-lg font-bold text-white tracking-tight">ZIWO Softphone</h1>
    <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-slate-500 mt-1">Agent Portal · Nayatel</p>

    {{-- Form --}}
    <form @submit.prevent="phoneAuthenticate()" class="w-full mt-7 space-y-2.5">
        <div>
            <label class="text-[9px] uppercase tracking-[0.12em] text-slate-500 font-bold mb-1 block">Username / Email</label>
            <input
                type="text"
                x-model="phoneAuthForm.username"
                placeholder="agent_username"
                autocomplete="username"
                class="w-full px-3 py-2.5 bg-[#0F172A] border border-slate-700 rounded-lg text-xs text-white placeholder-slate-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40 focus:bg-[#0F172A]/60 outline-none transition cursor-text"
            >
        </div>
        <div>
            <label class="text-[9px] uppercase tracking-[0.12em] text-slate-500 font-bold mb-1 block">Password</label>
            <div class="relative">
                <input
                    :type="showPhonePassword ? 'text' : 'password'"
                    x-model="phoneAuthForm.password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    class="w-full pl-3 pr-9 py-2.5 bg-[#0F172A] border border-slate-700 rounded-lg text-xs text-white placeholder-slate-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40 focus:bg-[#0F172A]/60 outline-none transition cursor-text"
                >
                <button
                    type="button"
                    @click="showPhonePassword = !showPhonePassword"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition p-1"
                >
                    <i class="fa-solid text-[10px]" :class="showPhonePassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
        </div>

        <button
            type="submit"
            :disabled="phoneSubmitting"
            class="w-full mt-3 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-lg shadow-lg shadow-indigo-500/30 transition cursor-pointer active:scale-[0.98] flex items-center justify-center gap-2"
        >
            <i class="fa-solid fa-arrow-right-to-bracket text-[10px]" x-show="!phoneSubmitting"></i>
            <i class="fa-solid fa-spinner fa-spin text-[10px]" x-show="phoneSubmitting" x-cloak></i>
            <span x-text="phoneSubmitting ? 'Connecting…' : 'Sign In'"></span>
        </button>
    </form>

    {{-- Mic permission banners --}}
    <template x-if="micAllowed === false">
        <div class="mt-5 px-3 py-2.5 bg-amber-500/10 border border-amber-500/30 rounded-lg flex items-center gap-2 text-amber-400 text-[10px]">
            <i class="fa-solid fa-microphone-slash"></i>
            <span class="flex-1 leading-tight">Microphone access blocked.<br>Calls will fail without it.</span>
            <button type="button" @click="checkOrRequestMicrophone()" class="px-2 py-1 bg-amber-500 hover:bg-amber-400 text-white rounded font-bold text-[9px] transition shrink-0">Allow</button>
        </div>
    </template>
    <template x-if="micAllowed === null">
        <div class="mt-5 px-3 py-2.5 bg-indigo-500/10 border border-indigo-500/30 rounded-lg flex items-center gap-2 text-indigo-300 text-[10px]">
            <i class="fa-solid fa-circle-info"></i>
            <span class="flex-1 leading-tight">Authorize your microphone<br>to enable voice calls.</span>
            <button type="button" @click="checkOrRequestMicrophone()" class="px-2 py-1 bg-indigo-500 hover:bg-indigo-400 text-white rounded font-bold text-[9px] transition shrink-0">Enable</button>
        </div>
    </template>

    {{-- Auth error --}}
    <div
        x-show="phoneStatusError"
        x-transition.opacity
        class="mt-3 px-3 py-2 bg-rose-500/10 border border-rose-500/30 rounded-lg text-rose-400 text-[10px] leading-tight"
        x-text="phoneStatusError"
    ></div>
</div>
