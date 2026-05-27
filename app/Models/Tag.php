<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function mediaItems(): BelongsToMany
    {
        return $this->belongsToMany(MediaItem::class, 'media_item_tag');
    }
}
