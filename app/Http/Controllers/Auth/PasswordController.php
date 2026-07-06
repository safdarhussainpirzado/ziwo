<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     * Enforces password history — rejects last 5 passwords (NIST IA-5).
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();

        // ── Password history check (last 5 passwords) ────────────────────
        $recentHashes = PasswordHistory::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->pluck('password');

        foreach ($recentHashes as $oldHash) {
            if (Hash::check($validated['password'], $oldHash)) {
                throw ValidationException::withMessages([
                    'password' => [
                        'You cannot reuse one of your last 5 passwords. Choose a new password.',
                    ],
                ])->errorBag('updatePassword');
            }
        }

        // ── Archive current password before overwriting ─────────────────
        PasswordHistory::create([
            'user_id'  => $user->id,
            'password' => $user->password,  // store current (still-valid) hash
        ]);

        $user->update([
            'password'        => Hash::make($validated['password']),
            'failed_attempts' => 0,   // Reset lockout counter on password change
            'locked_until'    => null,
        ]);

        return back()->with('status', 'password-updated');
    }
}
