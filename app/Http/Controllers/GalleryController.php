<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class GalleryController extends Controller
{
    private const CACHE_TTL = 300; // 5 minutes

    public function index(Request $request): View
    {
        $categorySlug = $request->query('category');
        $tagSlug      = $request->query('tag');

        $cacheKey = 'gallery:' . md5($categorySlug . '|' . $tagSlug . '|page:' . $request->query('page', 1));

        $items = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($categorySlug, $tagSlug) {
            return MediaItem::query()
                ->with(['category', 'tags'])
                ->when($categorySlug, fn ($q) => $q->whereHas(
                    'category', fn ($q) => $q->where('slug', $categorySlug)
                ))
                ->when($tagSlug, fn ($q) => $q->whereHas(
                    'tags', fn ($q) => $q->where('slug', $tagSlug)
                ))
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderByDesc('taken_at')
                ->paginate(24)
                ->withQueryString();
        });

        $categories = Cache::remember('categories:all', self::CACHE_TTL, fn () =>
            Category::withCount('mediaItems')->orderBy('name')->get()
        );

        return view('gallery.index', compact('items', 'categories', 'categorySlug', 'tagSlug'));
    }

    public function show(MediaItem $mediaItem): View
    {
        $mediaItem->load(['category', 'tags']);

        $related = Cache::remember(
            'gallery:related:' . $mediaItem->id,
            self::CACHE_TTL,
            fn () => MediaItem::query()
                ->where('id', '!=', $mediaItem->id)
                ->where('category_id', $mediaItem->category_id)
                ->orderByDesc('is_featured')
                ->limit(6)
                ->get()
        );

        return view('gallery.show', compact('mediaItem', 'related'));
    }
}
