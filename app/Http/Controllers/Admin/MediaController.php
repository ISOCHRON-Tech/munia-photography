<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaRequest;
use App\Models\MediaItem;
use App\Services\MediaUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(
        private readonly MediaUploadService $uploadService,
    ) {}

    public function index(): View
    {
        $items = MediaItem::with(['category', 'tags'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.media.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.media.create');
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
