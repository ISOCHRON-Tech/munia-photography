<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MediaItem;
use App\Models\Story;
use App\Support\HomeDummyData;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featured = MediaItem::query()
            ->with(['category'])
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->orderByDesc('taken_at')
            ->limit(6)
            ->get();

        // Fall back to most recent if no featured items set
        if ($featured->isEmpty()) {
            $featured = MediaItem::query()
                ->with(['category'])
                ->orderByDesc('taken_at')
                ->limit(6)
                ->get();
        }

        $stories = Story::published()
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $totalPhotos = MediaItem::count();

        // ── Dummy data fallback (no DB records yet) ───────────────────────
        if ($featured->isEmpty()) {
            $featured = HomeDummyData::featured();
        }

        if ($stories->isEmpty()) {
            $stories = HomeDummyData::stories();
        }

        if ($totalPhotos === 0) {
            $totalPhotos = HomeDummyData::totalPhotos();
        }

        return view('home', compact('featured', 'stories', 'totalPhotos'));
    }
}
