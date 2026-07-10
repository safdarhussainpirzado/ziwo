<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\LoginLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // ── Forensic login audit ───────────────────────────────────────
        LoginLog::create([
            'user_id'    => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at'   => now(),
        ]);

        // Update last login on user record
        auth()->user()->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'failed_attempts' => 0, // Reset on successful login
        ]);

        // User role-based dynamic landing page
        $user = auth()->user();
        $landingPage = $user->getLandingPageRoute();

        // ── Guard: never redirect to API/JSON endpoints as the landing page ──
        // AJAX polling requests (e.g. /telephony/status) can accidentally set the
        // "intended" URL in the session when the session expires mid-poll.
        // Detect these by checking the stored intended URL against known API prefixes.
        $intendedUrl = $request->session()->get('url.intended', '');
        $apiPrefixes = ['/telephony/', '/api/', '/ajax/'];
        $intendedIsApi = false;
        if ($intendedUrl) {
            $intendedPath = parse_url($intendedUrl, PHP_URL_PATH) ?? '';
            foreach ($apiPrefixes as $prefix) {
                if (str_starts_with($intendedPath, $prefix)) {
                    $intendedIsApi = true;
                    break;
                }
            }
        }

        if ($intendedIsApi) {
            // Discard the API intended URL and go straight to landing page
            $request->session()->forget('url.intended');
            return redirect()->to($landingPage);
        }

        return redirect()->intended($landingPage);
    }

    public function destroy(Request $request): RedirectResponse
    {
        // ── Forensic logout audit ──────────────────────────────────────
        if (auth()->check()) {
            LoginLog::where('user_id', auth()->id())
                ->whereNull('logout_at')
                ->latest('login_at')
                ->first()
                ?->update(['logout_at' => now()]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
