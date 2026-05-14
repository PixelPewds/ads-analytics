<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ ($title ?? 'Dashboard') . ' — Ads Analytics' }}</title>

    <!-- Fonts (preloaded via CSS @import) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FAF9F6]">

    {{-- Mobile sidebar backdrop --}}
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 z-20 bg-slate-900/40 lg:hidden"
    ></div>

    {{-- Sidebar --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-30 w-64 bg-[#264653] text-white flex flex-col transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:inset-auto"
    >
        @include('components.sidebar')
    </aside>

    {{-- Main wrapper --}}
    <div class="flex flex-col flex-1 min-h-screen lg:pl-64">

        {{-- Top navbar --}}
        <header class="sticky top-0 z-10 bg-white border-b border-[#D6E1EA] shadow-sm">
            @include('components.navbar')
        </header>

        {{-- Flash messages --}}
        <div class="px-4 pt-4 sm:px-6 lg:px-8 max-w-screen-2xl mx-auto w-full">
            @if(session('success'))
                <div class="alert-success mb-4 flex items-start gap-3">
                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="alert-error mb-4 flex items-start gap-3">
                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            @if($errors->any())
                <div class="alert-error mb-4">
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Page content --}}
        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8 max-w-screen-2xl mx-auto w-full">
            @yield('content')
        </main>

        <footer class="px-4 py-4 sm:px-6 lg:px-8 text-center text-xs text-[#6B7C8D] border-t border-[#D6E1EA]">
            &copy; {{ date('Y') }} Ads Analytics &mdash; Premium Meta Ads Intelligence
        </footer>
    </div>

</body>
</html>