<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — @yield('title', 'Dashboard') · munia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0f0f0f] text-[#e5e5e5] antialiased min-h-screen flex" x-data>

    {{-- Sidebar --}}
    <aside class="w-56 bg-[#1a1a1a] border-r border-[#2e2e2e] flex flex-col py-8 px-5 fixed inset-y-0 left-0 z-30">
        <a href="{{ route('home') }}" target="_blank"
           class="font-display text-lg tracking-[0.3em] uppercase text-[#f5f0eb] hover:text-[#c9a84c] transition-colors mb-12">
            munia
        </a>

        <nav class="flex flex-col gap-1 text-sm" aria-label="Admin navigation">
            <a href="{{ route('admin.media.index') }}"
               class="px-3 py-2 rounded transition-colors hover:bg-[#2e2e2e] @if(request()->routeIs('admin.media.*')) bg-[#2e2e2e] text-[#c9a84c] @else text-[#9e9e9e] @endif">
                Media
            </a>
            <a href="{{ route('admin.stories.index') }}"
               class="px-3 py-2 rounded transition-colors hover:bg-[#2e2e2e] @if(request()->routeIs('admin.stories.*')) bg-[#2e2e2e] text-[#c9a84c] @else text-[#9e9e9e] @endif">
                Stories
            </a>
        </nav>

        <div class="mt-auto text-xs text-[#9e9e9e]">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="hover:text-[#f5f0eb] transition-colors">Log out</button>
            </form>
        </div>
    </aside>

    {{-- Content --}}
    <div class="ml-56 flex-1 p-8 md:p-12">

        @if(session('success'))
        <div class="mb-6 px-4 py-3 rounded bg-emerald-900/40 border border-emerald-700 text-emerald-300 text-sm"
             x-data x-init="setTimeout(() => $el.remove(), 4000)">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 px-4 py-3 rounded bg-red-900/40 border border-red-700 text-red-300 text-sm">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </div>

</body>
</html>
