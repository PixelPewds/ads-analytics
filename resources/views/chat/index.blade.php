@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto flex flex-col" style="height: calc(100vh - 10rem);">

    {{-- Chat header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-extrabold text-[#264653]" style="font-family:'Barlow',sans-serif">AI Assistant</h2>
            <p class="text-sm text-[#6B7C8D] mt-0.5">Ask anything about your Meta Ads performance.</p>
        </div>

        {{-- Report context picker --}}
        <div class="flex items-center gap-2">
            @if($reports->isNotEmpty())
            <select id="reportSelect"
                    class="text-sm border border-[#D6E1EA] rounded-lg px-3 py-2 bg-white text-[#264653] focus:outline-none focus:ring-2 focus:ring-[#2A9D8F]/40">
                <option value="">No report context</option>
                @foreach($reports as $r)
                    <option value="{{ $r->id }}" {{ $reportId == $r->id ? 'selected' : '' }}>
                        {{ Str::limit($r->original_filename, 30) }}
                    </option>
                @endforeach
            </select>
            @endif

            <button id="clearBtn"
                    class="text-xs px-3 py-2 rounded-lg border border-[#D6E1EA] text-[#6B7C8D] hover:bg-[#E8EEF4] transition-colors">
                Clear
            </button>
        </div>
    </div>

    {{-- Chat window --}}
    <div id="chatWindow"
         class="flex-1 bg-white rounded-2xl border border-[#D6E1EA] p-5 overflow-y-auto flex flex-col gap-4 mb-4 shadow-sm">

        {{-- Welcome message --}}
        @if(empty($history))
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-[#2A9D8F]/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-[#2A9D8F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <div class="chat-bubble-assistant">
                <p class="text-sm font-semibold text-[#264653] mb-1">Hi! I'm your Meta Ads AI assistant.</p>
                <p class="text-sm text-[#6B7C8D]">Select a report above and ask me anything — campaign performance, budget optimisation, audience insights, or creative analysis.</p>
            </div>
        </div>
        @endif

        {{-- History --}}
        @foreach($history as $msg)
            @if($msg['role'] === 'user')
            <div class="flex justify-end">
                <div class="chat-bubble-user text-sm">{{ $msg['content'] }}</div>
            </div>
            @else
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-[#2A9D8F]/15 flex items-center justify-center flex-shrink-0 text-[#2A9D8F]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div class="chat-bubble-assistant text-sm whitespace-pre-wrap">{{ $msg['content'] }}</div>
            </div>
            @endif
        @endforeach

        {{-- Typing indicator (hidden by default) --}}
        <div id="typingIndicator" class="flex items-start gap-3 hidden">
            <div class="w-8 h-8 rounded-full bg-[#2A9D8F]/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-[#2A9D8F] animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
            </div>
            <div class="chat-bubble-assistant text-sm text-[#6B7C8D] italic">Thinking…</div>
        </div>

    </div>

    {{-- Input bar --}}
    <form id="chatForm" class="flex gap-3">
        <input
            type="text"
            id="chatInput"
            placeholder="Ask about your campaigns, spend, ROAS, audiences…"
            maxlength="2000"
            class="flex-1 border border-[#D6E1EA] rounded-xl px-4 py-3 text-sm text-[#264653] placeholder-[#9BBACB] focus:outline-none focus:ring-2 focus:ring-[#2A9D8F]/40 focus:border-[#2A9D8F]"
            autocomplete="off"
        >
        <button type="submit" id="sendBtn" class="btn-primary px-5 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Send
        </button>
    </form>

</div>

<script>
(function () {
    const SESSION_ID  = '{{ $sessionId }}';
    const CSRF        = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const chatWindow  = document.getElementById('chatWindow');
    const chatForm    = document.getElementById('chatForm');
    const chatInput   = document.getElementById('chatInput');
    const sendBtn     = document.getElementById('sendBtn');
    const typing      = document.getElementById('typingIndicator');
    const reportSelect= document.getElementById('reportSelect');
    const clearBtn    = document.getElementById('clearBtn');

    function scrollBottom() {
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }
    scrollBottom();

    function appendBubble(role, content) {
        const wrap = document.createElement('div');

        if (role === 'user') {
            wrap.className = 'flex justify-end';
            wrap.innerHTML = `<div class="chat-bubble-user text-sm">${escHtml(content)}</div>`;
        } else {
            wrap.className = 'flex items-start gap-3';
            wrap.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-[#2A9D8F]/15 flex items-center justify-center flex-shrink-0 text-[#2A9D8F]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div class="chat-bubble-assistant text-sm whitespace-pre-wrap">${escHtml(content)}</div>`;
        }

        chatWindow.insertBefore(wrap, typing);
        scrollBottom();
    }

    function escHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    chatForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const message = chatInput.value.trim();
        if (!message) return;

        appendBubble('user', message);
        chatInput.value = '';
        sendBtn.disabled = true;
        typing.classList.remove('hidden');
        scrollBottom();

        try {
            const body = {
                message,
                session_id: SESSION_ID,
                report_id: reportSelect ? (reportSelect.value || null) : null,
                _token: CSRF,
            };

            const resp = await fetch('{{ route('chat.message') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(body),
            });

            const data = await resp.json();
            typing.classList.add('hidden');
            appendBubble('assistant', data.reply || 'Sorry, something went wrong.');
        } catch (err) {
            typing.classList.add('hidden');
            appendBubble('assistant', 'Network error. Please try again.');
        } finally {
            sendBtn.disabled = false;
            chatInput.focus();
        }
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', async function () {
            if (!confirm('Clear this chat session?')) return;
            await fetch('{{ route('chat.clear') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ session_id: SESSION_ID, _token: CSRF }),
            });
            window.location.reload();
        });
    }

    chatInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });
})();
</script>

@endsection