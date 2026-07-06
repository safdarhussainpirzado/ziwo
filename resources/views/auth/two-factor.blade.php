<x-guest-layout>
    <div class="min-h-screen bg-[#F8FAFC] flex items-center justify-center p-6 relative overflow-hidden font-inter">
        <!-- Abstract Background Blobs -->
        <div class="absolute -right-20 -top-20 w-96 h-96 bg-blue-600 rounded-full blur-[120px] opacity-[0.08] animate-pulse"></div>
        <div class="absolute -left-20 -bottom-20 w-96 h-96 bg-navy-900 rounded-full blur-[120px] opacity-[0.05]"></div>
        
        <div class="w-full max-w-md relative z-10">
            <!-- Security Badge Header -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-[2rem] bg-navy-900 shadow-2xl mb-6 border border-white/10 group animate-float">
                    <i class="fa-solid fa-shield-halved text-3xl text-blue-400 group-hover:scale-110 transition-transform"></i>
                </div>
                <h1 class="text-3xl font-black text-navy-900 tracking-tight mb-2 uppercase italic scale-y-110">Vault Access</h1>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.4em]">Multi-Factor Authentication Required</p>
            </div>

            <!-- Verification Card -->
            <div class="bg-white rounded-[3rem] shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] border border-slate-100 p-12 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-slate-50/50 to-white pointer-events-none"></div>
                
                <form method="POST" action="{{ route('2fa.verify') }}" class="relative z-10 space-y-8" x-data="{ code: '' }">
                    @csrf
                    
                    <div class="text-center">
                        <p class="text-xs font-bold text-slate-500 leading-relaxed max-w-[240px] mx-auto">
                            Please enter the 6-digit synchronization code from your authenticator device.
                        </p>
                    </div>

                    <!-- Code Input Matrix -->
                    <div class="group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2 text-center">Protocol Synchronizer</label>
                        <div class="relative">
                            <input type="text" name="code" maxlength="6" autofocus placeholder="000 000"
                                x-model="code"
                                class="w-full text-center py-6 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none text-4xl font-black text-navy-900 tracking-[0.2em] transition-all shadow-inner placeholder:text-slate-200">
                        </div>
                        <x-input-error :messages="$errors->get('code')" class="mt-4 text-center" />
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full py-5 bg-navy-900 text-white rounded-2xl font-black text-xs uppercase tracking-[0.3em] shadow-[0_20px_40px_rgba(15,23,42,0.3)] hover:shadow-[0_25px_50px_rgba(15,23,42,0.4)] hover:-translate-y-1 active:scale-95 transition-all flex items-center justify-center gap-3">
                            <i class="fa-solid fa-satellite-dish animate-pulse"></i>
                            <span>Authorize Session</span>
                        </button>
                    </div>

                    <div class="text-center pt-6 border-t border-slate-50">
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-rose-600 transition-colors">
                            Invalidate Credentials
                        </a>
                    </div>
                </form>
                
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>

            <!-- Footer Meta -->
            <div class="mt-12 flex items-center justify-center gap-4 opacity-50">
                <div class="h-px w-8 bg-slate-300"></div>
                <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Secure Terminal No. {{ date('Y-H') }}</div>
                <div class="h-px w-8 bg-slate-300"></div>
            </div>
        </div>
    </div>

    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</x-guest-layout>
