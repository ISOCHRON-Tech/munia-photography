<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \App\Services\MediaUploadService::class,
            \App\Services\MediaUploadService::class,
        );
    }

    public function boot(): void
    {
        // Admin login: 5 attempts per minute per IP
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Media upload: 20 per 5 minutes per IP
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinutes(5, 20)->by($request->ip());
        });

        // Contact form
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });
    }
}

