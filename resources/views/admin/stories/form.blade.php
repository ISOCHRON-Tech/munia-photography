@extends('layouts.admin')
@section('title', isset($story) ? 'Edit Story' : 'New Story')

@section('content')
<div class="max-w-3xl">
    <h1 class="text-2xl font-display text-[#f5f0eb] mb-8">
        {{ isset($story) ? 'Edit: ' . $story->title : 'New Story' }}
    </h1>

    <form method="POST"
          action="{{ isset($story) ? route('admin.stories.update', $story) : route('admin.stories.store') }}"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @isset($story) @method('PUT') @endisset

        {{-- Title --}}
        <div>
            <label class="label-xs">Title *</label>
            <input type="text" name="title" value="{{ old('title', $story->title ?? '') }}"
                   required maxlength="255"
                   class="field-input">
        </div>

        {{-- Slug --}}
        <div>
            <label class="label-xs">Slug (auto-generated if blank)</label>
            <input type="text" name="slug" value="{{ old('slug', $story->slug ?? '') }}"
                   maxlength="255"
                   class="field-input">
        </div>

        {{-- Banner image --}}
        <div>
            <label class="label-xs">Banner Image</label>
            @isset($story)
            @if($story->banner_url)
            <img src="{{ $story->banner_url }}" alt="" class="h-32 rounded mb-3 object-cover">
            @endif
            @endisset
            <input type="file" name="banner" accept="image/jpeg,image/png,image/webp"
                   class="block text-sm text-[#9e9e9e] file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-[#2e2e2e] file:text-[#f5f0eb] hover:file:bg-[#3a3a3a] cursor-pointer">
        </div>

        {{-- Content --}}
        <div>
            <label class="label-xs">Content * (HTML)</label>
            <textarea name="content" rows="20" required
                      class="field-input font-mono text-xs">{{ old('content', $story->content ?? '') }}</textarea>
        </div>

        {{-- SEO --}}
        <fieldset class="border border-[#2e2e2e] rounded p-5 space-y-4">
            <legend class="label-xs px-2">SEO</legend>

            <div>
                <label class="label-xs">Meta Title (≤70 chars)</label>
                <input type="text" name="meta_title" maxlength="70"
                       value="{{ old('meta_title', $story->meta_title ?? '') }}"
                       class="field-input">
            </div>

            <div>
                <label class="label-xs">Meta Description (≤160 chars)</label>
                <textarea name="meta_description" rows="2" maxlength="160"
                          class="field-input resize-none">{{ old('meta_description', $story->meta_description ?? '') }}</textarea>
            </div>
        </fieldset>

        {{-- Status + Publish Date --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="label-xs">Status *</label>
                <select name="status" class="field-input">
                    <option value="draft"     @selected(old('status', $story->status ?? 'draft') === 'draft')>Draft</option>
                    <option value="published" @selected(old('status', $story->status ?? 'draft') === 'published')>Published</option>
                </select>
            </div>
            <div>
                <label class="label-xs">Publish Date</label>
                <input type="datetime-local" name="published_at"
                       value="{{ old('published_at', isset($story) && $story->published_at ? $story->published_at->format('Y-m-d\TH:i') : '') }}"
                       class="field-input">
            </div>
        </div>

        <div class="flex gap-4 pt-2">
            <button type="submit"
                    class="px-6 py-2 bg-[#c9a84c] text-[#0a0a0a] text-sm font-medium rounded hover:bg-[#b8943e] transition-colors">
                {{ isset($story) ? 'Update Story' : 'Create Story' }}
            </button>
            <a href="{{ route('admin.stories.index') }}"
               class="px-4 py-2 text-sm text-[#9e9e9e] hover:text-[#f5f0eb] transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

<style>
.label-xs   { @apply block text-xs tracking-widest uppercase text-[#9e9e9e] mb-1; }
.field-input { @apply w-full bg-[#1a1a1a] border border-[#2e2e2e] rounded px-4 py-2 text-sm text-[#f5f0eb] focus:outline-none focus:border-[#c9a84c]; }
</style>
@endsection
