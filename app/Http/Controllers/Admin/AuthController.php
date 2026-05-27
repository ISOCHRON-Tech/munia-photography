<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    // ── Show login form ──────────────────────────────────────────────────────

    public function showLogin(): View
    {
        return view('admin.login');
    }

    // ── Handle login submission ──────────────────────────────────────────────

    public function login(Request $request): RedirectResponse
    {
        // Rate-limit: 5 attempts per 10 minutes per IP
        $key = 'admin-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'form' => "Too many attempts. Try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $adminEmail    = (string) config('admin.email', '');
        $adminHash     = (string) config('admin.password_hash', '');

        $emailMatch    = hash_equals(strtolower($adminEmail), strtolower($request->email));
        $passwordMatch = $adminHash && Hash::check($request->password, $adminHash);

        if (!$emailMatch || !$passwordMatch) {
            RateLimiter::hit($key, 600);

            return back()->withErrors([
                'form' => 'The credentials you entered are incorrect.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($key);

        $request->session()->regenerate();
        $request->session()->put('admin_authenticated', true);
        $request->session()->put('admin_ip', $request->ip());

        return redirect()->intended(route('admin.media.index'));
    }

    // ── Logout ───────────────────────────────────────────────────────────────

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}

