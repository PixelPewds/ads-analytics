{{-- ══════════════════════════════════════════════════════
     Sidebar — premium editorial dark panel (#264653)
══════════════════════════════════════════════════════ --}}

<div class="flex flex-col h-full">

    {{-- Brand --}}
    <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
        <div class="w-8 h-8 rounded-lg bg-[#2A9D8F] flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <div>
            <p class="text-white font-bold text-sm leading-tight" style="font-family:'Barlow',sans-serif">Ads Analytics</p>
            <p class="text-white/50 text-xs">Meta Ads Intelligence</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

        <p class="px-3 pt-2 pb-1.5 text-xs font-semibold uppercase tracking-widest text-white/30" style="font-family:'Barlow',sans-serif">
            Analytics
        </p>

        <a href="{{ route('dashboard') }}"
           class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active !text-[#2A9D8F] bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/8' }}">
            <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('reports.index') }}"
           class="sidebar-nav-link {{ request()->routeIs('reports.*') ? 'active !text-[#2A9D8F] bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/8' }}">
            <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Reports
        </a>

        <a href="{{ route('chat.index') }}"
           class="sidebar-nav-link {{ request()->routeIs('chat.*') ? 'active !text-[#2A9D8F] bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/8' }}">
            <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            AI Assistant
        </a>

        <p class="px-3 pt-4 pb-1.5 text-xs font-semibold uppercase tracking-widest text-white/30" style="font-family:'Barlow',sans-serif">
            Data
        </p>

        <a href="{{ route('upload.index') }}"
           class="sidebar-nav-link {{ request()->routeIs('upload.*') ? 'active !text-[#2A9D8F] bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/8' }}">
            <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Upload Report
        </a>

    </nav>

    {{-- User panel --}}
    <div class="px-4 py-4 border-t border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-[#2A9D8F]/30 flex items-center justify-center text-[#2A9D8F] font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-xs font-semibold truncate">{{ auth()->user()->name ?? 'User' }}</p>
                <p class="text-white/40 text-xs truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Log out" class="text-white/40 hover:text-white/80 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

</div>