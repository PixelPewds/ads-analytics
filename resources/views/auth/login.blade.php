<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — AdsAnalytics</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-ivory: #F5F5F7;
            --color-misty: #2F5061;
            --color-misty-light: #3a6278;
            --color-misty-dark: #243d4d;
            --color-teal: #4297A0;
            --color-coral: #E57F84;
            --font-barlow: 'Barlow', sans-serif;
            --font-plex: 'IBM Plex Sans', sans-serif;
        }
        body { font-family: 'IBM Plex Sans', sans-serif; }
        h1, h2, h3, h4, h5 { font-family: 'Barlow', sans-serif; }
    </style>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-[#F5F5F7]" x-data="{ showPass: false }">

<div class="min-h-screen flex">

    {{-- Left panel: branding --}}
    <div class="hidden lg:flex lg:w-1/2 bg-[#2F5061] flex-col justify-between p-12 relative overflow-hidden">

        {{-- Background geometric decoration --}}
        <div class="absolute inset-0 opacity-5">
            <div class="absolute top-20 left-20 w-64 h-64 rounded-full border-2 border-white"></div>
            <div class="absolute top-40 left-40 w-96 h-96 rounded-full border border-white"></div>
            <div class="absolute bottom-20 right-10 w-48 h-48 rounded-full border-2 border-white"></div>
            <div class="absolute -bottom-10 -right-10 w-72 h-72 rounded-full border border-white"></div>
        </div>
        <div class="absolute top-0 right-0 w-px h-full bg-gradient-to-b from-transparent via-white/10 to-transparent"></div>

        {{-- Logo --}}
        <div class="relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#4297A0] flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <span class="text-white text-xl font-bold font-[Barlow]">AdsAnalytics</span>
            </div>
        </div>

        {{-- Hero content --}}
        <div class="relative z-10">
            <h1 class="text-4xl font-bold text-white font-[Barlow] leading-tight mb-6">
                Transform your<br>
                <span class="text-[#4297A0]">Meta Ads</span><br>
                into insights.
            </h1>
            <p class="text-white/70 text-base leading-relaxed mb-8 max-w-md">
                Upload your Meta Ads CSV reports and get AI-powered analysis, campaign recommendations, and performance dashboards — instantly.
            </p>

            {{-- Feature pills --}}
            <div class="flex flex-wrap gap-3">
                @foreach(['Campaign Analytics', 'AI Recommendations', 'CTR & ROAS Tracking', 'Chat Assistant'] as $feature)
                    <span class="px-3 py-1.5 rounded-full bg-white/10 text-white/80 text-sm border border-white/20 backdrop-blur-sm">
                        {{ $feature }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Bottom stat cards --}}
        <div class="relative z-10 grid grid-cols-3 gap-4">
            <div class="bg-white/10 rounded-xl p-4 border border-white/20 backdrop-blur-sm">
                <div class="text-2xl font-bold text-white font-[Barlow]">AI</div>
                <div class="text-white/60 text-xs mt-1">Powered Analysis</div>
            </div>
            <div class="bg-white/10 rounded-xl p-4 border border-white/20 backdrop-blur-sm">
                <div class="text-2xl font-bold text-white font-[Barlow]">CSV</div>
                <div class="text-white/60 text-xs mt-1">& XLSX Support</div>
            </div>
            <div class="bg-white/10 rounded-xl p-4 border border-white/20 backdrop-blur-sm">
                <div class="text-2xl font-bold text-white font-[Barlow]">10+</div>
                <div class="text-white/60 text-xs mt-1">KPI Metrics</div>
            </div>
        </div>
    </div>

    {{-- Right panel: login form --}}
    <div class="flex-1 flex flex-col items-center justify-center px-6 py-12 lg:px-16">

        {{-- Mobile logo --}}
        <div class="lg:hidden mb-10 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#2F5061] flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <span class="text-[#2F5061] text-xl font-bold font-[Barlow]">AdsAnalytics</span>
        </div>

        <div class="w-full max-w-md">

            {{-- Header --}}
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-[#2F5061] font-[Barlow] mb-2">Welcome back</h2>
                <p class="text-gray-500 text-sm">Sign in to your analytics dashboard.</p>
            </div>

            {{-- Error messages --}}
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        @foreach($errors->all() as $error)
                            <p class="text-red-700 text-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(session('status'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                    <p class="text-emerald-700 text-sm">{{ session('status') }}</p>
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            placeholder="you@example.com"
                            class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#4297A0]/40 focus:border-[#4297A0] transition-all duration-200 @error('email') border-red-400 focus:ring-red-200 @enderror"
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input
                            :type="showPass ? 'text' : 'password'"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full pl-10 pr-12 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#4297A0]/40 focus:border-[#4297A0] transition-all duration-200"
                        >
                        <button
                            type="button"
                            @click="showPass = !showPass"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-[#4297A0] focus:ring-[#4297A0]/40 transition">
                        <span class="text-sm text-gray-600">Remember me</span>
                    </label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full py-3 px-6 bg-[#E57F84] hover:bg-[#d96e73] text-white font-semibold text-sm rounded-xl transition-all duration-200 shadow-sm hover:shadow-md active:scale-[0.98] font-[Barlow] tracking-wide"
                >
                    Sign In to Dashboard
                </button>
            </form>

            {{-- Footer --}}
            <p class="mt-8 text-center text-xs text-gray-400">
                AdsAnalytics &mdash; Powered by OpenAI &amp; Chart.js
            </p>
        </div>
    </div>
</div>

</body>
</html>
