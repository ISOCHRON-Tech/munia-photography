@extends('layouts.admin')
@section('title', 'Edit Photo')

@section('content')
<div class="flex items-center gap-4 mb-8">
    <a href="{{ route('admin.media.index') }}"
       class="text-[#8b3a6e] hover:text-[#1a0d14] transition-colors text-sm">← Gallery</a>
    <span class="text-[#2e2e2e]">/</span>
    <h1 class="text-2xl font-display text-[#1a0d14]">Edit Photo</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

    {{-- Thumbnail preview --}}
    <div class="lg:col-span-2">
        <div class="rounded overflow-hidden bg-[#ffe4f0] aspect-square">
            @if($mediaItem->public_url)
            <img src="{{ $mediaItem->public_url }}"
                 alt="{{ $mediaItem->title ?? '' }}"
                 class="w-full h-full object-cover">
            @else
            <div class="w-full h-full flex items-center justify-center text-[#8b3a6e] text-xs">
                Processing…
            </div>
            @endif
        </div>

        {{-- EXIF info --}}
        @if($mediaItem->camera_model || $mediaItem->taken_at)
        <div class="mt-4 space-y-1.5 text-xs text-[#8b3a6e]">
            @if($mediaItem->camera_model)
            <div class="flex justify-between">
                <span class="opacity-50 tracking-widest uppercase">Camera</span>
                <span>{{ $mediaItem->camera_model }}</span>
            </div>
            @endif
            @if($mediaItem->lens)
            <div class="flex justify-between">
                <span class="opacity-50 tracking-widest uppercase">Lens</span>
                <span>{{ $mediaItem->lens }}</span>
            </div>
            @endif
            @if($mediaItem->aperture)
            <div class="flex justify-between">
                <span class="opacity-50 tracking-widest uppercase">Aperture</span>
                <span>ƒ{{ $mediaItem->aperture }}</span>
            </div>
            @endif
            @if($mediaItem->shutter_speed)
            <div class="flex justify-between">
                <span class="opacity-50 tracking-widest uppercase">Shutter</span>
                <span>{{ $mediaItem->shutter_speed }}</span>
            </div>
            @endif
            @if($mediaItem->iso)
            <div class="flex justify-between">
                <span class="opacity-50 tracking-widest uppercase">ISO</span>
                <span>{{ $mediaItem->iso }}</span>
            </div>
            @endif
            @if($mediaItem->focal_length)
            <div class="flex justify-between">
                <span class="opacity-50 tracking-widest uppercase">Focal length</span>
                <span>{{ $mediaItem->focal_length }}mm</span>
            </div>
            @endif
            @if($mediaItem->taken_at)
            <div class="flex justify-between">
                <span class="opacity-50 tracking-widest uppercase">Taken</span>
                <span>{{ $mediaItem->taken_at->format('j M Y') }}</span>
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- Edit form --}}
    <div class="lg:col-span-3">
        @if($errors->any())
        <div class="mb-6 text-sm text-red-700 bg-red-50 border border-red-300 rounded px-4 py-3 space-y-1">
            @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('admin.media.update', $mediaItem) }}">
            @csrf
            @method('PUT')

            <div class="space-y-5">

                {{-- Title --}}
                <div>
                    <label class="block text-xs tracking-widest uppercase text-[#8b3a6e] mb-1">Title</label>
                    <input type="text" name="title" maxlength="200"
                           value="{{ old('title', $mediaItem->title) }}"
                           class="w-full bg-[#ffe4f0] border border-[#ffb3d9] rounded px-4 py-2.5 text-sm text-[#1a0d14]
                                  focus:outline-none focus:border-[#ff1493] transition-colors">
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs tracking-widest uppercase text-[#8b3a6e] mb-1">Description</label>
                    <textarea name="description" rows="4" maxlength="2000"
                              class="w-full bg-[#ffe4f0] border border-[#ffb3d9] rounded px-4 py-2.5 text-sm text-[#1a0d14]
                                     focus:outline-none focus:border-[#ff1493] transition-colors resize-none">{{ old('description', $mediaItem->description) }}</textarea>
                </div>

                {{-- Category + Sort order --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs tracking-widest uppercase text-[#8b3a6e] mb-1">Category</label>
                        <select name="category_id"
                                class="w-full bg-[#ffe4f0] border border-[#ffb3d9] rounded px-4 py-2.5 text-sm text-[#1a0d14]
                                       focus:outline-none focus:border-[#ff1493] transition-colors">
                            <option value="">— None —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                    {{ old('category_id', $mediaItem->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs tracking-widest uppercase text-[#8b3a6e] mb-1">Sort order</label>
                        <input type="number" name="sort_order" min="0"
                               value="{{ old('sort_order', $mediaItem->sort_order ?? 0) }}"
                               class="w-full bg-[#ffe4f0] border border-[#ffb3d9] rounded px-4 py-2.5 text-sm text-[#1a0d14]
                                      focus:outline-none focus:border-[#ff1493] transition-colors">
                    </div>
                </div>

                {{-- Tags --}}
                <div>
                    <label class="block text-xs tracking-widest uppercase text-[#8b3a6e] mb-1">Tags
                        <span class="normal-case opacity-50">(comma-separated)</span>
                    </label>
                    <input type="text" name="tags"
                           value="{{ old('tags', $mediaItem->tags->pluck('name')->implode(', ')) }}"
                           class="w-full bg-[#ffe4f0] border border-[#ffb3d9] rounded px-4 py-2.5 text-sm text-[#1a0d14]
                                  focus:outline-none focus:border-[#ff1493] transition-colors"
                           placeholder="landscape, travel, portrait">
                </div>

                {{-- Featured --}}
                <div class="flex items-start gap-3 pt-1">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1"
                           {{ old('is_featured', $mediaItem->is_featured) ? 'checked' : '' }}
                           class="mt-0.5 rounded border-[#ffb3d9] bg-[#ffe4f0] text-[#ff1493] focus:ring-0">
                    <div>
                        <label for="is_featured" class="text-sm text-[#1a0d14] cursor-pointer">
                            Featured <span class="text-[#ff1493]">★</span>
                        </label>
                        <p class="text-xs text-[#8b3a6e] mt-0.5">
                            Shows in the <strong class="text-[#ff1493]">Home page</strong> hero section and
                            selected work grid
                        </p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between pt-4 border-t border-[#ffb3d9]">
                    <a href="{{ route('admin.media.index') }}"
                       class="text-sm text-[#8b3a6e] hover:text-[#1a0d14] transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 bg-[#ff1493] text-white text-sm font-medium rounded
                                   hover:bg-[#c71585] transition-colors">
                        Save changes
                    </button>
                </div>

            </div>
        </form>
    </div>

</div>
@endsection
