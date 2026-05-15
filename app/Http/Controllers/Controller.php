<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ChatHistory;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chat
    ) {}

    /**
     * Show the chat interface.
     */
    public function index(Request $request): View
    {
        // Allow pre-selecting a report context via query param
        $reportId = $request->query('report_id');

        $report = $reportId
            ? Report::where('status', 'processed')->find($reportId)
            : Report::where('status', 'processed')->latest()->first();

        $allReports = Report::where('status', 'processed')
            ->orderByDesc('created_at')
            ->get(['id', 'original_filename', 'date_range_start', 'date_range_end']);

        // Establish or restore a session for this user
        $sessionId = $this->resolveSessionId($request);

        // Load existing history for this session
        $history = $this->chat->getHistory($sessionId);

        return view('chat.index', compact('report', 'allReports', 'history', 'sessionId'));
    }

    /**
     * Handle an incoming chat message and return a response.
     */
    public function message(Request $request): JsonResponse
    {
        $request->validate([
            'message'   => ['required', 'string', 'max:2000'],
            'report_id' => ['nullable', 'integer', 'exists:reports,id'],
        ]);

        $sessionId = $this->resolveSessionId($request);
        $message   = trim($request->input('message'));
        $reportId  = $request->input('report_id');

        try {
            $reply = $this->chat->respond($message, $sessionId, $reportId ? (int)$reportId : null);

            return response()->json([
                'success' => true,
                'reply'   => $reply,
                'time'    => now()->format('H:i'),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'reply'   => 'I encountered an error processing your message. Please try again.',
                'time'    => now()->format('H:i'),
            ], 500);
        }
    }

    /**
     * Clear chat history for the current session.
     */
    public function clearHistory(Request $request): RedirectResponse|JsonResponse
    {
        $sessionId = $this->resolveSessionId($request);
        $this->chat->clearSession($sessionId);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Chat history cleared.');
    }

    /**
     * Resolve (or create) the chat session ID stored in the PHP session.
     */
    private function resolveSessionId(Request $request): string
    {
        $key = 'chat_session_id';

        if (! $request->session()->has($key)) {
            $request->session()->put($key, \Str::uuid()->toString());
        }

        return $request->session()->get($key);
    }
}
