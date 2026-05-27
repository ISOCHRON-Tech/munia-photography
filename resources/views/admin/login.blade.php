<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — Monea</title>
    @vite(['resources/css/app.css'])
    <style>
        body { background: #0a0a0a; color: #f5f0eb; font-family: 'Inter', sans-serif; }
    </style>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500|playfair-display:400i&display=swap" rel="stylesheet">
</head>
<body class="h-full flex items-center justify-center min-h-screen px-4">

<div class="w-full max-w-sm">

    {{-- Logo --}}
    <div class="text-center mb-10">
        <a href="{{ route('home') }}"
           class="font-display text-3xl tracking-[0.3em] uppercase text-[#f5f0eb] hover:text-[#c9a84c] transition-colors">
            Monea
        </a>
        <p class="mt-2 text-xs tracking-widest uppercase text-[#9e9e9e]">Admin Access</p>
    </div>

    {{-- Status / success messages --}}
    @if(session('status'))
    <div class="mb-6 px-4 py-3 border border-[#2e2e2e] text-[#9e9e9e] text-sm text-center">
        {{ session('status') }}
    </div>
    @endif

    @if(session('magic_link'))
    {{-- Dev-mode: show the link directly so you can click it --}}
    <div class="mb-6 px-4 py-4 border border-[#c9a84c]/40 bg-[#c9a84c]/5 rounded">
        <p class="text-xs text-[#c9a84c] uppercase tracking-widest mb-3">
            Development — Magic Link:
        </p>
        <a href="{{ session('magic_link') }}"
           class="block text-xs text-[#f5f0eb] break-all hover:text-[#c9a84c] transition-colors leading-relaxed">
            {{ session('magic_link') }}
        </a>
        <p class="mt-3 text-xs text-[#5a5a5a]">
            In production this link is emailed. It expires in 15 minutes and is single-use.
        </p>
    </div>
    @endif

    {{-- Token errors --}}
    @if($errors->has('token'))
    <div class="mb-6 px-4 py-3 border border-red-900/50 bg-red-900/10 text-red-400 text-sm text-center">
        {{ $errors->first('token') }}
    </div>
    @endif

    {{-- Login form --}}
    @unless(session('magic_link'))
    <form method="POST" action="{{ route('admin.login.request') }}" novalidate>
        @csrf

        <div class="mb-5">
            <label for="email" class="block text-xs tracking-widest uppercase text-[#9e9e9e] mb-2">
                Email address
            </label>
            <input type="email"
                   id="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autocomplete="email"
                   placeholder="you@example.com"
                   class="w-full bg-[#1a1a1a] border @error('email') border-red-700 @else border-[#2e2e2e] @enderror
                          text-[#f5f0eb] placeholder-[#5a5a5a] px-4 py-3 text-sm
                          focus:outline-none focus:border-[#c9a84c] transition-colors">
            @error('email')
            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full bg-[#c9a84c] hover:bg-[#b8942f] text-[#0a0a0a]
                       font-medium text-sm tracking-widest uppercase
                       py-3 px-6 transition-colors duration-200">
            Send Magic Link
        </button>
    </form>
    @endunless

    {{-- Back to site --}}
    <p class="mt-8 text-center text-xs text-[#5a5a5a]">
        <a href="{{ route('home') }}" class="hover:text-[#9e9e9e] transition-colors">
            ← Back to site
        </a>
    </p>

</div>
</body>
</html>
