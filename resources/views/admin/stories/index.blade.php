@extends('layouts.admin')
@section('title', 'Stories')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-display text-[#1a0d14]">Stories</h1>
    <a href="{{ route('admin.stories.create') }}"
       class="px-5 py-2 bg-[#ff1493] text-white text-sm font-medium rounded hover:bg-[#c71585] transition-colors">
        + New Story
    </a>
</div>

<div class="overflow-x-auto -mx-4 px-4">
<table class="w-full text-sm border-collapse min-w-[480px]">
    <thead>
        <tr class="text-left text-[#8b3a6e] text-xs tracking-widest uppercase border-b border-[#ffb3d9]">
            <th class="pb-3 pr-4 font-normal">Title</th>
            <th class="pb-3 pr-4 font-normal">Status</th>
            <th class="pb-3 pr-4 font-normal">Published</th>
            <th class="pb-3 font-normal">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-[#ffe4f0]">
        @forelse($stories as $story)
        <tr class="hover:bg-[#ffe4f0]/50 transition-colors">
            <td class="py-4 pr-4">
                <span class="text-[#1a0d14]">{{ $story->title }}</span>
                <span class="block text-xs text-[#8b3a6e]">{{ $story->slug }}</span>
            </td>
            <td class="py-4 pr-4">
                <span class="text-xs px-2 py-0.5 rounded-full
                    @if($story->status === 'published') bg-emerald-100 text-emerald-700 border border-emerald-300
                    @else bg-[#ffe4f0] text-[#8b3a6e] border border-[#ffb3d9] @endif">
                    {{ $story->status }}
                </span>
            </td>
            <td class="py-4 pr-4 text-[#8b3a6e]">
                {{ $story->published_at?->format('M j, Y') ?? '—' }}
            </td>
            <td class="py-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.stories.edit', $story) }}"
                       class="text-[#8b3a6e] hover:text-[#ff1493] transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-pen-to-square text-xs"></i> Edit</a>

                    @if($story->status === 'published')
                    <a href="{{ route('stories.show', $story->slug) }}" target="_blank"
                       class="text-[#8b3a6e] hover:text-[#1a0d14] transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i> View</a>
                    @endif

                    <form method="POST" action="{{ route('admin.stories.destroy', $story) }}"
                          onsubmit="return confirm('Delete this story permanently?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-400 transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-trash text-xs"></i> Delete</button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="py-16 text-center text-[#8b3a6e]">No stories yet.</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($stories->hasPages())
<div class="mt-8">{{ $stories->links('vendor.pagination.tailwind') }}</div>
@endif
</div>
@endsection
