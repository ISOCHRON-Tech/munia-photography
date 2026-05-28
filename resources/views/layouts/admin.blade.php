<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — @yield('title', 'Dashboard') · Munia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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
<body class="bg-[#fff0f5] text-[#1a0d14] antialiased h-screen overflow-hidden flex"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: JSON.parse(localStorage.getItem('adminSidebarCollapsed') || 'false')
      }"
      x-init="
          $watch('sidebarCollapsed', v => {
              localStorage.setItem('adminSidebarCollapsed', JSON.stringify(v));
              document.body.classList.toggle('sidebar-collapsed', v);
          });
          document.body.classList.toggle('sidebar-collapsed', sidebarCollapsed);
      ">

    {{-- Mobile top bar --}}
    <div class="md:hidden fixed inset-x-0 top-0 z-50 h-14 bg-[#ffe4f0] border-b border-[#ffb3d9] flex items-center justify-between px-4">
        <a href="{{ route('home') }}" target="_blank"
           class="font-display text-base tracking-[0.3em] uppercase text-[#1a0d14]">
            Munia
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
    <aside class="fixed inset-y-0 left-0 z-40 h-screen bg-[#ffe4f0] border-r border-[#ffb3d9] flex flex-col py-8 overflow-y-auto
                  transition-all duration-200 ease-in-out
                  -translate-x-full md:translate-x-0"
           :class="[
               sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
               sidebarCollapsed ? 'w-14 px-2' : 'w-56 px-5'
           ]">

        {{-- Desktop collapse toggle --}}
        <button @click="sidebarCollapsed = !sidebarCollapsed"
                class="hidden md:flex absolute -right-4 top-8 w-8 h-8 rounded-full
                       bg-[#ff1493] border-2 border-white items-center justify-center
                       text-white hover:bg-[#c71585]
                       transition-colors z-50 shadow-md"
                :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
            <i class="text-[11px] font-bold" :class="sidebarCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left'"></i>
        </button>

        <a href="{{ route('home') }}" target="_blank"
           :title="sidebarCollapsed ? 'Munia' : ''"
           class="font-display uppercase text-[#1a0d14] hover:text-[#ff1493] transition-colors mb-12 flex items-center justify-center overflow-hidden whitespace-nowrap">
            <span x-show="!sidebarCollapsed"
                  x-transition:enter="transition-opacity duration-150 delay-100"
                  x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                  x-transition:leave="transition-opacity duration-100"
                  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                  class="text-lg tracking-[0.3em]">Munia</span>
            <i x-show="sidebarCollapsed"
               x-transition:enter="transition-opacity duration-150 delay-100"
               x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
               x-transition:leave="transition-opacity duration-100"
               x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
               class="fa-solid fa-cat text-xl" style="display:none"></i>
        </a>

        <nav class="flex flex-col gap-1 text-sm overflow-hidden" aria-label="Admin navigation">
            {{-- Gallery: all photos --}}
            <a href="{{ route('admin.media.index') }}"
               @click="sidebarOpen = false"
               :title="sidebarCollapsed ? 'Gallery' : ''"
               :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'"
               class="py-2 rounded transition-colors hover:bg-[#ffcde8] flex items-center gap-2.5
                      @if(request()->routeIs('admin.media.*') && request()->query('section') !== 'featured')
                          bg-[#ffcde8] text-[#ff1493]
                      @else
                          text-[#8b3a6e]
                      @endif">
                <i class="fa-solid fa-images w-4 text-center flex-shrink-0"></i>
                <span x-show="!sidebarCollapsed"
                      x-transition:enter="transition-opacity duration-150 delay-100"
                      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                      x-transition:leave="transition-opacity duration-100"
                      x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                      class="whitespace-nowrap overflow-hidden">Gallery</span>
            </a>
            {{-- Featured (Home) --}}
            <a href="{{ route('admin.media.index', ['section' => 'featured']) }}"
               @click="sidebarOpen = false"
               :title="sidebarCollapsed ? 'Featured (Home)' : ''"
               :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'"
               class="py-2 rounded transition-colors hover:bg-[#ffcde8] flex items-center gap-2.5
                      @if(request()->query('section') === 'featured')
                          bg-[#ffcde8] text-[#ff1493]
                      @else
                          text-[#8b3a6e]
                      @endif">
                <i class="fa-solid fa-star w-4 text-center flex-shrink-0"></i>
                <span x-show="!sidebarCollapsed"
                      x-transition:enter="transition-opacity duration-150 delay-100"
                      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                      x-transition:leave="transition-opacity duration-100"
                      x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                      class="whitespace-nowrap overflow-hidden">Featured <span class="opacity-40 text-xs">(Home)</span></span>
            </a>
            {{-- Stories --}}
            <a href="{{ route('admin.stories.index') }}"
               @click="sidebarOpen = false"
               :title="sidebarCollapsed ? 'Stories' : ''"
               :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'"
               class="py-2 rounded transition-colors hover:bg-[#ffcde8] flex items-center gap-2.5
                      @if(request()->routeIs('admin.stories.*')) bg-[#ffcde8] text-[#ff1493] @else text-[#8b3a6e] @endif">
                <i class="fa-solid fa-book-open w-4 text-center flex-shrink-0"></i>
                <span x-show="!sidebarCollapsed"
                      x-transition:enter="transition-opacity duration-150 delay-100"
                      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                      x-transition:leave="transition-opacity duration-100"
                      x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                      class="whitespace-nowrap overflow-hidden">Stories</span>
            </a>
        </nav>

        {{-- Hello Kitty sidebar decoration --}}
        <div class="mt-auto flex flex-col items-center">
            <img x-show="!sidebarCollapsed"
                 src="/images/kitty/kitty-head.png"
                 alt="" aria-hidden="true" draggable="false"
                 class="w-20 mb-3 opacity-70 hover:opacity-100 transition-opacity pointer-events-none select-none"
                 style="image-rendering:-webkit-optimize-contrast; display:none">
            <div class="w-full text-xs text-[#8b3a6e]" :class="sidebarCollapsed ? 'flex justify-center' : ''">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"
                            :title="sidebarCollapsed ? 'Log out' : ''"
                            class="hover:text-[#1a0d14] transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-right-from-bracket flex-shrink-0"></i>
                        <span x-show="!sidebarCollapsed"
                              x-transition:enter="transition-opacity duration-150 delay-100"
                              x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              x-transition:leave="transition-opacity duration-100"
                              x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                              class="whitespace-nowrap overflow-hidden">Log out</span>
                    </button>
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
    <div class="flex-1 overflow-y-auto h-screen pt-[4.5rem] md:pt-0 px-4 pb-4 md:p-8 lg:p-12 transition-all duration-200 ease-in-out"
         :class="sidebarCollapsed ? 'md:ml-14' : 'md:ml-56'"
         x-init="$watch('sidebarOpen', v => $el.style.overflow = v ? 'hidden' : '')">

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
