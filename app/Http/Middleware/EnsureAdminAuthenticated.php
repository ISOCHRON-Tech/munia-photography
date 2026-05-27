<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards all /admin routes.
 * Checks for the session flag set by Admin\AuthController after a
 * successful magic-link redemption.
 */
class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->get('admin_authenticated')) {
            return redirect()->route('admin.login')
                ->with('intended', $request->fullUrl());
        }

        return $next($request);
    }
}
