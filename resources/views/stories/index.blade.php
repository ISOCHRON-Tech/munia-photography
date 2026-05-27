@extends('layouts.app')

@section('title', 'Stories')
@section('meta_description', 'Field notes, travel stories, and essays on photography.')

@section('content')
<section class="pt-28 pb-20 px-4 md:px-8 xl:px-16">

    <header class="mb-16 text-center">
        <h1 class="font-display text-5xl md:text-7xl tracking-tight mb-4">Stories</h1>
        <p class="text-[#9e9e9e] text-sm tracking-widest uppercase">Field notes & essays</p>
    </header>

    @if($stories->isEmpty())
    <div class="text-center text-[#9e9e9e] py-32 text-lg">No stories published yet.</div>
    @else
    <div class="max-w-screen-xl mx-auto grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        @foreach($stories as $story)
        <a href="{{ route('stories.show', $story->slug) }}"
           class="group block rounded overflow-hidden bg-[#1a1a1a] hover:bg-[#222] transition-colors">

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
            <div class="aspect-video bg-[#2e2e2e]"></div>
            @endif

            <div class="p-6 space-y-3">
                <p class="text-xs text-[#9e9e9e] tracking-widest uppercase">
                    {{ $story->published_at?->format('M j, Y') }}
                    &nbsp;·&nbsp;
                    {{ $story->reading_time_minutes }} min read
                </p>
                <h2 class="font-display text-xl text-[#f5f0eb] group-hover:text-[#c9a84c] transition-colors leading-snug">
                    {{ $story->title }}
                </h2>
                @if($story->meta_description)
                <p class="text-[#9e9e9e] text-sm leading-relaxed line-clamp-3">{{ $story->meta_description }}</p>
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
