<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One-time magic-link token for admin authentication.
 *
 * @property int         $id
 * @property string      $token_hash   SHA-256 of the raw token
 * @property string|null $ip_address
 * @property Carbon      $expires_at
 * @property Carbon|null $used_at
 */
class AdminLoginToken extends Model
{
    protected $fillable = ['token_hash', 'ip_address', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────────────────────────

    /** Only tokens that haven't been used AND haven't expired. */
    public function scopeValid($query): void
    {
        $query->whereNull('used_at')
              ->where('expires_at', '>', now());
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    /** Mark the token as consumed (idempotent). */
    public function consume(): void
    {
        if (!$this->used_at) {
            $this->update(['used_at' => now()]);
        }
    }
}
