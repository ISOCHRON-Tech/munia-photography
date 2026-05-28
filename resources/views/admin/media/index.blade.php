@extends('layouts.admin')
@section('title', $section === 'featured' ? 'Featured Photos' : 'Gallery')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-display text-[#f5f0eb]">
            {{ $section === 'featured' ? 'Featured Photos' : 'Gallery' }}
        </h1>
        <p class="text-xs text-[#9e9e9e] mt-1">
            @if($section === 'featured')
                Photos shown in the <span class="text-[#c9a84c]">Home page hero &amp; selected work</span> grid
            @else
                All uploaded photographs — visible in the public Gallery
            @endif
        </p>
    </div>
    <a href="{{ route('admin.media.create') }}"
       class="px-5 py-2 bg-[#c9a84c] text-[#0a0a0a] text-sm font-medium rounded hover:bg-[#b8943e] transition-colors">
        + Upload
    </a>
</div>

{{-- Section tabs --}}
<div class="flex gap-6 border-b border-[#2e2e2e] mb-6">
    <a href="{{ route('admin.media.index') }}"
       class="pb-3 text-sm border-b-2 transition-colors -mb-px
              {{ !$section ? 'border-[#c9a84c] text-[#c9a84c]' : 'border-transparent text-[#9e9e9e] hover:text-[#f5f0eb]' }}">
        All Photos
        @if(!$section)<span class="ml-1 opacity-50 text-xs">({{ $items->total() }})</span>@endif
    </a>
    <a href="{{ route('admin.media.index', ['section' => 'featured']) }}"
       class="pb-3 text-sm border-b-2 transition-colors -mb-px
              {{ $section === 'featured' ? 'border-[#c9a84c] text-[#c9a84c]' : 'border-transparent text-[#9e9e9e] hover:text-[#f5f0eb]' }}">
        ★ Featured
        @if($section === 'featured')<span class="ml-1 opacity-50 text-xs">({{ $items->total() }})</span>@endif
        <span class="ml-1 text-xs opacity-40">(Home &amp; Hero)</span>
    </a>
</div>

{{-- Flash message --}}
@if(session('success'))
<div class="mb-4 text-sm text-[#c9a84c] bg-[#c9a84c]/10 border border-[#c9a84c]/20 rounded px-4 py-3">
    {{ session('success') }}
</div>
@endif

{{-- Grid --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
    @forelse($items as $item)
    <div class="group relative rounded overflow-hidden bg-[#1a1a1a] aspect-square"
         x-data="{ featured: {{ $item->is_featured ? 'true' : 'false' }} }">

        {{-- Thumbnail --}}
        @if($item->public_url)
        <img src="{{ $item->public_url }}"
             alt="{{ $item->title ?? '' }}"
             loading="lazy"
             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
        <div class="w-full h-full flex items-center justify-center text-[#9e9e9e] text-xs">Processing…</div>
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
                ? 'bg-[#c9a84c]/90 text-[#0a0a0a]'
                : 'bg-black/50 text-white/60 opacity-0 group-hover:opacity-100 hover:text-[#c9a84c]'"
            class="absolute top-2 right-2 z-10 w-7 h-7 rounded flex items-center justify-center text-xs transition-all"
            title="Toggle featured (Home page)">
            ★
        </button>

        {{-- Hover overlay --}}
        <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity
                    flex flex-col items-center justify-end pb-4 px-3 gap-1">
            <p class="text-xs text-center text-white font-medium line-clamp-2 w-full">
                {{ $item->title ?? '(untitled)' }}
            </p>
            @if($item->category)
            <span class="text-[10px] text-[#c9a84c] tracking-widest uppercase">{{ $item->category->name }}</span>
            @endif
            <div class="flex items-center gap-3 mt-2">
                <a href="{{ route('admin.media.edit', $item) }}"
                   class="text-xs text-white/70 hover:text-[#c9a84c] transition-colors">Edit</a>
                <form method="POST" action="{{ route('admin.media.destroy', $item) }}"
                      onsubmit="return confirm('Delete this photograph permanently?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="text-xs text-red-500 hover:text-red-400 transition-colors">Delete</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-5 py-24 text-center text-[#9e9e9e]">
        @if($section === 'featured')
            No featured photos yet.
            Upload photos then click the <span class="text-[#c9a84c]">★</span> star on any photo, or mark them Featured during upload.
        @else
            No media uploaded yet. <a href="{{ route('admin.media.create') }}" class="text-[#c9a84c] hover:underline">Upload now →</a>
        @endif
    </div>
    @endforelse
</div>

@if($items->hasPages())
<div class="mt-10">{{ $items->links('vendor.pagination.tailwind') }}</div>
@endif
@endsection
