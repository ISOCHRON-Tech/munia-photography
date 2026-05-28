<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Injects strict security headers on every outgoing response.
 * Configured conservatively so CSP works with Vite-compiled assets
 * (nonces are not needed because scripts are loaded via <link> / <script src>).
 */
class SecureHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $isDev = app()->environment('local');

        $scriptSrc = $isDev
            ? "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://127.0.0.1:5173"
            : "script-src 'self' 'unsafe-inline' 'unsafe-eval'";

        $styleSrc = $isDev
            ? "style-src 'self' 'unsafe-inline' http://127.0.0.1:5173 https://fonts.googleapis.com https://cdnjs.cloudflare.com"
            : "style-src 'self' 'unsafe-inline'";

        $fontSrc = $isDev
            ? "font-src 'self' data: https://fonts.googleapis.com https://fonts.gstatic.com https://cdnjs.cloudflare.com"
            : "font-src 'self' data:";

        $connectSrc = $isDev
            ? "connect-src 'self' http://127.0.0.1:5173 ws://127.0.0.1:5173"
            : "connect-src 'self'";

        $r2PublicUrl = rtrim((string) env('R2_PUBLIC_URL', ''), '/');

        $imgSrc = $isDev
            ? "img-src 'self' data: blob: http://127.0.0.1:5173 https://picsum.photos https://fastly.picsum.photos" . ($r2PublicUrl ? " $r2PublicUrl" : '')
            : "img-src 'self' data: blob:" . ($r2PublicUrl ? " $r2PublicUrl" : '');

        $cspParts = [
            "default-src 'self'",
            $scriptSrc,
            $styleSrc,
            $imgSrc,
            $fontSrc,
            $connectSrc,
            "media-src 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        if (! $isDev) {
            $cspParts[] = "upgrade-insecure-requests";
        }

        $csp = implode('; ', $cspParts);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains; preload'
        );

        // Remove headers that leak server info
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
