@extends('layouts.app')

@section('title', 'Gallery')
@section('meta_description', 'Browse the full photography collection.')

@section('content')
<section data-page="gallery" class="pt-28 pb-20 px-4 md:px-8 xl:px-16 relative">

    {{-- Three.js ambient dust background --}}
    <canvas id="gallery-bg" aria-hidden="true"></canvas>

    {{-- ── Header ── --}}
    <div class="mb-12 py-10 text-center relative" style="min-height:180px;">
        <h1 class="font-display text-5xl md:text-7xl tracking-tight mb-3 text-[#1a0d14]">
            <span class="text-[#ff1493]">G</span>allery
        </h1>

        {{-- Hello Kitty decoration --}}
        <img src="/images/kitty/kitty-kawaii.png"
             alt="" aria-hidden="true"
             class="kitty-deco hidden md:block"
             style="top:50%;right:2rem;transform:translateY(-50%);width:clamp(120px,12vw,180px);z-index:5;"
             loading="lazy" draggable="false">
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
    <div class="mt-16 flex justify-center" id="gallery-pagination">
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
    border: 1px solid #ffcde8;
    border-radius: 9999px;
    color: #c4607a;
    background: transparent;
    transition: all 0.25s ease;
}
.filter-pill:hover,
.filter-pill--active {
    border-color: #ff1493;
    color: #ff1493;
    background: rgba(255, 20, 147, 0.06);
    box-shadow: 0 0 12px rgba(255, 20, 147, 0.18);
}
</style>
@endsection
