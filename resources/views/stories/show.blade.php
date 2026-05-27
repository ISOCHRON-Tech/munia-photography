@extends('layouts.app')

@section('title', $story->effective_meta_title)
@section('meta_description', $story->meta_description ?? '')
@if($story->og_image_url)
@section('og_image', $story->og_image_url)
@endif

@section('content')

{{-- Reading progress bar --}}
<div id="reading-progress"
     class="fixed top-0 left-0 h-[2px] bg-[#ff1493] z-50 w-full origin-left"
     aria-hidden="true"></div>

<article data-page="story" class="pt-24 pb-24">

    {{-- ── Parallax Banner ── --}}
    @if($story->banner_url)
    <div class="parallax-banner mb-16">
        <img src="{{ $story->banner_url }}"
             alt="{{ $story->title }}"
             class="parallax-img"
             decoding="async">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-[#fff0f5]/30 to-[#fff0f5]"></div>
    </div>
    @endif

    {{-- ── Header ── --}}
    <header class="max-w-3xl mx-auto px-6 md:px-8 mb-14">
        <p class="text-xs text-[#c4607a] tracking-widest uppercase mb-4">
            {{ $story->published_at?->format('F j, Y') }}
            &nbsp;·&nbsp;
            {{ $story->reading_time_minutes }} min read
        </p>
        <h1 class="font-display text-4xl md:text-6xl leading-tight text-[#1a0d14] mb-0">
            {{ $story->title }}
        </h1>
    </header>

    {{-- ── Body ── --}}
    <div class="story-body prose prose-invert max-w-3xl mx-auto px-6 md:px-8">
        {!! $story->content !!}
    </div>

    {{-- ── Navigation between stories ── --}}
    @if($prev || $next)
    <nav class="max-w-3xl mx-auto px-6 md:px-8 mt-20 flex justify-between gap-8 border-t border-[#ffcde8] pt-10"
         aria-label="Story navigation">

        <div class="flex-1 text-left">
            @if($prev)
            <a href="{{ route('stories.show', $prev->slug) }}"
               class="group text-sm text-[#c4607a] hover:text-[#ff1493] transition-colors">
                <span class="block text-xs tracking-widest uppercase mb-2 text-[#c4607a]">← Previous</span>
                <span class="font-display text-lg group-hover:text-[#ff1493] transition-colors">
                    {{ $prev->title }}
                </span>
            </a>
            @endif
        </div>

        <div class="flex-1 text-right">
            @if($next)
            <a href="{{ route('stories.show', $next->slug) }}"
               class="group text-sm text-[#c4607a] hover:text-[#ff1493] transition-colors">
                <span class="block text-xs tracking-widest uppercase mb-2 text-[#c4607a]">Next →</span>
                <span class="font-display text-lg group-hover:text-[#ff1493] transition-colors">
                    {{ $next->title }}
                </span>
            </a>
            @endif
        </div>

    </nav>
    @endif

    {{-- ── Hello Kitty decoration ── --}}
    <div class="hidden md:flex justify-end max-w-3xl mx-auto px-6 md:px-8 mt-12 mb-4" aria-hidden="true">
        <img src="/images/kitty/kitty-head.png"
             alt=""
             class="w-28 opacity-80"
             style="pointer-events:none;user-select:none;-webkit-user-drag:none;"
             loading="lazy" draggable="false">
    </div>

</article>
@endsection
