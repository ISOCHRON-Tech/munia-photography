<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Monea') — Photography & Stories</title>
    <meta name="description" content="@yield('meta_description', 'A personal photography portfolio and story journal.')">

    {{-- Open Graph --}}
    <meta property="og:type"  content="website">
    <meta property="og:title" content="@yield('title', 'Monea')">
    <meta property="og:description" content="@yield('meta_description', 'A personal photography portfolio.')">
    @hasSection('og_image')
    <meta property="og:image" content="@yield('og_image')">
    @endif

    {{-- Preconnect for performance --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,500,600,700|pacifico:400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="bg-[#fff0f5] text-[#1a0d14] antialiased">

    {{-- ══ Navigation ══ --}}
    <header id="main-nav" class="nav-header" role="banner">
        <nav class="nav-bar" role="navigation" aria-label="Main navigation">

            {{-- Logo --}}
            <a href="{{ route('home') }}" id="nav-logo" class="nav-logo" aria-label="Monea — Home">
                <span class="nav-logo__l">M</span><span
                    class="nav-logo__l">O</span><span
                    class="nav-logo__l">N</span><span
                    class="nav-logo__l">E</span><span
                    class="nav-logo__l">A</span>
            </a>

            {{-- Desktop links --}}
            <ul class="hidden md:flex items-center gap-10" id="nav-links">
                <li>
                    <a href="{{ route('gallery.index') }}"
                       class="nav-link {{ request()->routeIs('gallery.*') ? 'active' : '' }}">
                        <span class="nav-link__rail">
                            <span class="nav-link__word">Gallery</span>
                            <span class="nav-link__word nav-link__word--gold" aria-hidden="true">Gallery</span>
                        </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('stories.index') }}"
                       class="nav-link {{ request()->routeIs('stories.*') ? 'active' : '' }}">
                        <span class="nav-link__rail">
                            <span class="nav-link__word">Stories</span>
                            <span class="nav-link__word nav-link__word--gold" aria-hidden="true">Stories</span>
                        </span>
                    </a>
                </li>
            </ul>

            {{-- Mobile hamburger --}}
            <button id="nav-burger" class="md:hidden flex flex-col gap-[6px] cursor-pointer z-10 py-1 px-1"
                    aria-label="Open menu" aria-expanded="false">
                <span class="burger-line"></span>
                <span class="burger-line"></span>
                <span class="burger-line"></span>
            </button>
        </nav>
    </header>

    {{-- Mobile full-screen drawer --}}
    <div id="nav-drawer" class="nav-drawer pointer-events-none" role="dialog" aria-modal="true" aria-label="Navigation">

        {{-- Decorative corner rule --}}
        <span class="nav-drawer__rule" aria-hidden="true"></span>

        <nav aria-label="Mobile navigation">
            <ul class="flex flex-col items-start gap-8">
                <li class="nav-drawer__item">
                    <a href="{{ route('gallery.index') }}" class="nav-drawer__link {{ request()->routeIs('gallery.*') ? 'active' : '' }}">
                        <span class="nav-drawer__num">01</span>Gallery
                    </a>
                </li>
                <li class="nav-drawer__item">
                    <a href="{{ route('stories.index') }}" class="nav-drawer__link {{ request()->routeIs('stories.*') ? 'active' : '' }}">
                        <span class="nav-drawer__num">02</span>Stories
                    </a>
                </li>
            </ul>
        </nav>

        <p class="nav-drawer__brand" aria-hidden="true">Monea</p>
    </div>

    {{-- ══ Main Content ══ --}}
    <main>
        @yield('content')
    </main>

    {{-- ══ Footer ══ --}}
    <footer class="border-t border-[#2e2e2e] py-12 px-6 md:px-12 mt-24">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-[#9e9e9e] text-xs tracking-widest uppercase">
            <span>© {{ date('Y') }} Monea</span>
            <nav class="flex gap-8" aria-label="Footer navigation">
                <a href="{{ route('gallery.index') }}" class="hover:text-[#c9a84c] transition-colors">Gallery</a>
                <a href="{{ route('stories.index') }}" class="hover:text-[#c9a84c] transition-colors">Stories</a>
            </nav>
        </div>
    </footer>

    @stack('scripts')

</body>
</html>
