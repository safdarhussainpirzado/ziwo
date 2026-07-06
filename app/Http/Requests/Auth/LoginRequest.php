<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // ── DB-level account lockout check (NIST AC-7 / ISO 27001 A.9.4.2) ──
        $user = \App\Models\User::where('username', $this->string('username'))->first();

        if ($user) {
            // Account hard-locked by admin / previous lockout
            if (!$user->is_active) {
                throw ValidationException::withMessages([
                    'username' => 'This account has been deactivated. Contact your administrator.',
                ]);
            }

            // Time-based lockout still active
            if ($user->locked_until && $user->locked_until->isFuture()) {
                $remaining = now()->diffInMinutes($user->locked_until, false);
                throw ValidationException::withMessages([
                    'username' => "Account locked. Try again in {$remaining} minute(s).",
                ]);
            }
        }

        if (! Auth::attempt($this->only('username', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Increment failed attempts and lock after 5 consecutive failures
            if ($user) {
                $attempts = $user->failed_attempts + 1;
                $lockUntil = $attempts >= 5 ? now()->addMinutes(30) : null;
                $user->update([
                    'failed_attempts' => $attempts,
                    'locked_until'    => $lockUntil,
                ]);

                // Log failed attempt in audit_logs
                \App\Models\AuditLog::create([
                    'user_id'    => $user->id,
                    'action'     => 'login_failed',
                    'ip_address' => $this->ip(),
                    'user_agent' => $this->userAgent(),
                ]);
            }

            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }
}
