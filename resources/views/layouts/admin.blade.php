<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — @yield('title', 'Dashboard') · Monea</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    @stack('before_alpine')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, a, button, [role="button"], input, select, textarea, label {
            cursor: url('{{ asset('images/cursor/cursor.png') }}') 16 15, auto;
        }
        a:hover, button:hover, [role="button"]:hover, input:hover, select:hover,
        textarea:hover, label:hover {
            cursor: url('{{ asset('images/cursor/cursor-hover.png') }}') 20 19, pointer;
        }
    </style>
    @stack('styles')
    @stack('head_scripts')
</head>
<body class="bg-[#fff0f5] text-[#1a0d14] antialiased min-h-screen flex" x-data="{ sidebarOpen: false }">

    {{-- Mobile top bar --}}
    <div class="md:hidden fixed inset-x-0 top-0 z-50 h-14 bg-[#ffe4f0] border-b border-[#ffb3d9] flex items-center justify-between px-4">
        <a href="{{ route('home') }}" target="_blank"
           class="font-display text-base tracking-[0.3em] uppercase text-[#1a0d14]">
            Monea
        </a>
        <button @click="sidebarOpen = !sidebarOpen"
                class="p-2 text-[#8b3a6e] hover:text-[#1a0d14] transition-colors"
                aria-label="Toggle navigation">
            <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg x-show="sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen"
         @click="sidebarOpen = false"
         class="md:hidden fixed inset-0 z-30 bg-[#1a0d14]/30"
         x-transition:enter="transition-opacity duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display:none">
    </div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-56 bg-[#ffe4f0] border-r border-[#ffb3d9] flex flex-col py-8 px-5
                  transition-transform duration-200
                  -translate-x-full md:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
        <a href="{{ route('home') }}" target="_blank"
           class="font-display text-lg tracking-[0.3em] uppercase text-[#1a0d14] hover:text-[#ff1493] transition-colors mb-12">
            Monea
        </a>

        <nav class="flex flex-col gap-1 text-sm" aria-label="Admin navigation">
            {{-- Gallery: all photos --}}
            <a href="{{ route('admin.media.index') }}"
               @click="sidebarOpen = false"
               class="px-3 py-2 rounded transition-colors hover:bg-[#ffcde8]
                      @if(request()->routeIs('admin.media.*') && request()->query('section') !== 'featured')
                          bg-[#ffcde8] text-[#ff1493]
                      @else
                          text-[#8b3a6e]
                      @endif">
                Gallery
            </a>
            {{-- Featured (Home) --}}
            <a href="{{ route('admin.media.index', ['section' => 'featured']) }}"
               @click="sidebarOpen = false"
               class="px-3 py-2 rounded transition-colors hover:bg-[#ffcde8]
                      @if(request()->query('section') === 'featured')
                          bg-[#ffcde8] text-[#ff1493]
                      @else
                          text-[#8b3a6e]
                      @endif">
                ★ Featured <span class="opacity-40 text-xs">(Home)</span>
            </a>
            {{-- Stories --}}
            <a href="{{ route('admin.stories.index') }}"
               @click="sidebarOpen = false"
               class="px-3 py-2 rounded transition-colors hover:bg-[#ffcde8] @if(request()->routeIs('admin.stories.*')) bg-[#ffcde8] text-[#ff1493] @else text-[#8b3a6e] @endif">
                Stories
            </a>
        </nav>

        {{-- Hello Kitty sidebar decoration --}}
        <div class="mt-auto flex flex-col items-center">
            <img src="/images/kitty/kitty-head.png"
                 alt="" aria-hidden="true" draggable="false"
                 class="w-20 mb-3 opacity-70 hover:opacity-100 transition-opacity pointer-events-none select-none"
                 style="image-rendering:-webkit-optimize-contrast;">
            <div class="w-full text-xs text-[#8b3a6e]">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="hover:text-[#1a0d14] transition-colors">Log out</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Hello Kitty corner decoration (desktop only) --}}
    <img src="/images/kitty/kitty-full.png"
         alt="" aria-hidden="true" draggable="false"
         class="hidden md:block fixed bottom-0 right-0 pointer-events-none select-none z-10"
         style="width:clamp(140px,12vw,200px);opacity:0.55;image-rendering:-webkit-optimize-contrast;">

    {{-- Content --}}
    <div class="flex-1 md:ml-56 p-4 pt-18 md:pt-0 md:p-8 lg:p-12" style="padding-top: calc(3.5rem + 1rem)" x-init="$watch('sidebarOpen', v => document.body.style.overflow = v ? 'hidden' : '')">

        @if(session('success'))
        <div class="mb-6 px-4 py-3 rounded bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm"
             x-data x-init="setTimeout(() => $el.remove(), 4000)">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 px-4 py-3 rounded bg-red-50 border border-red-300 text-red-700 text-sm">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
