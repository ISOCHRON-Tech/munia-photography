@extends('layouts.admin')
@section('title', $section === 'featured' ? 'Featured Photos' : 'Gallery')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-display text-[#1a0d14]">
            {{ $section === 'featured' ? 'Featured Photos' : 'Gallery' }}
        </h1>
        <p class="text-xs text-[#8b3a6e] mt-1">
            @if($section === 'featured')
                Photos shown in the <span class="text-[#ff1493]">Home page hero &amp; selected work</span> grid
            @else
                All uploaded photographs — visible in the public Gallery
            @endif
        </p>
    </div>
    <a href="{{ route('admin.media.create') }}"
       class="px-5 py-2 bg-[#ff1493] text-white text-sm font-medium rounded hover:bg-[#c71585] transition-colors">
        + Upload
    </a>
</div>

{{-- Section tabs --}}
<div class="flex gap-6 border-b border-[#ffb3d9] mb-6">
    <a href="{{ route('admin.media.index') }}"
       class="pb-3 text-sm border-b-2 transition-colors -mb-px
              {{ !$section ? 'border-[#ff1493] text-[#ff1493]' : 'border-transparent text-[#8b3a6e] hover:text-[#1a0d14]' }}">
        All Photos
        @if(!$section)<span class="ml-1 opacity-50 text-xs">({{ $items->total() }})</span>@endif
    </a>
    <a href="{{ route('admin.media.index', ['section' => 'featured']) }}"
       class="pb-3 text-sm border-b-2 transition-colors -mb-px
              {{ $section === 'featured' ? 'border-[#ff1493] text-[#ff1493]' : 'border-transparent text-[#8b3a6e] hover:text-[#1a0d14]' }}">
        ★ Featured
        @if($section === 'featured')<span class="ml-1 opacity-50 text-xs">({{ $items->total() }})</span>@endif
        <span class="ml-1 text-xs opacity-40">(Home &amp; Hero)</span>
    </a>
</div>

{{-- Flash message --}}
@if(session('success'))
<div class="mb-4 text-sm text-[#c71585] bg-[#fff0f5] border border-[#ffb3d9] rounded px-4 py-3">
    {{ session('success') }}
</div>
@endif

{{-- Grid --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
    @forelse($items as $item)
    <div class="group relative rounded overflow-hidden bg-[#ffe4f0] aspect-square"
         x-data="{ featured: {{ $item->is_featured ? 'true' : 'false' }} }">

        {{-- Thumbnail --}}
        @if($item->public_url)
        <img src="{{ $item->public_url }}"
             alt="{{ $item->title ?? '' }}"
             loading="lazy"
             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
        <div class="w-full h-full flex items-center justify-center text-[#8b3a6e] text-xs">Processing…</div>
        @endif

        {{-- Featured star --}}
        <button
            @click.prevent="
                fetch('{{ route('admin.media.feature', $item) }}', {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                }).then(r => r.json()).then(d => { featured = d.featured })
            "
            :class="featured
                ? 'bg-[#ff1493]/90 text-white'
                : 'bg-[#1a0d14]/50 text-[#1a0d14]/60 opacity-0 group-hover:opacity-100 hover:text-[#ff1493]'"
            class="absolute top-2 right-2 z-10 w-7 h-7 rounded flex items-center justify-center text-xs transition-all"
            title="Toggle featured (Home page)">
            ★
        </button>

        {{-- Hover overlay --}}
        <div class="absolute inset-0 bg-[#1a0d14]/70 opacity-0 group-hover:opacity-100 transition-opacity
                    flex flex-col items-center justify-end pb-4 px-3 gap-1">
            <p class="text-xs text-center text-[#1a0d14] font-medium line-clamp-2 w-full">
                {{ $item->title ?? '(untitled)' }}
            </p>
            @if($item->category)
            <span class="text-[10px] text-[#ff1493] tracking-widest uppercase">{{ $item->category->name }}</span>
            @endif
            <div class="flex items-center gap-3 mt-2">
                <a href="{{ route('admin.media.edit', $item) }}"
                   class="text-xs text-[#1a0d14]/70 hover:text-[#ff1493] transition-colors flex items-center gap-1">
                    <i class="fa-solid fa-pen-to-square"></i> Edit</a>
                <form method="POST" action="{{ route('admin.media.destroy', $item) }}"
                      onsubmit="return confirm('Delete this photograph permanently?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="text-xs text-red-500 hover:text-red-400 transition-colors flex items-center gap-1">
                        <i class="fa-solid fa-trash"></i> Delete</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-5 py-24 text-center text-[#8b3a6e]">
        @if($section === 'featured')
            No featured photos yet.
            Upload photos then click the <span class="text-[#ff1493]">★</span> star on any photo, or mark them Featured during upload.
        @else
            No media uploaded yet. <a href="{{ route('admin.media.create') }}" class="text-[#ff1493] hover:underline">Upload now →</a>
        @endif
    </div>
    @endforelse
</div>

@if($items->hasPages())
<div class="mt-10">{{ $items->links('vendor.pagination.tailwind') }}</div>
@endif
@endsection
