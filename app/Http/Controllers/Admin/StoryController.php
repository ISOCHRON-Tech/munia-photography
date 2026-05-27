<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStoryRequest;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class StoryController extends Controller
{
    public function index(): View
    {
        $stories = Story::orderByDesc('updated_at')->paginate(15);
        return view('admin.stories.index', compact('stories'));
    }

    public function create(): View
    {
        return view('admin.stories.create');
    }

    public function store(StoreStoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('banner')) {
            [$bannerPath, $bannerWebpPath] = $this->processBanner($request->file('banner'));
            $data['banner_path']     = $bannerPath;
            $data['banner_webp_path'] = $bannerWebpPath;
        }

        unset($data['banner']);

        $story = Story::create($data);

        Cache::forget('stories:index:page:1');

        return redirect()->route('admin.stories.index')->with('success', "Story '{$story->title}' saved.");
    }

    public function edit(Story $story): View
    {
        return view('admin.stories.edit', compact('story'));
    }

    public function update(StoreStoryRequest $request, Story $story): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('banner')) {
            // Remove old banner files
            Storage::delete(array_filter([$story->banner_path, $story->banner_webp_path]));

            [$bannerPath, $bannerWebpPath] = $this->processBanner($request->file('banner'));
            $data['banner_path']      = $bannerPath;
            $data['banner_webp_path'] = $bannerWebpPath;
        }

        unset($data['banner']);

        $story->update($data);

        Cache::forget('stories:' . $story->slug);
        Cache::forget('stories:index:page:1');

        return redirect()->route('admin.stories.index')->with('success', 'Story updated.');
    }

    public function destroy(Story $story): RedirectResponse
    {
        Storage::delete(array_filter([$story->banner_path, $story->banner_webp_path, $story->og_image_path]));
        Cache::forget('stories:' . $story->slug);
        $story->delete();

        return redirect()->route('admin.stories.index')->with('success', 'Story deleted.');
    }

    // -----------------------------------------------------------------------

    private function processBanner(\Illuminate\Http\UploadedFile $file): array
    {
        $uuid    = (string) Str::uuid();
        $dir     = 'public/banners/' . $uuid;
        $manager = new ImageManager(new GdDriver());
        $image   = $manager->read($file->getRealPath());

        // Resize banner to max 1920 wide, preserve ratio
        if ($image->width() > 1920) {
            $image->scaleDown(width: 1920);
        }

        $origPath = $dir . '/banner.jpg';
        $webpPath = $dir . '/banner.webp';

        Storage::put($origPath, $image->toJpeg(quality: 85)->toString());
        Storage::put($webpPath, $image->toWebp(quality: 82)->toString());

        return [$origPath, $webpPath];
    }
}
