<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class MediaItem extends Model
{
    protected $fillable = [
        'uuid',
        'title',
        'description',
        'original_path',
        'webp_path',
        'avif_path',
        'srcset_paths',
        'blurhash',
        'lqip_path',
        'width',
        'height',
        'camera_make',
        'camera_model',
        'lens',
        'iso',
        'aperture',
        'shutter_speed',
        'focal_length',
        'taken_at_location',
        'taken_at',
        'category_id',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'srcset_paths'  => 'array',
        'is_featured'   => 'boolean',
        'taken_at'      => 'datetime',
        'sort_order'    => 'integer',
        'width'         => 'integer',
        'height'        => 'integer',
    ];

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'media_item_tag');
    }

    // -----------------------------------------------------------------------
    // Accessors
    // -----------------------------------------------------------------------

    /** Primary public URL — prefers AVIF, falls back to WebP. */
    public function getPublicUrlAttribute(): ?string
    {
        if ($this->avif_path) {
            return Storage::disk('r2')->url($this->avif_path);
        }

        if ($this->webp_path) {
            return Storage::disk('r2')->url($this->webp_path);
        }

        return null; // original never served publicly
    }

    /** WebP public URL. */
    public function getWebpUrlAttribute(): ?string
    {
        return $this->webp_path ? Storage::disk('r2')->url($this->webp_path) : null;
    }

    /** AVIF public URL. */
    public function getAvifUrlAttribute(): ?string
    {
        return $this->avif_path ? Storage::disk('r2')->url($this->avif_path) : null;
    }

    /**
     * Build an HTML srcset string from the stored responsive sizes map.
     */
    public function getSrcsetAttribute(): ?string
    {
        if (empty($this->srcset_paths)) {
            return null;
        }

        $parts = [];
        foreach ($this->srcset_paths as $width => $path) {
            $parts[] = Storage::disk('r2')->url($path) . ' ' . $width . 'w';
        }

        return implode(', ', $parts);
    }

    /** Aspect ratio as a CSS-friendly percentage (for padding-top trick). */
    public function getAspectRatioAttribute(): ?float
    {
        if ($this->width && $this->height) {
            return round(($this->height / $this->width) * 100, 4);
        }

        return null;
    }

    /** Camera display string. */
    public function getCameraDisplayAttribute(): string
    {
        return trim(($this->camera_make ?? '') . ' ' . ($this->camera_model ?? ''));
    }
}
