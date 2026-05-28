@extends('layouts.app')

@section('title', $mediaItem->title ?? 'Photograph')
@section('meta_description', $mediaItem->description ? \Illuminate\Support\Str::limit(strip_tags($mediaItem->description), 160) : 'A photograph from the Munia collection.')
@if($mediaItem->public_url)
@section('og_image', $mediaItem->public_url)
@endif

@section('content')
<div data-page="gallery-show">

    {{-- ══ PHOTO HERO ══════════════════════════════════════════════════ --}}
    <section class="gs-hero">

        {{-- Grain overlay --}}
        <div class="gs-hero__grain" aria-hidden="true"></div>

        {{-- Photo --}}
        <div class="gs-hero__frame">
            <picture>
                @if($mediaItem->avif_url)
                <source srcset="{{ $mediaItem->avif_url }}" type="image/avif">
                @endif
                @if($mediaItem->webp_url)
                <source srcset="{{ $mediaItem->webp_url }}" type="image/webp">
                @endif
                <img src="{{ $mediaItem->public_url }}"
                     @if($mediaItem->srcset) srcset="{{ $mediaItem->srcset }}" sizes="100vw" @endif
                     alt="{{ $mediaItem->title ?? '' }}"
                     class="gs-hero__img"
                     loading="eager"
                     decoding="async">
            </picture>
        </div>

        {{-- Bottom fade into page bg --}}
        <div class="gs-hero__fade" aria-hidden="true"></div>

    </section>

    {{-- ══ METADATA ═══════════════════════════════════════════════════ --}}
    <section class="gs-meta">

        {{-- Back + breadcrumb --}}
        <a href="{{ route('gallery.index') }}" class="gs-back">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <path d="M12 7H2M6 2L1 7l5 5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>All Photographs</span>
        </a>

        @if($mediaItem->title)
        <h1 class="gs-title">{{ $mediaItem->title }}</h1>
        @endif

        @if($mediaItem->category)
        <p class="gs-category">
            <a href="{{ route('gallery.index', ['category' => $mediaItem->category->slug]) }}" class="gs-category__link">
                {{ $mediaItem->category->name }}
            </a>
        </p>
        @endif

        @if($mediaItem->description)
        <div class="gs-description">
            {!! \Illuminate\Support\Str::markdown(strip_tags($mediaItem->description)) !!}
        </div>
        @endif

        {{-- Tags --}}
        @if($mediaItem->tags->isNotEmpty())
        <div class="gs-tags">
            @foreach($mediaItem->tags as $tag)
            <a href="{{ route('gallery.index', ['tag' => $tag->slug]) }}" class="gs-tag">
                # {{ $tag->name }}
            </a>
            @endforeach
        </div>
        @endif

        {{-- EXIF data --}}
        @php
            $exif = array_filter([
                'Camera'       => $mediaItem->camera_display ?: null,
                'Lens'         => $mediaItem->lens,
                'Aperture'     => $mediaItem->aperture,
                'Shutter'      => $mediaItem->shutter_speed,
                'ISO'          => $mediaItem->iso ? 'ISO ' . $mediaItem->iso : null,
                'Focal Length' => $mediaItem->focal_length,
                'Date'         => $mediaItem->taken_at?->format('F j, Y'),
                'Location'     => $mediaItem->taken_at_location,
            ]);
        @endphp
        @if($exif)
        <dl class="gs-exif">
            @foreach($exif as $label => $value)
            <div class="gs-exif__item">
                <dt class="gs-exif__label">{{ $label }}</dt>
                <dd class="gs-exif__value">{{ $value }}</dd>
            </div>
            @endforeach
        </dl>
        @endif

    </section>

    {{-- ══ RELATED ══════════════════════════════════════════════════════ --}}
    @if($related->isNotEmpty())
    <section class="gs-related">

        <div class="hp-section-head">
            <div class="hp-sec-label">
                <div class="hp-sec-label__line"></div>
                <span>More from this series</span>
            </div>
            <a href="{{ route('gallery.index') }}" class="hp-sec-link">
                All photographs
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                    <path d="M2 6h8M7 3l3 3-3 3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                </svg>
            </a>
        </div>

        <div class="gallery-grid px-[clamp(1.5rem,6vw,8rem)]">
            @foreach($related as $item)
            @include('components.photo-card', ['item' => $item])
            @endforeach
        </div>

    </section>
    @endif

    {{-- Hello Kitty corner --}}
    <img src="/images/kitty/kitty-full.png"
         alt="" aria-hidden="true" draggable="false"
         class="hidden md:block fixed bottom-0 right-0 pointer-events-none select-none z-10"
         style="width:clamp(140px,12vw,200px);opacity:0.45;image-rendering:-webkit-optimize-contrast;">

</div>
@endsection
