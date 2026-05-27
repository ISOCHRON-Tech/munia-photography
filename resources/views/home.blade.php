@extends('layouts.app')

@section('title', 'Monea — Photography & Visual Stories')
@section('meta_description', 'A photography portfolio and story journal — light, silence, and the moments between.')

@section('content')
<div data-page="home">

{{-- ─── Preloader (home-only) ─────────────────────────────────── --}}
<div id="hp-preloader" aria-hidden="true">
    <canvas id="hp-preloader-canvas"></canvas>
    <div id="hp-preloader-content">
        <div id="hp-preloader-letters">
            <span class="hp-pre-letter">M</span>
            <span class="hp-pre-letter">O</span>
            <span class="hp-pre-letter">N</span>
            <span class="hp-pre-letter">E</span>
            <span class="hp-pre-letter">A</span>
        </div>
        <div id="hp-preloader-line"><div id="hp-preloader-fill"></div></div>
        <p id="hp-preloader-sub">Photography &amp; Visual Stories</p>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════════════════ --}}
<section class="hp-hero" id="hp-hero" aria-label="Introduction">


    {{-- Background photo --}}
    @if($featured->isNotEmpty() && $featured->first()->public_url)
    <div class="hp-hero__bg" id="hp-hero-bg">
        <img src="{{ $featured->first()->public_url }}"
             alt=""
             class="hp-hero__bg-img"
             id="hp-hero-bg-img"
             loading="eager">
    </div>
    @endif

    <div class="hp-hero__overlay" aria-hidden="true"></div>
    <div class="hp-hero__grain"   aria-hidden="true"></div>
    <div class="hp-hero__rules"   aria-hidden="true">
        <span></span><span></span><span></span>
    </div>

    <div class="hp-hero__body">
        <p class="hp-hero__eyebrow" id="hp-eyebrow">
            <span>Photography</span>
            <span class="hp-dot" aria-hidden="true"></span>
            <span>Visual Stories</span>
        </p>

        <h1 class="hp-hero__name" id="hp-name" aria-label="Monea">
            <span class="hp-hero__name-m" aria-hidden="true">M</span><span class="hp-hero__name-rest" aria-hidden="true">onea</span>
        </h1>

        <p class="hp-hero__tagline" id="hp-tagline">
            Light &ensp;·&ensp; Silence &ensp;·&ensp; Moment
        </p>

        <div class="hp-hero__actions" id="hp-actions">
            <a href="{{ route('gallery.index') }}" class="hp-btn-primary">
                <span>View Gallery</span>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M2 8h12M9 3l5 5-5 5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            @if($totalPhotos > 0)
            <span class="hp-hero__stat">
                <span class="hp-hero__stat-num">{{ $totalPhotos }}</span>
                <span>{{ Str::plural('photograph', $totalPhotos) }}</span>
            </span>
            @endif
        </div>
    </div>

    <div class="hp-hero__scroll" id="hp-scroll" aria-hidden="true">
        <div class="hp-hero__scroll-track">
            <div class="hp-hero__scroll-thumb"></div>
        </div>
        <span>scroll</span>
    </div>

</section>


{{-- ══════════════════════════════════════════════════════════════
     SELECTED WORK
══════════════════════════════════════════════════════════════ --}}
@if($featured->isNotEmpty())
<section class="hp-work" id="hp-work" aria-label="Selected work">

    <div class="hp-section-head">
        <div class="hp-sec-label" data-hp-reveal>
            <div class="hp-sec-label__line"></div>
            <span>Selected Work</span>
        </div>
        <a href="{{ route('gallery.index') }}" class="hp-sec-link" data-hp-reveal>
            All photographs
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                <path d="M2 6h8M7 3l3 3-3 3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
            </svg>
        </a>
    </div>

    <div class="hp-work-grid">
        @foreach($featured as $i => $item)
        <a href="{{ route('gallery.show', ['mediaItem' => $item->id]) }}"
           class="hp-work-card"
           data-index="{{ $i }}"
           data-hp-card
           aria-label="{{ $item->title ?? 'Photograph ' . ($i + 1) }}">

            <div class="hp-work-card__inner" style="--ar: {{ $item->aspect_ratio ?? '66.67%' }}">

                @php
                    $lqip = null;
                    if ($item->lqip_path) {
                        try { $lqip = \Illuminate\Support\Facades\Storage::disk('r2')->get($item->lqip_path); } catch (\Throwable) {}
                    }
                @endphp
                @if($lqip)
                <div class="hp-work-card__lqip" style="background-image: url('{{ $lqip }}')"></div>
                @endif

                <picture>
                    @if($item->avif_path)<source type="image/avif" srcset="{{ $item->avif_url }}">@endif
                    @if($item->webp_path)<source type="image/webp" srcset="{{ $item->webp_url }}">@endif
                    <img src="{{ $item->public_url ?? '' }}"
                         alt="{{ $item->title ?? '' }}"
                         loading="{{ $i < 2 ? 'eager' : 'lazy' }}"
                         class="hp-work-card__img">
                </picture>

                <div class="hp-work-card__overlay" aria-hidden="true">
                    @if($item->title)<p class="hp-work-card__title">{{ $item->title }}</p>@endif
                    @if($item->category)<p class="hp-work-card__cat">{{ $item->category->name }}</p>@endif
                </div>

            </div>
            <span class="hp-work-card__num" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>

        </a>
        @endforeach
    </div>

