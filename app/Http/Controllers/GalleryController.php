<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MediaItem;
use App\Support\HomeDummyData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $categorySlug = $request->query('category');
        $tagSlug      = $request->query('tag');

        // Never cache a paginator — it serializes poorly. Run the query directly.
        $items = MediaItem::query()
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

        $categories = Category::withCount('mediaItems')->orderBy('name')->get();

        // ── Dummy data fallback (no DB records yet) ───────────────────────
        if ($items->isEmpty()) {
            $dummyItems = HomeDummyData::galleryItems();
            $items = new LengthAwarePaginator(
                $dummyItems->all(),
                $dummyItems->count(),
                24,
                1,
                ['path' => $request->url()]
            );
        }

        if ($categories->isEmpty()) {
            $categories = HomeDummyData::galleryCategories();
        }

        if ($request->ajax()) {
            $gridHtml = '';
            foreach ($items as $item) {
                $gridHtml .= view('components.photo-card', ['item' => $item])->render();
            }
            $paginationHtml = $items->hasPages()
                ? (string) $items->links('vendor.pagination.tailwind')
                : '';
            return response()->json(['grid' => $gridHtml, 'pagination' => $paginationHtml]);
        }

        return view('gallery.index', compact('items', 'categories', 'categorySlug', 'tagSlug'));
    }

    public function show(MediaItem $mediaItem): View
    {
        $mediaItem->load(['category', 'tags']);

        $related = MediaItem::query()
            ->where('id', '!=', $mediaItem->id)
            ->where('category_id', $mediaItem->category_id)
            ->orderByDesc('is_featured')
            ->limit(6)
            ->get();

        return view('gallery.show', compact('mediaItem', 'related'));
    }
}
