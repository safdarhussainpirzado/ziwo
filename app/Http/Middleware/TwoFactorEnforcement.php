<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorEnforcement
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->is_active) {
            $requiredLevel = setting('2fa_level', 'none');

            $needs2FA = false;
            
            if ($requiredLevel === 'all') {
                $needs2FA = true;
            } elseif ($requiredLevel === 'supervisor' && in_array($user->role_id, [\App\Models\Role::ADMIN, \App\Models\Role::SUPERVISOR])) {
                $needs2FA = true;
            }

            if ($needs2FA && !session('2fa_verified')) {
                // If user doesn't have a secret, they need to set it up
                if (!$user->totp_secret) {
                    if (!$request->is('2fa/setup*') && !$request->is('logout')) {
                        if ($request->ajax() || $request->hasHeader('X-Requested-With')) {
                            return response('MFA_SETUP_REQUIRED', 403);
                        }
                        return redirect()->route('2fa.setup');
                    }
                } else {
                    // Regular 2FA verification
                    if (!$request->is('2fa*') && !$request->is('logout')) {
                        if ($request->ajax() || $request->hasHeader('X-Requested-With')) {
                            return response('MFA_REQUIRED', 403);
                        }
                        return redirect()->route('2fa.index');
                    }
                }
            }
        }

        return $next($request);
    }
}
