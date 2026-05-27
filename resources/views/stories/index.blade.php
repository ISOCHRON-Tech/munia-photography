@extends('layouts.app')

@section('title', 'Stories')
@section('meta_description', 'Field notes, travel stories, and essays on photography.')

@section('content')
<section data-page="stories" class="pt-28 pb-20 px-4 md:px-8 xl:px-16">

    <header class="mb-16 text-center relative overflow-hidden" style="background-color:#fff0f5;">
        {{-- Three.js wave mesh background --}}
        <canvas id="stories-bg" aria-hidden="true"></canvas>

        <div class="relative z-10 py-12">
            <h1 class="font-display text-5xl md:text-7xl tracking-tight mb-4">Stories</h1>
            <p class="text-[#c4607a] text-sm tracking-widest uppercase">Field notes &amp; essays</p>
        </div>

        {{-- Hello Kitty fairy decoration --}}
        <img src="/images/kitty/kitty-colorful.png"
             alt="" aria-hidden="true"
             class="kitty-deco hidden md:block"
             style="bottom:0;right:4%;width:clamp(120px,12vw,180px);z-index:2;background-color:#fff0f5;"
             loading="lazy" draggable="false">
    </header>

    @if($stories->isEmpty())
    <div class="text-center text-[#c4607a] py-32 text-lg">No stories published yet.</div>
    @else
    <div class="max-w-screen-xl mx-auto grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        @foreach($stories as $story)
        <a href="{{ route('stories.show', $story->slug) }}"
           class="story-card group block rounded overflow-hidden bg-[#fff0f5] transition-colors">

            {{-- Banner thumbnail --}}
            @if($story->banner_url)
            <div class="overflow-hidden aspect-video">
                <img src="{{ $story->banner_url }}"
                     alt="{{ $story->title }}"
                     loading="lazy"
                     decoding="async"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
            </div>
            @else
            <div class="aspect-video bg-[#ffcde8]"></div>
            @endif

            <div class="p-6 space-y-3">
                <p class="text-xs text-[#c4607a] tracking-widest uppercase">
                    {{ $story->published_at?->format('M j, Y') }}
                    &nbsp;·&nbsp;
                    {{ $story->reading_time_minutes }} min read
                </p>
                <h2 class="font-display text-xl text-[#1a0d14] group-hover:text-[#ff1493] transition-colors leading-snug">
                    {{ $story->title }}
                </h2>
                @if($story->meta_description)
                <p class="text-[#c4607a] text-sm leading-relaxed line-clamp-3">{{ $story->meta_description }}</p>
                @endif
            </div>
        </a>
        @endforeach
    </div>

    @if($stories->hasPages())
    <div class="mt-16 flex justify-center">
        {{ $stories->links('vendor.pagination.tailwind') }}
    </div>
    @endif
    @endif

</section>
@endsection
