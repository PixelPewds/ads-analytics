<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — Ads Analytics</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#E8EEF4] flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

            {{-- Header band --}}
            <div class="bg-[#264653] px-8 py-7">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 rounded-lg bg-[#2A9D8F] flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <span class="text-white font-bold text-lg" style="font-family:'Barlow',sans-serif">Ads Analytics</span>
                </div>
                <h1 class="text-white text-2xl font-extrabold" style="font-family:'Barlow',sans-serif">Welcome back</h1>
                <p class="text-white/50 text-sm mt-0.5">Sign in to your analytics dashboard</p>
            </div>

            {{-- Form --}}
            <div class="px-8 py-7">
                @if($errors->any())
                    <div class="alert-error mb-5 text-sm">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-semibold text-[#264653] uppercase tracking-wide mb-1.5" style="font-family:'Barlow',sans-serif">
                            Email address
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="you@example.com"
                            class="w-full border border-[#D6E1EA] rounded-lg px-4 py-2.5 text-sm text-[#264653] placeholder-[#9BBACB] focus:outline-none focus:ring-2 focus:ring-[#2A9D8F]/40 focus:border-[#2A9D8F] transition-all"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-[#264653] uppercase tracking-wide mb-1.5" style="font-family:'Barlow',sans-serif">
                            Password
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full border border-[#D6E1EA] rounded-lg px-4 py-2.5 text-sm text-[#264653] placeholder-[#9BBACB] focus:outline-none focus:ring-2 focus:ring-[#2A9D8F]/40 focus:border-[#2A9D8F] transition-all"
                        >
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-[#6B7C8D] cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-[#D6E1EA] text-[#2A9D8F]">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="btn-coral w-full py-2.5 text-sm text-center block">
                        Sign In
                    </button>
                </form>

                <p class="mt-6 text-center text-xs text-[#6B7C8D]">
                    Default: <code class="bg-[#E8EEF4] px-1.5 py-0.5 rounded text-[#264653]">admin@adsanalytics.test</code> / <code class="bg-[#E8EEF4] px-1.5 py-0.5 rounded text-[#264653]">password</code>
                </p>
            </div>
        </div>

        <p class="text-center text-xs text-[#6B7C8D] mt-4">
            &copy; {{ date('Y') }} Ads Analytics &mdash; Premium Meta Ads Intelligence
        </p>
    </div>

</body>
</html>