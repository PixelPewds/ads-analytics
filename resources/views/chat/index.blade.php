@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto flex flex-col h-[calc(100vh-7rem)]"
     x-data="chatApp({
         sessionId: '{{ $sessionId }}',
         reportId: {{ $reportId ?? 'null' }},
         csrfToken: '{{ csrf_token() }}',
     })"
     x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4 flex-shrink-0">
        <div>
            <h2 class="text-xl font-extrabold text-[#264653] font-barlow">AI Assistant</h2>
            <p class="text-xs text-[#6B7C8D] mt-0.5">Ask anything about your Meta Ads performance.</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Report picker --}}
            @if($reports->isNotEmpty())
            <select x-model="reportId" class="form-input !w-auto text-sm">
                <option value="">Select report…</option>
                @foreach($reports as $r)
                <option value="{{ $r->id }}" {{ $reportId == $r->id ? 'selected' : '' }}>
                    {{ Str::limit($r->original_filename, 30) }}
                </option>
                @endforeach
            </select>
            @endif

            {{-- Clear history --}}
            <button @click="clearHistory()"
                    class="btn-ghost text-xs"
                    title="Clear conversation">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Clear
            </button>
        </div>
    </div>

    {{-- Messages panel --}}
    <div class="chart-card flex-1 overflow-y-auto mb-3 min-h-0" id="messagesPanel">

        {{-- Welcome --}}
        @if(empty($history))
        <div class="flex flex-col items-center justify-center h-full py-12 text-center" x-show="messages.length === 0">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#2A9D8F] to-[#1A7A70] flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <h3 class="font-barlow font-bold text-[#264653] text-lg mb-1">Hi! I'm your Meta Ads AI assistant.</h3>
            <p class="text-sm text-[#6B7C8D] max-w-sm leading-relaxed">
                Select a report above and ask me anything — campaign performance,
                budget optimisation, audience insights, or creative analysis.
            </p>
            {{-- Quick prompts --}}
            <div class="grid grid-cols-2 gap-2 mt-5 w-full max-w-sm">
                @foreach([
                    'What campaign has the best ROAS?',
                    'Which ads should I pause?',
                    'How can I lower my CPC?',
                    'Summarise this report',
                ] as $prompt)
                <button @click="quickPrompt('{{ $prompt }}')"
                        class="text-left text-xs p-3 rounded-xl border border-[#DDE8F0] bg-[#F7FAFB]
                               hover:bg-[#EEF3F8] hover:border-[#2A9D8F] text-[#4A6A82] transition-colors">
                    {{ $prompt }}
                </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Seeded history (from server) --}}
        <template x-if="messages.length === 0">
            <div>
                @foreach($history as $msg)
                @if($msg['role'] === 'user')
                <div class="flex justify-end mb-3">
                    <div class="max-w-[75%] bg-[#0F1C28] text-white text-sm rounded-2xl rounded-tr-sm px-4 py-2.5 leading-relaxed">
                        {{ $msg['content'] }}
                    </div>
                </div>
                @else
                <div class="flex items-start gap-2.5 mb-3">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#2A9D8F] to-[#1A7A70] flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3"/>
                        </svg>
                    </div>
                    <div class="max-w-[75%] bg-[#F0F6FA] text-[#1A2E3E] text-sm rounded-2xl rounded-tl-sm px-4 py-2.5 leading-relaxed">
                        {!! nl2br(e($msg['content'])) !!}
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </template>

        {{-- Dynamic messages (Alpine) --}}
        <template x-for="(msg, i) in messages" :key="i">
            <div>
                {{-- User --}}
                <div x-show="msg.role === 'user'" class="flex justify-end mb-3">
                    <div class="max-w-[75%] bg-[#0F1C28] text-white text-sm rounded-2xl rounded-tr-sm px-4 py-2.5 leading-relaxed"
                         x-text="msg.content"></div>
                </div>
                {{-- Assistant --}}
                <div x-show="msg.role === 'assistant'" class="flex items-start gap-2.5 mb-3">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#2A9D8F] to-[#1A7A70] flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3"/>
                        </svg>
                    </div>
                    <div class="max-w-[75%] bg-[#F0F6FA] text-[#1A2E3E] text-sm rounded-2xl rounded-tl-sm px-4 py-2.5 leading-relaxed"
                         x-html="msg.html || msg.content"></div>
                </div>
            </div>
        </template>

        {{-- Typing indicator --}}
        <div x-show="loading" class="flex items-center gap-2.5 mb-3">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#2A9D8F] to-[#1A7A70] flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3"/>
                </svg>
            </div>
            <div class="bg-[#F0F6FA] rounded-2xl rounded-tl-sm px-4 py-3">
                <div class="flex gap-1.5 items-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#2A9D8F] animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#2A9D8F] animate-bounce" style="animation-delay:150ms"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#2A9D8F] animate-bounce" style="animation-delay:300ms"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Input bar --}}
    <div class="chart-card flex-shrink-0">
        <form @submit.prevent="sendMessage()" class="flex gap-2">
            <input
                type="text"
                x-model="input"
                placeholder="Ask about your campaigns…"
                :disabled="loading"
                class="form-input flex-1"
                @keydown.enter.prevent="sendMessage()"
                id="chatInput"
                autocomplete="off"
            >
            <button
                type="submit"
                class="btn-teal flex-shrink-0 px-4"
                :disabled="loading || !input.trim()"
                :class="(!input.trim() || loading) ? 'opacity-50 cursor-not-allowed' : ''"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <span class="hidden sm:inline">Send</span>
            </button>
        </form>
        <p class="text-[10px] text-[#AABFCC] mt-2">
            AI responses are generated based on your report data.
            Always verify recommendations before making budget decisions.
        </p>
    </div>

</div>
@endsection

@push('scripts')
<script>
function chatApp({ sessionId, reportId, csrfToken }) {
    return {
        sessionId,
        reportId,
        csrfToken,
        input: '',
        messages: [],
        loading: false,

        init() {
            this.scrollToBottom();
        },

        async sendMessage() {
            const text = this.input.trim();
            if (!text || this.loading) return;

            this.messages.push({ role: 'user', content: text });
            this.input = '';
            this.loading = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const res = await fetch('{{ route("chat.message") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        message: text,
                        session_id: this.sessionId,
                        report_id: this.reportId || null,
                    })
                });

                const data = await res.json();
                const reply = data.reply || 'Sorry, I could not generate a response.';
                this.messages.push({
                    role: 'assistant',
                    content: reply,
                    html: this.formatReply(reply),
                });
            } catch (e) {
                this.messages.push({
                    role: 'assistant',
                    content: 'Connection error. Please try again.',
                    html: '<span class="text-[#C0422A]">Connection error. Please try again.</span>',
                });
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        quickPrompt(text) {
            this.input = text;
            this.sendMessage();
        },

        async clearHistory() {
            try {
                await fetch('{{ route("chat.clear") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ session_id: this.sessionId })
                });
                this.messages = [];
            } catch(e) {}
        },

        formatReply(text) {
            // Basic markdown-like formatting
            return text
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.+?)\*/g, '<em>$1</em>')
                .replace(/`(.+?)`/g, '<code class="bg-white/60 px-1 rounded text-xs font-mono">$1</code>')
                .replace(/\n/g, '<br>');
        },

        scrollToBottom() {
            const panel = document.getElementById('messagesPanel');
            if (panel) panel.scrollTop = panel.scrollHeight;
        }
    };
}
</script>
@endpush
