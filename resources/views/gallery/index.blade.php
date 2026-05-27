@extends('layouts.app')

@section('title', 'Gallery')
@section('meta_description', 'Browse the full photography collection.')

@section('content')
<section data-page="gallery" class="pt-28 pb-20 px-4 md:px-8 xl:px-16">

    {{-- ── Header ── --}}
    <div class="mb-12 text-center">
        <h1 class="font-display text-5xl md:text-7xl tracking-tight mb-4 text-[#f5f0eb]">
            Gallery
        </h1>
        <p class="text-[#9e9e9e] text-sm tracking-widest uppercase">
            {{ $items->total() }} photographs
        </p>
    </div>

    {{-- ── Category filter ── --}}
    @if($categories->isNotEmpty())
    <div class="flex flex-wrap justify-center gap-3 mb-14" role="navigation" aria-label="Filter by category">
        <a href="{{ route('gallery.index') }}"
           class="filter-pill @if(!$categorySlug) filter-pill--active @endif">
            All
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('gallery.index', ['category' => $cat->slug]) }}"
           class="filter-pill @if($categorySlug === $cat->slug) filter-pill--active @endif">
            {{ $cat->name }}
            <span class="opacity-40 ml-1">({{ $cat->media_items_count }})</span>
        </a>
        @endforeach
    </div>
    @endif

    {{-- ── Asymmetric Grid ── --}}
    @if($items->isEmpty())
    <div class="text-center text-[#9e9e9e] py-32 text-lg">No photographs yet. Check back soon.</div>
    @else
    <div class="gallery-grid" id="gallery-grid">
        @foreach($items as $item)
        @include('components.photo-card', ['item' => $item])
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($items->hasPages())
    <div class="mt-16 flex justify-center">
        {{ $items->links('vendor.pagination.tailwind') }}
    </div>
    @endif
    @endif

</section>

<style>
.filter-pill {
    padding: 0.375rem 1.25rem;
    font-size: 0.75rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    border: 1px solid #2e2e2e;
    border-radius: 9999px;
    color: #9e9e9e;
    transition: all 0.2s ease;
}
.filter-pill:hover,
.filter-pill--active {
    border-color: #c9a84c;
    color: #c9a84c;
}
</style>
@endsection
