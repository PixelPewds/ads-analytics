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
<body class="bg-[#FAF9F6] min-h-screen">

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-30 w-64 bg-[#264653] text-white flex flex-col transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:flex"
    >
        @include('components.sidebar')
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col min-h-screen lg:ml-64">

        {{-- Navbar --}}
        <header class="sticky top-0 z-10 bg-white border-b border-[#D6E1EA] shadow-sm">
            @include('components.navbar')
        </header>

        {{-- Flash messages --}}
        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert-error mb-4">
                    {{ session('error') }}
                </div>
            @endif
        </div>

        {{-- Page Content --}}
        <main class="flex-1 px-6 py-6">
            @yield('content')
        </main>

    </div>

</div>

</body>
</html>