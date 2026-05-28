@extends('layouts.admin')
@section('title', 'Stories')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-display text-[#f5f0eb]">Stories</h1>
    <a href="{{ route('admin.stories.create') }}"
       class="px-5 py-2 bg-[#c9a84c] text-[#0a0a0a] text-sm font-medium rounded hover:bg-[#b8943e] transition-colors">
        + New Story
    </a>
</div>

<div class="overflow-x-auto -mx-4 px-4">
<table class="w-full text-sm border-collapse min-w-[480px]">
    <thead>
        <tr class="text-left text-[#9e9e9e] text-xs tracking-widest uppercase border-b border-[#2e2e2e]">
            <th class="pb-3 pr-4 font-normal">Title</th>
            <th class="pb-3 pr-4 font-normal">Status</th>
            <th class="pb-3 pr-4 font-normal">Published</th>
            <th class="pb-3 font-normal">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-[#1a1a1a]">
        @forelse($stories as $story)
        <tr class="hover:bg-[#1a1a1a]/50 transition-colors">
            <td class="py-4 pr-4">
                <span class="text-[#f5f0eb]">{{ $story->title }}</span>
                <span class="block text-xs text-[#9e9e9e]">{{ $story->slug }}</span>
            </td>
            <td class="py-4 pr-4">
                <span class="text-xs px-2 py-0.5 rounded-full
                    @if($story->status === 'published') bg-emerald-900/40 text-emerald-400 border border-emerald-800
                    @else bg-[#2e2e2e] text-[#9e9e9e] @endif">
                    {{ $story->status }}
                </span>
            </td>
            <td class="py-4 pr-4 text-[#9e9e9e]">
                {{ $story->published_at?->format('M j, Y') ?? '—' }}
            </td>
            <td class="py-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.stories.edit', $story) }}"
                       class="text-[#9e9e9e] hover:text-[#c9a84c] transition-colors">Edit</a>

                    @if($story->status === 'published')
                    <a href="{{ route('stories.show', $story->slug) }}" target="_blank"
                       class="text-[#9e9e9e] hover:text-[#f5f0eb] transition-colors">View</a>
                    @endif

                    <form method="POST" action="{{ route('admin.stories.destroy', $story) }}"
                          onsubmit="return confirm('Delete this story permanently?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-400 transition-colors">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="py-16 text-center text-[#9e9e9e]">No stories yet.</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($stories->hasPages())
<div class="mt-8">{{ $stories->links('vendor.pagination.tailwind') }}</div>
@endif
</div>
@endsection
