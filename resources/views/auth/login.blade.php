<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Ads Analytics</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-barlow { font-family: 'Barlow', sans-serif; }
        .form-input {
            background: #F7FAFB; border: 1px solid #D0DCE8; border-radius: 9px;
            padding: 10px 13px; font-size: 0.875rem; color: #1A2E3E; width: 100%;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-input:focus { outline: none; border-color: #2A9D8F; box-shadow: 0 0 0 3px rgba(42,157,143,0.12); }
        .btn-coral {
            background: #E76F51; color: #fff; font-weight: 600; border-radius: 10px;
            padding: 11px 18px; font-size: 0.875rem; border: none; cursor: pointer;
            transition: background 0.15s; width: 100%;
        }
        .btn-coral:hover { background: #d05e40; }
    </style>
</head>
<body class="h-full bg-[#EEF3F8] flex items-center justify-center p-4">

<div class="w-full max-w-md">

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-[#DDE8F0]">

        {{-- Header band --}}
        <div class="bg-[#0F1C28] px-8 py-7 text-center">
            <div class="flex items-center justify-center gap-2.5 mb-4">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#2A9D8F] to-[#1A7A70] flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <span class="font-barlow text-xl font-bold text-white">Ads Analytics</span>
            </div>
            <p class="text-sm font-semibold text-white font-barlow">Welcome back</p>
            <p class="text-xs text-[#4A7A99] mt-1">Sign in to your analytics dashboard</p>
        </div>

        {{-- Form --}}
        <div class="px-8 py-7">
            @if($errors->any())
            <div class="flex items-start gap-2 p-3 rounded-lg bg-[#FEF1EE] border border-[#F5C3B5] text-[#9B3722] text-sm mb-5">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <ul class="space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-xs font-semibold text-[#4A6A82] uppercase tracking-wider mb-1.5">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        class="form-input"
                        placeholder="you@example.com"
                    >
                </div>

                <div class="mb-5">
                    <label for="password" class="block text-xs font-semibold text-[#4A6A82] uppercase tracking-wider mb-1.5">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                        class="form-input"
                        placeholder="••••••••"
                    >
                </div>

                <div class="flex items-center gap-2 mb-5">
                    <input type="checkbox" id="remember" name="remember" class="w-4 h-4 accent-[#2A9D8F] rounded">
                    <label for="remember" class="text-sm text-[#4A6A82] cursor-pointer">Remember me</label>
                </div>

                <button type="submit" class="btn-coral" :disabled="loading">
                    <span x-show="!loading">Sign In</span>
                    <span x-show="loading" class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        Signing in…
                    </span>
                </button>

                <p class="text-center text-xs text-[#8AABBF] mt-4">
                    Default: admin@adsanalytics.test / password
                </p>
            </form>
        </div>
    </div>

    <p class="text-center text-xs text-[#8AABBF] mt-5">
        © {{ date('Y') }} Ads Analytics — Premium Meta Ads Intelligence
    </p>
</div>

</body>
</html>
