<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

// ── Prune stale job records older than 48 hours ────────────────────────────
Schedule::command('queue:prune-failed --hours=48')->daily();

// ── Clear query/page caches every 10 minutes ──────────────────────────────
Schedule::command('cache:prune-stale-tags')->everyTenMinutes();

