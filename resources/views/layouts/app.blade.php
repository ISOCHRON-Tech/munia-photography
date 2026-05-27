<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'munia') — Photography & Stories</title>
    <meta name="description" content="@yield('meta_description', 'A personal photography portfolio and story journal.')">

    {{-- Open Graph --}}
    <meta property="og:type"  content="website">
    <meta property="og:title" content="@yield('title', 'munia')">
    <meta property="og:description" content="@yield('meta_description', 'A personal photography portfolio.')">
    @hasSection('og_image')
    <meta property="og:image" content="@yield('og_image')">
    @endif

    {{-- Preconnect for performance --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600|playfair-display:400,700i&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="bg-[#0a0a0a] text-[#f5f0eb] antialiased" x-data>

    {{-- ══ Preloader ══ --}}
    <div id="preloader" role="status" aria-label="Loading">
        <span class="preloader-logo" aria-hidden="true">munia</span>
        <div class="preloader-line" aria-hidden="true"></div>
    </div>

    {{-- ══ Navigation ══ --}}
    <header class="fixed top-0 inset-x-0 z-50 mix-blend-difference" x-data="{ open: false }">
        <nav class="flex items-center justify-between px-6 md:px-12 py-5"
             role="navigation" aria-label="Main navigation">

            <a href="{{ route('home') }}"
               class="font-display text-lg tracking-[0.35em] uppercase text-[#f5f0eb] hover:text-[#c9a84c] transition-colors">
                munia
            </a>

            {{-- Desktop nav --}}
            <ul class="hidden md:flex items-center gap-10">
                <li><a href="{{ route('gallery.index') }}" class="nav-link {{ request()->routeIs('gallery.*') ? 'active' : '' }}">Gallery</a></li>
                <li><a href="{{ route('stories.index') }}" class="nav-link {{ request()->routeIs('stories.*') ? 'active' : '' }}">Stories</a></li>
            </ul>

            {{-- Mobile hamburger --}}
            <button class="md:hidden flex flex-col gap-[5px] cursor-pointer z-10"
                    @click="open = !open" aria-label="Toggle menu">
                <span class="w-6 h-px bg-[#f5f0eb] transition-all"
                      :class="open ? 'rotate-45 translate-y-[7px]' : ''"></span>
                <span class="w-6 h-px bg-[#f5f0eb] transition-all"
                      :class="open ? 'opacity-0' : ''"></span>
                <span class="w-6 h-px bg-[#f5f0eb] transition-all"
                      :class="open ? '-rotate-45 -translate-y-[7px]' : ''"></span>
            </button>
        </nav>

        {{-- Mobile drawer --}}
        <div class="md:hidden fixed inset-0 bg-[#0a0a0a]/98 flex items-center justify-center z-40 transition-all duration-500"
             :class="open ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'"
             x-show="open" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <ul class="flex flex-col items-center gap-10 text-2xl">
                <li><a href="{{ route('gallery.index') }}" class="nav-link" @click="open=false">Gallery</a></li>
                <li><a href="{{ route('stories.index') }}" class="nav-link" @click="open=false">Stories</a></li>
            </ul>
        </div>
    </header>

    {{-- ══ Main Content ══ --}}
    <main>
        @yield('content')
    </main>

    {{-- ══ Footer ══ --}}
    <footer class="border-t border-[#2e2e2e] py-12 px-6 md:px-12 mt-24">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-[#9e9e9e] text-xs tracking-widest uppercase">
            <span>© {{ date('Y') }} munia</span>
            <nav class="flex gap-8" aria-label="Footer navigation">
                <a href="{{ route('gallery.index') }}" class="hover:text-[#c9a84c] transition-colors">Gallery</a>
                <a href="{{ route('stories.index') }}" class="hover:text-[#c9a84c] transition-colors">Stories</a>
            </nav>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
