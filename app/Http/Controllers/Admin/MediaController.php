<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Models\Category;
use App\Models\MediaItem;
use App\Models\Tag;
use App\Services\MediaUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(
        private readonly MediaUploadService $uploadService,
    ) {}

    public function index(Request $request): View
    {
        $section = $request->query('section');

        $query = MediaItem::with(['category', 'tags'])->orderByDesc('created_at');

        if ($section === 'featured') {
            $query->where('is_featured', true);
        }

        $items = $query->paginate(20);

        return view('admin.media.index', compact('items', 'section'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.media.create', compact('categories'));
    }

    /**
     * Handle one or more file uploads from the admin panel.
     * Each file is individually processed; errors on single files don't abort the batch.
     */
    public function store(StoreMediaRequest $request): JsonResponse
    {
        $results = [];

        foreach ($request->file('images') as $file) {
            $item      = $this->uploadService->store($file, $request->only(
                'title', 'description', 'category_id', 'tags', 'is_featured'
            ));
            $results[] = [
                'id'    => $item->id,
                'uuid'  => $item->uuid,
                'title' => $item->title,
            ];
        }

        return response()->json([
            'message' => 'Upload queued for processing.',
            'items'   => $results,
        ], 201);
    }

    public function edit(MediaItem $mediaItem): View
    {
        $categories = Category::orderBy('name')->get();
        $mediaItem->load(['category', 'tags']);
        return view('admin.media.edit', compact('mediaItem', 'categories'));
    }

    public function update(UpdateMediaRequest $request, MediaItem $mediaItem): RedirectResponse
    {
        $mediaItem->update([
            'title'       => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'is_featured' => $request->boolean('is_featured'),
            'sort_order'  => $request->integer('sort_order', $mediaItem->sort_order ?? 0),
        ]);

        $tagIds = collect(
            array_filter(array_map('trim', explode(',', $request->tags ?? '')))
        )->map(fn ($name) => Tag::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name]
        )->id)->all();

        $mediaItem->tags()->sync($tagIds);

        return redirect()->route('admin.media.index')->with('success', 'Photo updated.');
    }

    /** AJAX: toggle is_featured on a single item. */
    public function feature(MediaItem $mediaItem): JsonResponse
    {
        $mediaItem->update(['is_featured' => !$mediaItem->is_featured]);
        return response()->json(['featured' => $mediaItem->is_featured]);
    }

    public function destroy(MediaItem $mediaItem): RedirectResponse
    {
        // Remove stored files from both disks
        foreach ([$mediaItem->original_path, $mediaItem->webp_path, $mediaItem->avif_path] as $path) {
            if ($path) {
                \Illuminate\Support\Facades\Storage::delete($path);
            }
        }

        if ($mediaItem->srcset_paths) {
            foreach ($mediaItem->srcset_paths as $path) {
                \Illuminate\Support\Facades\Storage::delete($path);
            }
        }

        $mediaItem->delete();

        return redirect()->route('admin.media.index')->with('success', 'Media item deleted.');
    }
}
