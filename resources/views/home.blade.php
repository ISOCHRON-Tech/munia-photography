@extends('layouts.app')

@section('title', 'munia')
@section('meta_description', 'A photography portfolio and story journal — light, silence, and the moments between.')

@section('content')
<div data-page="home">

{{-- ══════════════════════════════════════════════════════════════
     HERO — full viewport
══════════════════════════════════════════════════════════════ --}}
<section class="hero-section" aria-label="Introduction">

    {{-- Film-grain noise overlay --}}
    <div class="hero-grain" aria-hidden="true"></div>

    {{-- Vertical rule lines --}}
    <div class="hero-rules" aria-hidden="true">
        <span></span><span></span><span></span>
    </div>

    {{-- Main copy --}}
    <div class="hero-body">

        {{-- Eyebrow --}}
        <p class="hero-eyebrow" id="hero-eyebrow">
            <span>Photography</span>
            <span class="hero-eyebrow-dot"></span>
            <span>Visual Stories</span>
        </p>

        {{-- Name --}}
        <h1 class="hero-name" id="hero-name" aria-label="munia">
            <span class="hero-name-m" aria-hidden="true">M</span>
            <span class="hero-name-rest" aria-hidden="true">unia</span>
        </h1>

        {{-- Tagline --}}
        <p class="hero-tagline" id="hero-tagline">
            Light.&ensp;Silence.&ensp;Moment.
        </p>

        {{-- CTA row --}}
        <div class="hero-cta" id="hero-cta">
            <a href="{{ route('gallery.index') }}" class="hero-btn-primary">
                <span>View Gallery</span>
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true">
                    <path d="M3 9h12M10 4l5 5-5 5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            @if($totalPhotos > 0)
            <span class="hero-count">
                {{ $totalPhotos }} {{ Str::plural('photograph', $totalPhotos) }}
            </span>
            @endif
        </div>

    </div>

    {{-- Scroll indicator --}}
    <div class="hero-scroll" id="hero-scroll" aria-hidden="true">
        <div class="hero-scroll-line"></div>
        <span>scroll</span>
    </div>

    {{-- Bottom left — horizontal rule --}}
    <div class="hero-bottom-rule" id="hero-rule" aria-hidden="true"></div>

</section>


{{-- ══════════════════════════════════════════════════════════════
     SELECTED WORK
══════════════════════════════════════════════════════════════ --}}
@if($featured->isNotEmpty())
<section class="work-section" aria-label="Selected work">

    {{-- Section label --}}
    <div class="section-label-wrap">
        <span class="section-label reveal-item">Selected Work</span>
        <a href="{{ route('gallery.index') }}" class="section-link reveal-item">
            All photographs
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    </div>

    {{-- Asymmetric photo grid --}}
    <div class="work-grid">
        @foreach($featured as $i => $item)
        <a href="{{ route('gallery.show', $item) }}"
           class="work-card reveal-item"
           data-index="{{ $i }}"
           aria-label="{{ $item->title ?? 'Photo ' . ($i+1) }}">

            {{-- Aspect wrapper --}}
            <div class="work-card-inner"
                 style="--ar: {{ $item->aspect_ratio ?? '66.67%' }}">

                {{-- LQIP blur-up --}}
                @php
                    $lqip = null;
                    if ($item->lqip_path) {
                        try {
                            $lqip = \Illuminate\Support\Facades\Storage::disk('r2')->get($item->lqip_path);
                        } catch (\Throwable) {}
                    }
                @endphp
                @if($lqip)
                <div class="work-lqip" style="background-image: url('{{ $lqip }}')"></div>
                @endif

                {{-- Picture --}}
                <picture>
                    @if($item->avif_path)
                    <source type="image/avif"
                            srcset="{{ Storage::url($item->avif_path) }}">
                    @endif
                    @if($item->webp_path)
                    <source type="image/webp"
                            srcset="{{ Storage::url($item->webp_path) }}">
                    @endif
                    <img src="{{ Storage::url($item->original_path) }}"
                         alt="{{ $item->title ?? '' }}"
                         loading="{{ $i < 2 ? 'eager' : 'lazy' }}"
                         class="work-img">
                </picture>

                {{-- Overlay --}}
                <div class="work-overlay" aria-hidden="true">
                    @if($item->title)
                    <p class="work-title">{{ $item->title }}</p>
                    @endif
                    @if($item->category)
                    <p class="work-category">{{ $item->category->name }}</p>
                    @endif
                </div>
            </div>

            {{-- Index number --}}
            <span class="work-num" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>

        </a>
        @endforeach
    </div>

</section>
@endif


{{-- ══════════════════════════════════════════════════════════════
     DIVIDER — full-width rule
══════════════════════════════════════════════════════════════ --}}
<div class="page-divider reveal-item" aria-hidden="true"></div>


{{-- ══════════════════════════════════════════════════════════════
     FROM THE JOURNAL
══════════════════════════════════════════════════════════════ --}}
@if($stories->isNotEmpty())
<section class="journal-section" aria-label="Latest stories">

    <div class="section-label-wrap">
        <span class="section-label reveal-item">From the Journal</span>
        <a href="{{ route('stories.index') }}" class="section-link reveal-item">
            All stories
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    </div>

    <div class="journal-grid">
        @foreach($stories as $i => $story)
        <a href="{{ route('stories.show', $story->slug) }}"
           class="journal-card reveal-item"
           style="--delay: {{ $i * 0.12 }}s">

            {{-- Banner --}}
            @if($story->banner_webp_path || $story->banner_path)
            <div class="journal-thumb">
                <picture>
                    @if($story->banner_webp_path)
                    <source type="image/webp" srcset="{{ Storage::url($story->banner_webp_path) }}">
                    @endif
                    <img src="{{ Storage::url($story->banner_path) }}"
                         alt="{{ $story->title }}"
                         loading="lazy"
                         class="journal-thumb-img">
                </picture>
            </div>
            @else
            <div class="journal-thumb journal-thumb--empty" aria-hidden="true">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <rect width="32" height="32" rx="1" fill="none"/>
                    <path d="M4 22l8-8 5 5 4-4 7 7" stroke="#2e2e2e" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="11" cy="11" r="2" stroke="#2e2e2e" stroke-width="1.5"/>
                </svg>
            </div>
            @endif

            {{-- Text --}}
            <div class="journal-body">
                <p class="journal-meta">
                    {{ $story->published_at->format('M j, Y') }}
                    @if($story->reading_time_minutes)
                    <span class="mx-2 opacity-30">/</span>
                    {{ $story->reading_time_minutes }} min read
                    @endif
                </p>
                <h3 class="journal-title">{{ $story->title }}</h3>
                @if($story->meta_description)
                <p class="journal-excerpt">{{ Str::limit($story->meta_description, 110) }}</p>
                @endif
                <span class="journal-read-more">
                    Read
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                        <path d="M2 6h8M7 3l3 3-3 3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </div>
        </a>
        @endforeach
    </div>

</section>
@endif


{{-- ══════════════════════════════════════════════════════════════
     BOTTOM CTA STRIP
══════════════════════════════════════════════════════════════ --}}
<section class="cta-strip reveal-item" aria-label="Call to action">
    <p class="cta-strip-label">The complete archive</p>
    <a href="{{ route('gallery.index') }}" class="cta-strip-link">
        Explore every photograph
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M3 10h14M11 4l6 6-6 6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </a>
</section>

</div>{{-- /data-page="home" --}}
@endsection
