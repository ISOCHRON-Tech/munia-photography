<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Story;
use App\Support\HomeDummyData;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class StoryController extends Controller
{
    private const CACHE_TTL = 300;

    public function index(): View
    {
        // Skip the cache when there are no published stories — avoids serializing
        // an empty paginator into the database cache which can fail to deserialize.
        if (! Story::published()->exists()) {
            Cache::forget('stories:index:page:1');
            $dummyStories = HomeDummyData::storiesIndex();
            $stories = new LengthAwarePaginator(
                $dummyStories->all(),
                $dummyStories->count(),
                9,
                1,
                ['path' => request()->url()]
            );
        } else {
            $stories = Cache::remember('stories:index:page:1', self::CACHE_TTL, fn () =>
                Story::published()
                    ->orderByDesc('published_at')
                    ->paginate(9)
            );
        }

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
