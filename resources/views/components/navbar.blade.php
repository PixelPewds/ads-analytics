<div class="flex items-center justify-between h-14 px-4 sm:px-6 lg:px-8">

    {{-- Left: hamburger (mobile) + page title --}}
    <div class="flex items-center gap-3">
        {{-- Mobile hamburger --}}
        <button
            @click="sidebarOpen = !sidebarOpen"
            class="lg:hidden p-1.5 rounded-md text-[#264653] hover:bg-[#E8EEF4] transition-colors"
            aria-label="Toggle sidebar"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <h1 class="text-base font-bold text-[#264653]" style="font-family:'Barlow',sans-serif">
            {{ $title ?? 'Dashboard' }}
        </h1>
    </div>

    {{-- Right: actions --}}
    <div class="flex items-center gap-3">
        {{-- Quick upload CTA --}}
        <a href="{{ route('upload.index') }}"
           class="hidden sm:inline-flex items-center gap-1.5 btn-coral text-xs py-2 px-3">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Upload
        </a>

        {{-- AI chat shortcut --}}
        <a href="{{ route('chat.index') }}"
           class="inline-flex items-center gap-1.5 bg-[#E8EEF4] hover:bg-[#D6E1EA] text-[#264653] px-3 py-2 rounded-lg text-xs font-medium transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            AI Chat
        </a>
    </div>

</div>