<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class StoryController extends Controller
{
    private const CACHE_TTL = 300;

    public function index(): View
    {
        $stories = Cache::remember('stories:index:page:1', self::CACHE_TTL, fn () =>
            Story::published()
                ->orderByDesc('published_at')
                ->paginate(9)
        );

        return view('stories.index', compact('stories'));
    }

    public function show(string $slug): View
    {
        $story = Cache::remember('stories:' . $slug, self::CACHE_TTL, fn () =>
            Story::published()->where('slug', $slug)->firstOrFail()
        );

        $adjacent = Cache::remember('stories:adjacent:' . $story->id, self::CACHE_TTL, function () use ($story) {
            $prev = Story::published()
                ->where('published_at', '<', $story->published_at)
                ->orderByDesc('published_at')
                ->first();

            $next = Story::published()
                ->where('published_at', '>', $story->published_at)
                ->orderBy('published_at')
                ->first();

            return compact('prev', 'next');
        });

        return view('stories.show', array_merge(compact('story'), $adjacent));
    }
}
