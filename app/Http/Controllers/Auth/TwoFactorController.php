<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function index()
    {
        return view('auth.two-factor');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric|digits:6',
        ]);

        $user = auth()->user();
        $google2fa = new Google2FA();

        if ($google2fa->verifyKey($user->totp_secret, $request->code)) {
            session(['2fa_verified' => true]);
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['code' => 'The provided security code is invalid.']);
    }

    public function setup()
    {
        $user = auth()->user();
        $google2fa = new Google2FA();

        if (!$user->totp_secret) {
            $user->totp_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->username,
            $user->totp_secret
        );

        return view('auth.two-factor-setup', [
            'secret' => $user->totp_secret,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }
}
