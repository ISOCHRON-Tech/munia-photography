<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — @yield('title', 'Dashboard') · Monea</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0f0f0f] text-[#e5e5e5] antialiased min-h-screen flex" x-data="{ sidebarOpen: false }">

    {{-- Mobile top bar --}}
    <div class="md:hidden fixed inset-x-0 top-0 z-50 h-14 bg-[#1a1a1a] border-b border-[#2e2e2e] flex items-center justify-between px-4">
        <a href="{{ route('home') }}" target="_blank"
           class="font-display text-base tracking-[0.3em] uppercase text-[#f5f0eb]">
            Monea
        </a>
        <button @click="sidebarOpen = !sidebarOpen"
                class="p-2 text-[#9e9e9e] hover:text-[#f5f0eb] transition-colors"
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
         class="md:hidden fixed inset-0 z-30 bg-black/60"
         x-transition:enter="transition-opacity duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display:none">
    </div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-56 bg-[#1a1a1a] border-r border-[#2e2e2e] flex flex-col py-8 px-5
                  transition-transform duration-200
                  -translate-x-full md:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
        <a href="{{ route('home') }}" target="_blank"
           class="font-display text-lg tracking-[0.3em] uppercase text-[#f5f0eb] hover:text-[#c9a84c] transition-colors mb-12">
            Monea
        </a>

        <nav class="flex flex-col gap-1 text-sm" aria-label="Admin navigation">
            {{-- Gallery: all photos --}}
            <a href="{{ route('admin.media.index') }}"
               @click="sidebarOpen = false"
               class="px-3 py-2 rounded transition-colors hover:bg-[#2e2e2e]
                      @if(request()->routeIs('admin.media.*') && request()->query('section') !== 'featured')
                          bg-[#2e2e2e] text-[#c9a84c]
                      @else
                          text-[#9e9e9e]
                      @endif">
                Gallery
            </a>
            {{-- Featured (Home) --}}
            <a href="{{ route('admin.media.index', ['section' => 'featured']) }}"
               @click="sidebarOpen = false"
               class="px-3 py-2 rounded transition-colors hover:bg-[#2e2e2e]
                      @if(request()->query('section') === 'featured')
                          bg-[#2e2e2e] text-[#c9a84c]
                      @else
                          text-[#9e9e9e]
                      @endif">
                ★ Featured <span class="opacity-40 text-xs">(Home)</span>
            </a>
            {{-- Stories --}}
            <a href="{{ route('admin.stories.index') }}"
               @click="sidebarOpen = false"
               class="px-3 py-2 rounded transition-colors hover:bg-[#2e2e2e] @if(request()->routeIs('admin.stories.*')) bg-[#2e2e2e] text-[#c9a84c] @else text-[#9e9e9e] @endif">
                Stories
            </a>
        </nav>

        <div class="mt-auto text-xs text-[#9e9e9e]">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="hover:text-[#f5f0eb] transition-colors">Log out</button>
            </form>
        </div>
    </aside>

    {{-- Content --}}
    <div class="flex-1 md:ml-56 p-4 pt-18 md:pt-0 md:p-8 lg:p-12" style="padding-top: calc(3.5rem + 1rem)" x-init="$watch('sidebarOpen', v => document.body.style.overflow = v ? 'hidden' : '')">

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

    @stack('scripts')
</body>
</html>
