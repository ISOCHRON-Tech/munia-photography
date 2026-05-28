<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Story;
use App\Support\HomeDummyData;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class StoryController extends Controller
{
    private const CACHE_TTL = 300;

    public function index(): View
    {
        if (! Story::published()->exists()) {
            $dummyStories = HomeDummyData::storiesIndex();
            $stories = new LengthAwarePaginator(
                $dummyStories->all(),
                $dummyStories->count(),
                9,
                1,
                ['path' => request()->url()]
            );
        } else {
            $stories = Story::published()
                ->orderByDesc('published_at')
                ->paginate(9);
        }

        return view('stories.index', compact('stories'));
    }

    public function show(string $slug): View
    {
        $story = Story::published()->where('slug', $slug)->firstOrFail();

        $prev = Story::published()
            ->where('published_at', '<', $story->published_at)
            ->orderByDesc('published_at')
            ->first();

        $next = Story::published()
            ->where('published_at', '>', $story->published_at)
            ->orderBy('published_at')
            ->first();

        return view('stories.show', compact('story', 'prev', 'next'));
    }
}