</section>
@endif


{{-- ══════════════════════════════════════════════════════════════
     MARQUEE TICKER
══════════════════════════════════════════════════════════════ --}}
<div class="hp-marquee" aria-hidden="true">
    <div class="hp-marquee__track">
        @for($j = 0; $j < 8; $j++)
        <span class="hp-marquee__word">Photography</span>
        <span class="hp-marquee__sep">·</span>
        <span class="hp-marquee__word">Visual Stories</span>
        <span class="hp-marquee__sep">·</span>
        <span class="hp-marquee__word">Light &amp; Shadow</span>
        <span class="hp-marquee__sep">·</span>
        <span class="hp-marquee__word">Monea · 2026</span>
        <span class="hp-marquee__sep">·</span>
        @endfor
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════
     STORIES / JOURNAL
══════════════════════════════════════════════════════════════ --}}
@if($stories->isNotEmpty())
@php $firstStory = $stories->first(); $otherStories = $stories->slice(1); @endphp
<section class="hp-stories" id="hp-stories" aria-label="Journal">

    <div class="hp-section-head">
        <div class="hp-sec-label" data-hp-reveal>
            <div class="hp-sec-label__line"></div>
            <span>From the Journal</span>
        </div>
        <a href="{{ route('stories.index') }}" class="hp-sec-link" data-hp-reveal>
            All stories
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                <path d="M2 6h8M7 3l3 3-3 3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
            </svg>
        </a>
    </div>

    {{-- Featured story --}}
    <a href="{{ $firstStory instanceof \App\Models\Story ? route('stories.show', $firstStory->slug) : route('stories.index') }}" class="hp-story-hero" data-hp-reveal>
        <div class="hp-story-hero__thumb">
            @php $heroBannerUrl = $firstStory->banner_url ?? null; @endphp
            @if($heroBannerUrl)
            <img src="{{ $heroBannerUrl }}"
                 alt="{{ $firstStory->title }}"
                 loading="lazy"
                 class="hp-story-hero__img">
            @else
            <div class="hp-story-hero__placeholder"></div>
            @endif
        </div>
        <div class="hp-story-hero__body">
            <p class="hp-story-meta">
                {{ $firstStory->published_at->format('M j, Y') }}
                @if($firstStory->reading_time_minutes)
                <span aria-hidden="true">·</span> {{ $firstStory->reading_time_minutes }} min read
                @endif
            </p>
            <h2 class="hp-story-hero__title">{{ $firstStory->title }}</h2>
            @if($firstStory->meta_description)
            <p class="hp-story-hero__excerpt">{{ Str::limit($firstStory->meta_description, 180) }}</p>
            @endif
            <span class="hp-read-more">
                Read story
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                </svg>
            </span>
        </div>
    </a>

    {{-- Secondary stories --}}
    @if($otherStories->isNotEmpty())
    <div class="hp-stories-grid">
        @foreach($otherStories as $story)
        <a href="{{ $story instanceof \App\Models\Story ? route('stories.show', $story->slug) : route('stories.index') }}" class="hp-story-card" data-hp-reveal>
            <div class="hp-story-card__thumb">
                @php $cardBannerUrl = $story->banner_url ?? null; @endphp
                @if($cardBannerUrl)
                <img src="{{ $cardBannerUrl }}"
                     alt="{{ $story->title }}"
                     loading="lazy"
                     class="hp-story-card__img">
                @else
                <div class="hp-story-card__placeholder"></div>
                @endif
            </div>
            <div class="hp-story-card__body">
                <p class="hp-story-meta">{{ $story->published_at->format('M j, Y') }}</p>
                <h3 class="hp-story-card__title">{{ $story->title }}</h3>
                @if($story->meta_description)
                <p class="hp-story-card__excerpt">{{ Str::limit($story->meta_description, 100) }}</p>
                @endif
                <span class="hp-read-more">
                    Read
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path d="M2 6h8M7 3l3 3-3 3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                    </svg>
                </span>
            </div>
        </a>
        @endforeach
    </div>
    @endif

</section>
@endif


{{-- ══════════════════════════════════════════════════════════════
     CONTACT
══════════════════════════════════════════════════════════════ --}}
<section class="hp-contact" id="hp-contact" aria-label="Contact">

    <div class="hp-contact__bg" aria-hidden="true">
        <div class="hp-contact__glow"></div>
    </div>

    <div class="hp-contact__inner">
        <p class="hp-contact__eyebrow" data-hp-reveal>— Get in touch</p>
        <h2 class="hp-contact__heading" data-hp-reveal>
            Let's create<br><em>together.</em>
        </h2>
        <p class="hp-contact__sub" data-hp-reveal>
            Available for commissions, editorial work, and private collections.
        </p>
        <a href="mailto:hello@monea.photo"
           class="hp-contact__email"
           data-hp-reveal>
            hello@monea.photo
        </a>
        <div class="hp-contact__rule" data-hp-reveal aria-hidden="true"></div>
    </div>

</section>

</div>{{-- /data-page="home" --}}
@endsection
