{{--
    Photo card component.
    Uses LQIP blur placeholder + lazy loading + lightbox data attributes.
    @param App\Models\MediaItem $item
--}}
@php
    $lqip = null;
    if ($item->lqip_path) {
        try {
            $lqip = \Illuminate\Support\Facades\Storage::disk('r2')->get($item->lqip_path);
        } catch (\Throwable) {}
    }
    $ar = $item->aspect_ratio ?? 66.67; // default ~3:2
@endphp

<article class="gallery-item photo-card"
         data-full="{{ $item->public_url }}"
         data-webp="{{ $item->webp_url }}"
         data-avif="{{ $item->avif_url }}"
         data-alt="{{ e($item->title) }}"
         data-camera="{{ e($item->camera_display) }}"
         data-iso="{{ e($item->iso) }}"
         data-aperture="{{ e($item->aperture) }}"
         data-shutter="{{ e($item->shutter_speed) }}"
         role="button"
         tabindex="0"
         aria-label="View {{ $item->title ?? 'photograph' }}">

    {{-- Aspect-ratio wrapper with LQIP background --}}
    <div class="lqip-wrap w-full"
         style="padding-top: {{ $ar }}%;
                @if($lqip) background-image: url('{{ $lqip }}'); background-size: cover; @endif">

        <div class="absolute inset-0">
            {{-- Responsive <picture> element --}}
            <picture>
                @if($item->avif_url)
                <source srcset="{{ $item->avif_url }}" type="image/avif">
                @endif
                @if($item->webp_url)
                <source srcset="{{ $item->webp_url }}" type="image/webp">
                @endif
                <img
                    src="{{ $lqip ?? 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' }}"
                    data-src="{{ $item->public_url }}"
                    @if($item->srcset) data-srcset="{{ $item->srcset }}" data-sizes="(max-width:768px) 100vw, 50vw" @endif
                    alt="{{ $item->title ?? '' }}"
                    width="{{ $item->width }}"
                    height="{{ $item->height }}"
                    loading="lazy"
                    decoding="async"
                    class="absolute inset-0 w-full h-full object-cover"
                >
            </picture>
        </div>

        {{-- Hover overlay with metadata --}}
        <div class="overlay absolute inset-0 p-4 flex flex-col justify-end">
            @if($item->title)
            <p class="text-white font-medium text-sm leading-snug mb-1 translate-y-2 group-hover:translate-y-0 transition-transform">
                {{ $item->title }}
            </p>
            @endif
            @if($item->camera_display || $item->aperture)
            <p class="text-white/50 text-xs tracking-wide">
                {{ implode(' · ', array_filter([$item->camera_display ?: null, $item->aperture, $item->iso ? 'ISO '.$item->iso : null])) }}
            </p>
            @endif
        </div>
    </div>
</article>
