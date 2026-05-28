<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — Munia</title>
    @vite(['resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500|playfair-display:400,400i&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-[#0a0a0a] text-[#1a0d14] antialiased">

<div class="min-h-screen flex">

    {{-- ── Left panel ─────────────────────────────────────────────────────── --}}
    <div class="hidden lg:flex lg:w-[45%] relative border-r border-[#161616] flex-col items-center justify-center px-16 overflow-hidden">

        {{-- ambient glow --}}
        <div class="absolute inset-0 pointer-events-none"
             style="background: radial-gradient(ellipse 80% 60% at 50% 40%, rgba(201,168,76,0.05) 0%, transparent 70%);"></div>

        {{-- top-left corner accent --}}
        <div class="absolute top-0 left-0 w-16 h-16 border-l border-t border-[#1e1e1e]"></div>
        <div class="absolute bottom-0 right-0 w-16 h-16 border-r border-b border-[#1e1e1e]"></div>

        <div class="relative text-center select-none">
            <span class="block font-display text-[5.5rem] leading-none tracking-[0.2em] uppercase text-[#1a0d14]">
                Munia
            </span>
            <div class="mt-5 flex items-center gap-4">
                <div class="h-px flex-1 bg-gradient-to-r from-transparent to-[#2e2e2e]"></div>
                <span class="text-[10px] tracking-[0.5em] uppercase text-[#4a4a4a]">Photography</span>
                <div class="h-px flex-1 bg-gradient-to-l from-transparent to-[#2e2e2e]"></div>
            </div>
            <p class="mt-6 text-[11px] tracking-[0.35em] uppercase text-[#2e2e2e] leading-loose">
                Light &nbsp;·&nbsp; Silence &nbsp;·&nbsp; Moment
            </p>
        </div>

        {{-- vertical rule + year --}}
        <div class="absolute bottom-10 left-0 right-0 flex flex-col items-center gap-3">
            <div class="w-px h-10 bg-gradient-to-b from-[#2e2e2e] to-transparent"></div>
            <span class="text-[10px] tracking-widest text-[#2e2e2e]">2026</span>
        </div>
    </div>

    {{-- ── Right panel: form ───────────────────────────────────────────────── --}}
    <div class="flex-1 flex items-center justify-center px-8 py-12" x-data>

        <div class="w-full max-w-[400px]">

            {{-- Mobile brand --}}
            <div class="lg:hidden text-center mb-14">
                <a href="{{ route('home') }}"
                   class="font-display text-4xl tracking-[0.25em] uppercase text-[#1a0d14] hover:text-[#ff1493] transition-colors">
                    Munia
                </a>
            </div>

            {{-- Heading --}}
            <div class="mb-10">
                <h1 class="text-2xl font-light tracking-wide">Welcome back</h1>
                <p class="mt-1.5 text-[11px] tracking-[0.3em] uppercase text-[#5a5a5a]">Admin access</p>
            </div>

            {{-- Error banner --}}
            @if($errors->has('form'))
            <div class="mb-7 flex items-center gap-3 px-4 py-3.5 border border-red-900/40 bg-red-950/30">
                <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-red-400">{{ $errors->first('form') }}</p>
            </div>
            @endif

            @if(session('status'))
            <div class="mb-7 px-4 py-3.5 border border-[#2a2a2a] text-[#8b3a6e] text-sm">
                {{ session('status') }}
            </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('admin.login.post') }}" novalidate class="space-y-7">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email"
                           class="block text-[10px] tracking-[0.25em] uppercase text-[#5a5a5a] mb-2.5">
                        Email address
                    </label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           required autocomplete="email"
                           class="w-full bg-[#fff0f5] border @error('email') border-red-800 @else border-[#242424] @enderror
                                  focus:border-[#ff1493] focus:outline-none
                                  text-[#1a0d14] placeholder-[#343434]
                                  px-4 py-3 text-sm transition-colors duration-200">
                </div>

                {{-- Password --}}
                <div x-data="{ show: false }">
                    <label for="password"
                           class="block text-[10px] tracking-[0.25em] uppercase text-[#5a5a5a] mb-2.5">
                        Password
                    </label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'"
                               id="password" name="password"
                               required autocomplete="current-password"
                               class="w-full bg-[#fff0f5] border @error('password') border-red-800 @else border-[#242424] @enderror
                                      focus:border-[#ff1493] focus:outline-none
                                      text-[#1a0d14]
                                      px-4 py-3 text-sm pr-11 transition-colors duration-200">
                        <button type="button" @click="show = !show" tabindex="-1"
                                class="absolute inset-y-0 right-0 w-10 flex items-center justify-center text-[#3a3a3a] hover:text-[#8b3a6e] transition-colors">
                            {{-- eye --}}
                            <svg x-show="!show" class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{-- eye-off --}}
                            <svg x-show="show" class="w-4 h-4" width="16" height="16" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full mt-2 bg-[#ff1493] hover:bg-[#b8942f] active:bg-[#a07f25]
                               text-white text-[11px] font-medium tracking-[0.3em] uppercase
                               py-4 transition-colors duration-200">
                    Sign in
                </button>
            </form>

            {{-- Footer --}}
            <div class="mt-10 flex items-center justify-between">
                <a href="{{ route('home') }}"
                   class="text-[11px] tracking-wider text-[#333] hover:text-[#6a6a6a] transition-colors">
                    ← Portfolio
                </a>
                <span class="text-[11px] text-[#242424] tracking-widest">MUNIA / ADMIN</span>
            </div>

        </div>
    </div>

</div>

</body>
</html>

