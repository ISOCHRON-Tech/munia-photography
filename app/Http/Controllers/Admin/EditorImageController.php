<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditorImageController extends Controller
{
    /**
     * Accept an image upload from the EasyMDE editor, store it on R2,
     * and return the public URL for the editor to insert.
     *
     * POST /admin/editor/image
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,webp,gif', 'max:10240'],
        ]);

        $file = $request->file('image');
        $ext  = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = 'editor/' . Str::uuid() . '.' . $ext;

        Storage::disk('r2')->put($path, $file->get(), 'public');

        $url = Storage::disk('r2')->url($path);

        return response()->json(['url' => $url], 201);
    }
}
