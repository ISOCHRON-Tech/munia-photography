<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLoginToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Passwordless magic-link authentication for the admin area.
 *
 * Flow:
 *  1. GET  /admin/login            → show email form
 *  2. POST /admin/login            → verify email, generate token, "send" link
 *  3. GET  /admin/login/verify/{t} → consume token, set session, redirect
 *  4. POST /admin/logout           → destroy session
 */
class AuthController extends Controller
{
    private const TOKEN_TTL_MINUTES = 15;

    // ── 1. Show login form ───────────────────────────────────────────────────

    public function showLogin(): View
    {
        return view('admin.login');
    }

    // ── 2. Request magic link ────────────────────────────────────────────────

    public function requestLink(Request $request): RedirectResponse
    {
        // Rate-limit: max 5 attempts per IP per 10 minutes
        $key = 'admin-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Too many attempts. Try again in {$seconds} seconds.",
            ]);
        }
        RateLimiter::hit($key, 600);

        $request->validate(['email' => ['required', 'email']]);

        $adminEmail = config('admin.email');

        // Constant-time comparison to prevent timing attacks
        if (!$adminEmail || !hash_equals(strtolower($adminEmail), strtolower($request->email))) {
            // Intentionally vague — don't reveal whether email exists
            return back()->with(
                'status',
                'If that address is registered, a login link has been sent.'
            );
        }

        // Purge all existing unused tokens before creating a new one
        AdminLoginToken::whereNull('used_at')->delete();

        // Generate a cryptographically-secure token
        $rawToken  = Str::random(64);
        $tokenHash = hash('sha256', $rawToken);

        AdminLoginToken::create([
            'token_hash' => $tokenHash,
            'ip_address' => $request->ip(),
            'expires_at' => now()->addMinutes(self::TOKEN_TTL_MINUTES),
        ]);

        $loginUrl = route('admin.login.verify', ['token' => $rawToken]);

        // In production this would be emailed. For now log + flash the link
        // so you can click it immediately in development.
        Log::info('[Admin Magic Link] ' . $loginUrl);

        return back()->with('magic_link', $loginUrl)
                     ->with('status', 'Login link generated. Valid for ' . self::TOKEN_TTL_MINUTES . ' minutes.');
    }

    // ── 3. Verify / consume token ────────────────────────────────────────────

    public function verifyToken(Request $request, string $token): RedirectResponse
    {
        $hash  = hash('sha256', $token);
        $record = AdminLoginToken::valid()->where('token_hash', $hash)->first();

        if (!$record) {
            return redirect()->route('admin.login')
                ->withErrors(['token' => 'This login link is invalid or has expired.']);
        }

        // Consume immediately (single-use)
        $record->consume();

        // Regenerate session to prevent fixation
        $request->session()->regenerate();
        $request->session()->put('admin_authenticated', true);
        $request->session()->put('admin_ip', $request->ip());

        return redirect()->route('admin.media.index')
            ->with('success', 'Welcome back.');
    }

    // ── 4. Logout ────────────────────────────────────────────────────────────

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('status', 'You have been logged out.');
    }
}
