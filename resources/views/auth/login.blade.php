<x-guest-layout>
  <style>
    /* bento elevation */
    .bento-card {
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
    }
    
    /* micro-interactions with ripple effect */
    .btn-ripple {
      position: relative;
      overflow: hidden;
      transform: translate3d(0,0,0);
    }
    .btn-ripple:after {
      content: "";
      display: block;
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      pointer-events: none;
      background-image: radial-gradient(circle, #fff 10%, transparent 10.01%);
      background-repeat: no-repeat;
      background-position: 50%;
      transform: scale(10,10);
      opacity: 0;
      transition: transform .3s, opacity .5s;
    }
    .btn-ripple:active:after {
      transform: scale(0,0);
      opacity: 0.3;
      transition: 0s;
    }
    
    /* page entrance animation */
    .fade-slide-in {
      animation: fadeSlide 0.6s cubic-bezier(0.2, 0.9, 0.4, 1) forwards;
    }
    @keyframes fadeSlide {
      0% { opacity: 0; transform: translateY(16px); }
      100% { opacity: 1; transform: translateY(0); }
    }
    
    /* custom checkbox accent */
    .checkbox-custom {
      accent-color: #2563eb;
    }
    
    /* glow effects for glass panels */
    .glow-shadow {
      box-shadow: 0 20px 40px -12px rgba(37, 99, 235, 0.15), 0 4px 18px -6px rgba(0, 0, 0, 0.05);
    }
    .glow-shadow-lg {
      box-shadow: 0 25px 50px -12px rgba(37, 99, 235, 0.25);
    }
    
    /* floating label enhancement */
    .input-floating-wrapper input:focus::placeholder {
      opacity: 0.5;
    }
  </style>


  <!-- MAIN BENTO GRID CONTAINER -->
  <div class="w-full max-w-7xl mx-auto">
    <!-- Session status area -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <div class="flex flex-col md:flex-row gap-6 lg:gap-8 items-stretch">

      <!-- LEFT BRANDING PANEL -->
      <div class="w-full md:w-[40%] relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-indigo-600 to-indigo-800 glow-shadow-lg p-8 flex flex-col justify-between bento-card border border-white/30 fade-slide-in" style="animation-delay: 0s;">
        <!-- abstract animated shapes -->
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-blue-400/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-10 left-0 w-40 h-40 bg-indigo-300/20 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 right-10 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
        
        <div class="relative z-10">
          <!-- NHMP Logo / emblem -->
          <div class="flex items-center gap-3 mb-8">
            <div class="w-14 h-14 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-xl border border-white/40">
              <i class="fa-solid fa-shield-halved text-white text-2xl"></i>
            </div>
            <span class="text-white font-black tracking-tight text-xl">NHMP 130</span>
          </div>
          <h1 class="text-4xl md:text-5xl font-extrabold text-white leading-tight tracking-tight drop-shadow-lg">
            Carriageway<br>Emergency CRM
          </h1>
          <p class="text-indigo-100 text-lg mt-4 font-medium max-w-xs">
            Secure • Rapid • Intelligent<br>Motorway response platform.
          </p>
        </div>

        <div class="relative z-10 mt-10">
          <div class="flex items-center gap-3 text-white/90 text-sm font-medium">
            <div class="w-5 h-5 rounded-full bg-emerald-400/30 flex items-center justify-center">
              <i class="fa-solid fa-check text-emerald-200 text-xs"></i>
            </div>
            <span>End‑to‑end encryption</span>
          </div>
          <div class="flex items-center gap-3 text-white/90 text-sm font-medium mt-3">
            <div class="w-5 h-5 rounded-full bg-emerald-400/30 flex items-center justify-center">
              <i class="fa-solid fa-check text-emerald-200 text-xs"></i>
            </div>
            <span>Real‑time incident sync</span>
          </div>
          <div class="flex items-center gap-3 text-white/90 text-sm font-medium mt-3">
            <div class="w-5 h-5 rounded-full bg-emerald-400/30 flex items-center justify-center">
              <i class="fa-solid fa-check text-emerald-200 text-xs"></i>
            </div>
            <span>24/7 emergency support</span>
          </div>
          <!-- subtle animated line -->
          <div class="mt-8 h-px w-20 bg-gradient-to-r from-white/50 to-transparent"></div>
          <p class="text-white/70 text-xs mt-4 uppercase tracking-widest font-bold">NHMP · since 2025</p>
        </div>
      </div>

      <!-- RIGHT LOGIN CARD -->
      <div class="w-full md:w-[60%] bg-white/75 backdrop-blur-xl rounded-3xl glow-shadow border border-white/60 p-6 md:p-8 lg:p-10 bento-card fade-slide-in" style="animation-delay: 0.1s;">
        
        <!-- Session status area -->
        <div class="mb-6 border-l-4 border-indigo-500 pl-4">
          <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">Secure Access</h2>
          <p class="text-slate-500 text-sm font-medium mt-1">Authenticate using your assigned credentials</p>
        </div>

        <!-- LOGIN FORM -->
        <form id="loginForm" class="space-y-6" method="POST" action="{{ route('login') }}">
          @csrf

          <!-- USERNAME FIELD -->
          <div class="input-floating-wrapper">
            <label class="block text-xs font-black tracking-widest uppercase text-slate-400 mb-2">System Login ID</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                <i class="fa-regular fa-user text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
              </div>
              <input id="username" 
                     name="username" 
                     type="text" 
                     value="{{ old('username') }}"
                     required 
                     autofocus 
                     autocomplete="username" 
                     placeholder="nhmp_admin"
                     class="block w-full pl-11 pr-4 py-3.5 rounded-xl border border-slate-200 bg-white/80 backdrop-blur-sm shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 text-slate-900 font-medium placeholder:text-slate-400 transition-all hover:bg-white outline-none">
            </div>
            <x-input-error :messages="$errors->get('username')" class="mt-2 text-rose-500 text-sm font-bold flex items-center gap-1" />
          </div>

          <!-- PASSWORD FIELD -->
          <div class="input-floating-wrapper">
            <label class="block text-xs font-black tracking-widest uppercase text-slate-400 mb-2">Password</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                <i class="fa-solid fa-lock text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
              </div>
              <input id="password" 
                     name="password" 
                     type="password" 
                     required 
                     autocomplete="current-password" 
                     placeholder="••••••••"
                     class="block w-full pl-11 pr-12 py-3.5 rounded-xl border border-slate-200 bg-white/80 backdrop-blur-sm shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 text-slate-900 font-medium tracking-wider placeholder:tracking-normal transition-all hover:bg-white outline-none">
              <!-- Show/Hide toggle button -->
              <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors z-10" aria-label="Toggle password visibility">
                <i class="fa-regular fa-eye-slash text-lg"></i>
              </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-500 text-sm font-bold flex items-center gap-1" />
          </div>

          <!-- Remember me + Forgot password -->
          <div class="flex items-center justify-between mt-6">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group select-none">
              <input id="remember_me" type="checkbox" class="checkbox-custom rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500 transition-colors w-4 h-4" name="remember">
              <span class="ms-2 text-sm font-bold text-slate-600 group-hover:text-slate-900 transition-colors">Remember Session</span>
            </label>

            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}" class="text-sm font-bold text-blue-600 hover:text-indigo-600 transition-colors">
                Forgot credentials?
              </a>
            @endif
          </div>

          <!-- Primary CTA (Login) -->
          <div class="pt-4">
            <button type="submit" id="loginButton" class="btn-ripple w-full py-4 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black tracking-widest uppercase shadow-xl shadow-blue-500/30 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5 active:scale-[0.98] flex items-center justify-center gap-2 text-base">
              <span id="btnText">Authenticate</span>
              <i class="fa-solid fa-arrow-right"></i>
              <!-- loading spinner -->
              <span id="loadingSpinner" class="hidden"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
            </button>
          </div>
        </form>
        
        <!-- subtle footer -->
        <p class="text-[11px] text-slate-400 text-center mt-8 uppercase tracking-wider font-bold">NHMP 130 • Carriageway Emergency</p>
      </div>
    </div>
  </div>

  <script>
    (function() {
      "use strict";

      // ----- SHOW/HIDE PASSWORD TOGGLE -----
      const passwordInput = document.getElementById('password');
      const toggleBtn = document.getElementById('togglePassword');
      const toggleIcon = toggleBtn.querySelector('i');
      
      toggleBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        if (type === 'text') {
          toggleIcon.classList.remove('fa-eye-slash');
          toggleIcon.classList.add('fa-eye');
        } else {
          toggleIcon.classList.remove('fa-eye');
          toggleIcon.classList.add('fa-eye-slash');
        }
      });

      // ----- LOADING STATE ON SUBMIT -----
      const form = document.getElementById('loginForm');
      const loginBtn = document.getElementById('loginButton');
      const btnText = document.getElementById('btnText');
      const spinner = document.getElementById('loadingSpinner');

      form.addEventListener('submit', () => {
        btnText.classList.add('hidden');
        spinner.classList.remove('hidden');
        loginBtn.disabled = true;
      });

      // Clear any cross-tab logout signals on the login page
      localStorage.removeItem('nhmp_session_logout');
    })();
  </script>
</x-guest-layout>