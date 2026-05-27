@extends('layouts.app')

@section('title', $story->effective_meta_title)
@section('meta_description', $story->meta_description ?? '')
@if($story->og_image_url)
@section('og_image', $story->og_image_url)
@endif

@section('content')

{{-- Reading progress bar --}}
<div id="reading-progress"
     class="fixed top-0 left-0 h-[2px] bg-[#c9a84c] z-50 w-full origin-left"
     aria-hidden="true"></div>

<article data-page="story" class="pt-24 pb-24">

    {{-- ── Parallax Banner ── --}}
    @if($story->banner_url)
    <div class="parallax-banner mb-16">
        <img src="{{ $story->banner_url }}"
             alt="{{ $story->title }}"
             class="parallax-img"
             decoding="async">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-[#0a0a0a]/30 to-[#0a0a0a]"></div>
    </div>
    @endif

    {{-- ── Header ── --}}
    <header class="max-w-3xl mx-auto px-6 md:px-8 mb-14">
        <p class="text-xs text-[#9e9e9e] tracking-widest uppercase mb-4">
            {{ $story->published_at?->format('F j, Y') }}
            &nbsp;·&nbsp;
            {{ $story->reading_time_minutes }} min read
        </p>
        <h1 class="font-display text-4xl md:text-6xl leading-tight text-[#f5f0eb] mb-0">
            {{ $story->title }}
        </h1>
    </header>

    {{-- ── Body ── --}}
    <div class="story-body prose prose-invert max-w-3xl mx-auto px-6 md:px-8">
        {!! $story->content !!}
    </div>

    {{-- ── Navigation between stories ── --}}
    @if($prev || $next)
    <nav class="max-w-3xl mx-auto px-6 md:px-8 mt-20 flex justify-between gap-8 border-t border-[#2e2e2e] pt-10"
         aria-label="Story navigation">

        <div class="flex-1 text-left">
            @if($prev)
            <a href="{{ route('stories.show', $prev->slug) }}"
               class="group text-sm text-[#9e9e9e] hover:text-[#c9a84c] transition-colors">
                <span class="block text-xs tracking-widest uppercase mb-2 text-[#2e2e2e]">← Previous</span>
                <span class="font-display text-lg group-hover:text-[#c9a84c] transition-colors">
                    {{ $prev->title }}
                </span>
            </a>
            @endif
        </div>

        <div class="flex-1 text-right">
            @if($next)
            <a href="{{ route('stories.show', $next->slug) }}"
               class="group text-sm text-[#9e9e9e] hover:text-[#c9a84c] transition-colors">
                <span class="block text-xs tracking-widest uppercase mb-2 text-[#2e2e2e]">Next →</span>
                <span class="font-display text-lg group-hover:text-[#c9a84c] transition-colors">
                    {{ $next->title }}
                </span>
            </a>
            @endif
        </div>

    </nav>
    @endif

</article>
@endsection
