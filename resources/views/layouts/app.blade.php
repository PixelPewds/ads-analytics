<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — AdsAnalytics</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN (v4 browser mode) --}}
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-ivory:      #F5F5F7;
            --color-misty:      #2F5061;
            --color-misty-light:#3a6278;
            --color-misty-dark: #243d4d;
            --color-teal:       #4297A0;
            --color-teal-light: #5aadb7;
            --color-coral:      #E57F84;
            --font-barlow:      'Barlow', sans-serif;
            --font-plex:        'IBM Plex Sans', sans-serif;
        }

        [x-cloak] { display: none !important; }
        body { font-family: 'IBM Plex Sans', sans-serif; background-color: #F5F5F7; }
        h1, h2, h3, h4, h5 { font-family: 'Barlow', sans-serif; }

        /* Thin scrollbar */
        .scrollbar-thin::-webkit-scrollbar { width: 4px; height: 4px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #4297A0; border-radius: 2px; }

        /* Sidebar active nav item */
        .nav-active { background-color: rgba(66,151,160,0.15); color: #4297A0; }
        .nav-item:hover { background-color: rgba(255,255,255,0.08); }

        /* Smooth transitions */
        * { transition-property: color, background-color, border-color, box-shadow; transition-duration: 150ms; }
    </style>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @stack('head')
</head>
<body class="h-full" x-data="{ sidebarOpen: false }">

{{-- Mobile sidebar overlay --}}
<div
    x-show="sidebarOpen"
    x-cloak
    @click="sidebarOpen = false"
    class="fixed inset-0 z-20 bg-black/50 lg:hidden backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
></div>

{{-- Sidebar --}}
<aside
    class="fixed inset-y-0 left-0 z-30 w-64 bg-[#2F5061] flex flex-col transform transition-transform duration-300 ease-in-out lg:translate-x-0 scrollbar-thin overflow-y-auto"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    {{-- Logo --}}
    <div class="flex items-center justify-between px-5 h-16 border-b border-white/10 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
            <div class="w-8 h-8 rounded-lg bg-[#4297A0] flex items-center justify-center group-hover:bg-[#5aadb7] transition-colors shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <span class="text-white font-bold text-base font-[Barlow] tracking-wide">AdsAnalytics</span>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden text-white/60 hover:text-white p-1 rounded">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-5 space-y-1">
        @php
            $nav = [
                [
                    'route' => 'dashboard',
                    'label' => 'Dashboard',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
                ],
                [
                    'route' => 'upload.index',
                    'label' => 'Upload Report',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>',
                ],
                [
                    'route' => 'reports.index',
                    'label' => 'Reports History',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                ],
                [
                    'route' => 'chat.index',
                    'label' => 'AI Assistant',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
                ],
            ];
        @endphp

        @foreach($nav as $item)
            @php $isActive = request()->routeIs($item['route'] . '*'); @endphp
            <a
                href="{{ route($item['route']) }}"
                class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $isActive ? 'nav-active' : 'text-white/70 hover:text-white' }}"
            >
                <svg class="w-5 h-5 shrink-0 {{ $isActive ? 'text-[#4297A0]' : 'text-white/50' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $item['icon'] !!}
                </svg>
                <span>{{ $item['label'] }}</span>
                @if($isActive)
                    <div class="ml-auto w-1.5 h-1.5 rounded-full bg-[#4297A0]"></div>
                @endif
            </a>
        @endforeach

        {{-- Section label --}}
        <div class="pt-4 pb-1 px-3">
            <span class="text-white/30 text-xs font-semibold uppercase tracking-wider">Account</span>
        </div>
    </nav>

    {{-- User footer --}}
    <div class="px-3 pb-4 border-t border-white/10 pt-3 shrink-0">
        <div class="flex items-center gap-3 px-3 py-2 mb-2">
            <div class="w-8 h-8 rounded-full bg-[#4297A0] flex items-center justify-center text-white text-sm font-bold shrink-0">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name ?? 'User' }}</p>
                <p class="text-white/50 text-xs truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-item flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm text-white/60 hover:text-white transition-all">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Sign out
            </button>
        </form>
    </div>
</aside>

{{-- Main content wrapper --}}
<div class="lg:pl-64 flex flex-col min-h-screen">

    {{-- Top navbar --}}
    <header class="sticky top-0 z-10 bg-[#F5F5F7]/90 backdrop-blur border-b border-gray-200/60 h-16 flex items-center px-4 sm:px-6 gap-4 shrink-0">
        {{-- Mobile hamburger --}}
        <button
            @click="sidebarOpen = true"
            class="lg:hidden p-2 rounded-xl text-gray-500 hover:text-[#2F5061] hover:bg-white transition-all"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Page title --}}
        <h1 class="text-lg font-bold text-[#2F5061] font-[Barlow] truncate flex-1">
            {{ $title ?? 'Dashboard' }}
        </h1>

        {{-- Header actions slot --}}
        <div class="flex items-center gap-3">
            @isset($headerActions)
                {{ $headerActions }}
            @endisset
            <a
                href="{{ route('upload.index') }}"
                class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-[#E57F84] hover:bg-[#d96e73] text-white text-sm font-semibold rounded-xl transition-all shadow-sm hover:shadow-md"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Upload
            </a>
        </div>
    </header>

    {{-- Flash messages --}}
    <div class="px-4 sm:px-6 pt-4 space-y-3" x-data>
        @if(session('success'))
            <div
                x-data="{ show: true }" x-show="show" x-cloak
                x-init="setTimeout(() => show = false, 5000)"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl"
            >
                <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-emerald-700 text-sm flex-1">{{ session('success') }}</p>
                <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 ml-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div
                x-data="{ show: true }" x-show="show" x-cloak
                x-init="setTimeout(() => show = false, 7000)"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl"
            >
                <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-red-700 text-sm flex-1">{{ session('error') }}</p>
                <button @click="show = false" class="text-red-400 hover:text-red-600 ml-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif
    </div>

    {{-- Page slot --}}
    <main class="flex-1 p-4 sm:p-6">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="px-6 py-4 border-t border-gray-200/60 text-center text-xs text-gray-400">
        AdsAnalytics &mdash; Meta Ads Intelligence Platform
    </footer>
</div>

@stack('scripts')
</body>
</html>
