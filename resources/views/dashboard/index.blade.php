<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'Dashboard') . ' — Ads Analytics' }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6, .font-barlow { font-family: 'Barlow', sans-serif; }

        /* Sidebar */
        .sidebar { background: #0F1C28; }
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 14px; border-radius: 10px;
            font-size: 0.8125rem; font-weight: 500; color: #7A9BB5;
            transition: background 0.15s, color 0.15s;
        }
        .nav-link:hover { background: #1C2E3E; color: #E0EAF4; }
        .nav-link.active { background: #1C2E3E; color: #ffffff; }
        .nav-link.active svg { color: #2A9D8F; }

        /* Cards */
        .chart-card {
            background: #ffffff;
            border: 1px solid #DDE8F0;
            border-radius: 16px;
            padding: 20px 22px;
            box-shadow: 0 1px 4px rgba(15,28,40,0.06);
        }

        /* KPI card */
        .kpi-card {
            background: #ffffff;
            border: 1px solid #DDE8F0;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(15,28,40,0.05);
        }

        /* Buttons */
        .btn-coral {
            background: #E76F51;
            color: #ffffff;
            font-weight: 600;
            border-radius: 10px;
            padding: 9px 18px;
            font-size: 0.8125rem;
            transition: background 0.15s, transform 0.1s;
            display: inline-flex; align-items: center; gap: 6px;
            border: none; cursor: pointer;
        }
        .btn-coral:hover { background: #d05e40; }
        .btn-coral:active { transform: scale(0.97); }

        .btn-teal {
            background: #2A9D8F;
            color: #ffffff;
            font-weight: 600;
            border-radius: 10px;
            padding: 9px 18px;
            font-size: 0.8125rem;
            transition: background 0.15s;
            display: inline-flex; align-items: center; gap: 6px;
            border: none; cursor: pointer;
        }
        .btn-teal:hover { background: #238a7e; }

        .btn-ghost {
            background: transparent;
            color: #4A6A82;
            font-weight: 500;
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 0.8125rem;
            border: 1px solid #D0DCE8;
            cursor: pointer;
            transition: background 0.15s;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-ghost:hover { background: #F0F6FA; }

        /* Form inputs */
        .form-input {
            background: #F7FAFB;
            border: 1px solid #D0DCE8;
            border-radius: 9px;
            padding: 9px 12px;
            font-size: 0.8125rem;
            color: #1A2E3E;
            width: 100%;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-input:focus {
            outline: none;
            border-color: #2A9D8F;
            box-shadow: 0 0 0 3px rgba(42,157,143,0.12);
        }
        .form-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #4A6A82;
            display: block;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* Drop zone */
        .drop-zone {
            border: 2px dashed #C0D4E4;
            border-radius: 14px;
            background: #F7FAFB;
            transition: border-color 0.2s, background 0.2s;
        }
        .drop-zone:hover, .drop-zone.drag-over {
            border-color: #2A9D8F;
            background: #EBF7F5;
        }

        /* Table */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th {
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.06em; color: #6B8DA6; padding: 10px 12px;
            border-bottom: 1px solid #E8EEF4; text-align: left;
        }
        .data-table td {
            font-size: 0.8125rem; color: #1A2E3E; padding: 10px 12px;
            border-bottom: 1px solid #F0F5F9;
        }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: #F8FBFD; }

        /* Badge */
        .badge-teal { background:#DFFAF6; color:#1A7A70; border-radius:20px; padding:2px 9px; font-size:0.7rem; font-weight:600; }
        .badge-coral { background:#FDECEA; color:#C0422A; border-radius:20px; padding:2px 9px; font-size:0.7rem; font-weight:600; }
        .badge-amber { background:#FEF3C7; color:#92400E; border-radius:20px; padding:2px 9px; font-size:0.7rem; font-weight:600; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #F0F5F9; }
        ::-webkit-scrollbar-thumb { background: #C0D4E4; border-radius: 99px; }

        /* Notification flash */
        .flash-bar {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 16px; border-radius: 10px; font-size: 0.8125rem;
            font-weight: 500; margin-bottom: 18px;
        }
        .flash-success { background: #E6F9F5; color: #166B60; border: 1px solid #A8E8DD; }
        .flash-error   { background: #FEF1EE; color: #9B3722; border: 1px solid #F5C3B5; }

        /* Mobile overlay */
        @media (max-width: 1023px) {
            .sidebar-overlay { display: none; }
            .sidebar-overlay.open { display: block; }
        }
    </style>

    @stack('head')
</head>
<body class="h-full bg-[#EEF3F8]" x-data="{ sidebarOpen: false }">

{{-- Mobile Sidebar Overlay --}}
<div
    class="sidebar-overlay fixed inset-0 bg-black/50 z-20 lg:hidden"
    :class="{ 'open': sidebarOpen }"
    @click="sidebarOpen = false"
    x-show="sidebarOpen"
    x-transition:enter="transition-opacity duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
></div>

<div class="flex h-full min-h-screen">

    {{-- ─── Sidebar ─────────────────────────────────────────── --}}
    <aside
        class="sidebar fixed inset-y-0 left-0 z-30 w-60 flex flex-col transition-transform duration-200
               lg:static lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        {{-- Logo --}}
        <div class="flex items-center gap-2.5 px-5 py-5 border-b border-white/8">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#2A9D8F] to-[#1A7A70] flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-bold text-white font-barlow leading-tight">Ads Analytics</div>
                <div class="text-[10px] text-[#4A7A99] font-medium">Meta Ads Intelligence</div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            <div class="text-[10px] font-bold text-[#3A5A72] uppercase tracking-widest px-3 mb-2">Main</div>

            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10-4a1 1 0 011-1h4a1 1 0 011 1v8a1 1 0 01-1 1h-4a1 1 0 01-1-1v-8z"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('upload.index') }}"
               class="nav-link {{ request()->routeIs('upload.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Upload Report
            </a>

            <a href="{{ route('reports.index') }}"
               class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Reports
            </a>

            <a href="{{ route('chat.index') }}"
               class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                AI Assistant
            </a>
        </nav>

        {{-- User footer --}}
        @auth
        <div class="border-t border-white/8 px-3 py-4">
            <div class="flex items-center gap-3 px-2">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#2A9D8F] to-[#E76F51] flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'User' }}</div>
                    <div class="text-[10px] text-[#4A7A99] truncate">{{ auth()->user()->email ?? '' }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2.5">
                @csrf
                <button type="submit"
                    class="nav-link w-full text-left hover:!text-[#FF6B6B]">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign Out
                </button>
            </form>
        </div>
        @endauth
    </aside>

    {{-- ─── Main ────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Top bar --}}
        <header class="h-14 bg-white border-b border-[#DDE8F0] flex items-center px-5 gap-4 flex-shrink-0 shadow-sm">
            {{-- Mobile hamburger --}}
            <button class="lg:hidden text-[#4A6A82] hover:text-[#1A2E3E]" @click="sidebarOpen = !sidebarOpen">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="flex-1">
                <h1 class="text-base font-bold text-[#0F1C28] font-barlow">{{ $title ?? 'Dashboard' }}</h1>
            </div>

            {{-- Quick upload CTA --}}
            <a href="{{ route('upload.index') }}" class="btn-coral text-xs px-3 py-2 hidden sm:inline-flex">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Upload
            </a>
        </header>

        {{-- Content area --}}
        <main class="flex-1 overflow-y-auto p-5 lg:p-7">

            {{-- Flash messages --}}
            @if(session('success'))
            <div class="flash-bar flash-success" x-data x-init="setTimeout(() => $el.remove(), 5000)">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="flash-bar flash-error" x-data x-init="setTimeout(() => $el.remove(), 5000)">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('error') }}
            </div>
            @endif
            @if($errors->any())
            <div class="flash-bar flash-error">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <ul class="space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
