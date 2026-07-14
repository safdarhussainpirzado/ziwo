<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZIWO Admin Login - NHMP 130</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f0c29 0%, #1a1a4e 30%, #302b63 60%, #24243e 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 2rem 1rem;
            position: relative; overflow: hidden;
        }

        /* Animated BG blobs */
        .blob {
            position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.3; pointer-events: none;
            animation: blobFloat 12s ease-in-out infinite;
        }
        .blob-1 { width: 600px; height: 600px; background: radial-gradient(circle, #7c3aed, transparent); top: -200px; left: -150px; animation-delay: 0s; }
        .blob-2 { width: 500px; height: 500px; background: radial-gradient(circle, #4f46e5, transparent); bottom: -150px; right: -100px; animation-delay: -5s; }
        .blob-3 { width: 300px; height: 300px; background: radial-gradient(circle, #06b6d4, transparent); top: 40%; left: 50%; animation-delay: -8s; }
        @keyframes blobFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
        }

        /* Card */
        .login-card {
            width: 100%; max-width: 440px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 1.5rem;
            padding: 2.5rem;
            box-shadow: 0 32px 80px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.08);
            position: relative; z-index: 10;
        }

        /* Logo area */
        .logo-ring {
            width: 72px; height: 72px;
            border-radius: 20px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(99,102,241,0.5);
            font-size: 1.75rem; color: white;
        }

        .login-title {
            text-align: center; color: white; font-size: 1.5rem;
            font-weight: 800; letter-spacing: -0.02em; margin-bottom: 0.4rem;
        }
        .login-subtitle {
            text-align: center; color: rgba(255,255,255,0.55);
            font-size: 0.82rem; margin-bottom: 2rem;
        }

        /* Form */
        .form-group { margin-bottom: 1.25rem; }
        .form-label {
            display: block; font-size: 0.75rem; font-weight: 700;
            color: rgba(255,255,255,0.7); text-transform: uppercase;
            letter-spacing: 0.07em; margin-bottom: 0.5rem;
        }
        .input-wrapper { position: relative; }
        .input-wrapper .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,0.35); font-size: 0.9rem; pointer-events: none;
        }
        .form-input {
            width: 100%; padding: 0.75rem 1rem 0.75rem 2.75rem;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 0.75rem;
            color: white; font-size: 0.9rem; font-family: 'Inter', sans-serif;
            outline: none; transition: all 0.2s;
        }
        .form-input::placeholder { color: rgba(255,255,255,0.3); }
        .form-input:focus {
            border-color: rgba(99,102,241,0.7);
            background: rgba(99,102,241,0.1);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }

        /* Show password toggle */
        .eye-btn {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            background: none; border: none; color: rgba(255,255,255,0.35);
            cursor: pointer; padding: 0; font-size: 0.85rem; transition: color 0.2s;
        }
        .eye-btn:hover { color: rgba(255,255,255,0.7); }

        /* Submit button */
        .btn-login {
            width: 100%; padding: 0.85rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none; border-radius: 0.875rem;
            color: white; font-size: 0.95rem; font-weight: 700;
            font-family: 'Inter', sans-serif; cursor: pointer;
            transition: all 0.2s; letter-spacing: -0.01em;
            box-shadow: 0 6px 20px rgba(99,102,241,0.4);
            position: relative; overflow: hidden;
        }
        .btn-login::before {
            content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(99,102,241,0.55); }
        .btn-login:hover::before { left: 100%; }
        .btn-login:active { transform: translateY(0); }

        /* Alert */
        .alert-error {
            background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.3);
            border-radius: 0.75rem; padding: 0.875rem 1rem; margin-bottom: 1.25rem;
            color: #fca5a5; font-size: 0.82rem;
            display: flex; align-items: center; gap: 8px;
        }
        .alert-success {
            background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.3);
            border-radius: 0.75rem; padding: 0.875rem 1rem; margin-bottom: 1.25rem;
            color: #6ee7b7; font-size: 0.82rem;
            display: flex; align-items: center; gap: 8px;
        }

        /* Bottom info */
        .info-box {
            margin-top: 1.5rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 0.75rem; padding: 0.875rem 1rem;
        }
        .info-box p { font-size: 0.75rem; color: rgba(255,255,255,0.45); margin: 0; }
        .info-box p + p { margin-top: 0.35rem; }

        /* Divider */
        .divider {
            display: flex; align-items: center; gap: 1rem; margin: 1.25rem 0;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.1);
        }
        .divider span { font-size: 0.72rem; color: rgba(255,255,255,0.35); white-space: nowrap; }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="login-card">
        {{-- Logo --}}
        <div class="logo-ring">
            <i class="fa-solid fa-tower-broadcast"></i>
        </div>

        <div class="login-title">ZIWO Admin Panel</div>
        <div class="login-subtitle">Connect your ZIWO administrator account to access<br>live dashboards and statistics reports</div>

        {{-- Error message --}}
        @if($errors->any())
        <div class="alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            {{ $errors->first('message') ?? $errors->first() }}
        </div>
        @endif

        @if(session('success'))
        <div class="alert-success">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
        @endif

        {{-- Login Form --}}
        <form method="POST" action="{{ route('ziwo.login.post') }}" id="ziwo-login-form">
            @csrf

            <div class="form-group">
                <label for="ziwo-username" class="form-label">ZIWO Admin Email</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-at input-icon"></i>
                    <input
                        id="ziwo-username"
                        type="email"
                        name="username"
                        class="form-input"
                        placeholder="admin@example.com"
                        value="{{ old('username') }}"
                        autocomplete="username"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="ziwo-password" class="form-label">ZIWO Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input
                        id="ziwo-password"
                        type="password"
                        name="password"
                        class="form-input"
                        placeholder="••••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="eye-btn" id="toggle-pw" aria-label="Show/hide password">
                        <i class="fa-solid fa-eye" id="eye-icon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="login-btn">
                <i class="fa-solid fa-right-to-bracket" style="margin-right:8px;"></i>
                Connect to ZIWO
            </button>
        </form>

        <div class="divider"><span>Authentication is end-to-end secure</span></div>

        <div class="info-box">
            <p><i class="fa-solid fa-shield-halved" style="color:#8b5cf6;margin-right:4px;"></i> Your credentials are verified directly with the ZIWO API and never stored locally.</p>
            <p><i class="fa-solid fa-clock" style="color:#6366f1;margin-right:4px;"></i> Session expires when you close the browser or log out.</p>
        </div>
    </div>

    <script>
        // Password visibility toggle
        document.getElementById('toggle-pw').addEventListener('click', function() {
            const pw = document.getElementById('ziwo-password');
            const icon = document.getElementById('eye-icon');
            if (pw.type === 'password') {
                pw.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                pw.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });

        // Loading state
        document.getElementById('ziwo-login-form').addEventListener('submit', function() {
            const btn = document.getElementById('login-btn');
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin" style="margin-right:8px;"></i> Connecting...';
            btn.disabled = true;
        });
    </script>
</body>
</html>
