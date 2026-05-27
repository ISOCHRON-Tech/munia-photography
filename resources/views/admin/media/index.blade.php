@extends('layouts.admin')
@section('title', 'Media Library')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-display text-[#f5f0eb]">Media Library</h1>
    <a href="{{ route('admin.media.create') }}"
       class="px-5 py-2 bg-[#c9a84c] text-[#0a0a0a] text-sm font-medium rounded hover:bg-[#b8943e] transition-colors">
        + Upload
    </a>
</div>

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
    @forelse($items as $item)
    <div class="group relative rounded overflow-hidden bg-[#1a1a1a] aspect-square">
        <img src="{{ $item->public_url }}"
             alt="{{ $item->title ?? '' }}"
             loading="lazy"
             class="w-full h-full object-cover">

        {{-- Hover actions --}}
        <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
            <p class="text-xs text-center text-white px-2 line-clamp-2">{{ $item->title ?? '(untitled)' }}</p>
            <form method="POST" action="{{ route('admin.media.destroy', $item) }}"
                  onsubmit="return confirm('Delete this photograph?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="text-red-400 hover:text-red-300 text-xs border border-red-700 px-3 py-1 rounded transition-colors">
                    Delete
                </button>
            </form>
        </div>
    </div>
    @empty
    <p class="col-span-5 text-[#9e9e9e] py-16 text-center">No media uploaded yet.</p>
    @endforelse
</div>

@if($items->hasPages())
<div class="mt-10">{{ $items->links('vendor.pagination.tailwind') }}</div>
@endif
@endsection
