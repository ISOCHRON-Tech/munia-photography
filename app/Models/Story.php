<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Story extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'content',
        'banner_path',
        'banner_webp_path',
        'meta_title',
        'meta_description',
        'og_image_path',
        'status',
        'published_at',
        'reading_time_minutes',
    ];

    protected $casts = [
        'published_at'          => 'datetime',
        'reading_time_minutes'  => 'integer',
    ];

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopePublished($query): mixed
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    // -----------------------------------------------------------------------
    // Accessors / Mutators
    // -----------------------------------------------------------------------

    public function getBannerUrlAttribute(): ?string
    {
        $path = $this->banner_webp_path ?? $this->banner_path;

        if (! $path) {
            return null;
        }

        // Support full HTTP URLs stored during seeding / dummy data scenarios
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return \Illuminate\Support\Facades\Storage::url($path);
    }

    public function getOgImageUrlAttribute(): ?string
    {
        return $this->og_image_path
            ? \Illuminate\Support\Facades\Storage::url($this->og_image_path)
            : $this->banner_url;
    }

    public function getEffectiveMetaTitleAttribute(): string
    {
        return $this->meta_title ?? $this->title;
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /** Re-compute reading time from current content (~200 wpm). */
    public function computeReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return (int) max(1, ceil($wordCount / 200));
    }

    /** Auto-generate slug from title if not provided. */
    protected static function booted(): void
    {
        static::creating(function (Story $story): void {
            if (empty($story->slug)) {
                $story->slug = Str::slug($story->title);
            }
            $story->reading_time_minutes = $story->computeReadingTime();
        });

        static::updating(function (Story $story): void {
            if ($story->isDirty('content')) {
                $story->reading_time_minutes = $story->computeReadingTime();
            }
        });
    }
}
