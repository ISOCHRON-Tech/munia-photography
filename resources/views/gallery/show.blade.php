@extends('layouts.app')

@section('title', $mediaItem->title ?? 'Photograph')

@section('content')
<article class="pt-24 pb-20">

    {{-- Full-bleed hero --}}
    <div class="relative w-full bg-[#0a0a0a] flex items-center justify-center min-h-[60vh]">
        <picture>
            @if($mediaItem->avif_url)
            <source srcset="{{ $mediaItem->avif_url }}" type="image/avif">
            @endif
            @if($mediaItem->webp_url)
            <source srcset="{{ $mediaItem->webp_url }}" type="image/webp">
            @endif
            <img
                src="{{ $mediaItem->public_url }}"
                @if($mediaItem->srcset) srcset="{{ $mediaItem->srcset }}" sizes="100vw" @endif
                alt="{{ $mediaItem->title ?? '' }}"
                width="{{ $mediaItem->width }}"
                height="{{ $mediaItem->height }}"
                class="max-h-[85vh] max-w-full mx-auto object-contain"
                decoding="async"
            >
        </picture>
    </div>

    {{-- Metadata strip --}}
    <div class="max-w-3xl mx-auto px-6 md:px-8 mt-10 space-y-6">

        @if($mediaItem->title)
        <h1 class="font-display text-3xl md:text-4xl text-[#f5f0eb]">{{ $mediaItem->title }}</h1>
        @endif

        @if($mediaItem->description)
        <p class="text-[#9e9e9e] leading-relaxed">{{ $mediaItem->description }}</p>
        @endif

        {{-- EXIF grid --}}
        @php
            $exif = array_filter([
                'Camera'        => $mediaItem->camera_display ?: null,
                'Lens'          => $mediaItem->lens,
                'Aperture'      => $mediaItem->aperture,
                'Shutter'       => $mediaItem->shutter_speed,
                'ISO'           => $mediaItem->iso,
                'Focal Length'  => $mediaItem->focal_length,
                'Taken'         => $mediaItem->taken_at?->format('F j, Y'),
                'Location'      => $mediaItem->taken_at_location,
            ]);
        @endphp
        @if($exif)
        <dl class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-4 text-sm border-t border-[#2e2e2e] pt-6">
            @foreach($exif as $label => $value)
            <div>
                <dt class="text-[#9e9e9e] text-xs tracking-widest uppercase mb-1">{{ $label }}</dt>
                <dd class="text-[#f5f0eb]">{{ $value }}</dd>
            </div>
            @endforeach
        </dl>
        @endif

        {{-- Tags --}}
        @if($mediaItem->tags->isNotEmpty())
        <div class="flex flex-wrap gap-2 pt-2">
            @foreach($mediaItem->tags as $tag)
            <a href="{{ route('gallery.index', ['tag' => $tag->slug]) }}"
               class="text-xs border border-[#2e2e2e] text-[#9e9e9e] px-3 py-1 rounded-full hover:border-[#c9a84c] hover:text-[#c9a84c] transition-colors">
                #{{ $tag->name }}
            </a>
            @endforeach
        </div>
        @endif

        <a href="{{ route('gallery.index') }}"
           class="inline-flex items-center gap-2 text-sm text-[#9e9e9e] hover:text-[#c9a84c] transition-colors mt-4">
            ← Back to Gallery
        </a>
    </div>

    {{-- Related --}}
    @if($related->isNotEmpty())
    <div class="max-w-screen-xl mx-auto px-4 md:px-8 mt-20">
        <h2 class="text-xs tracking-widest uppercase text-[#9e9e9e] mb-8">More from this series</h2>
        <div class="gallery-grid" data-page="gallery">
            @foreach($related as $item)
            @include('components.photo-card', ['item' => $item])
            @endforeach
        </div>
    </div>
    @endif

</article>
@endsection
