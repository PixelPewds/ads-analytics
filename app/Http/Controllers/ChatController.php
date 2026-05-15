<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chatService) {}

    /**
     * Show the chat interface.
     */
    public function index(Request $request): View
    {
        // Fetch all processed reports for the dropdown
        $reports = Report::where('status', 'processed')
            ->orderByDesc('created_at')
            ->get(['id', 'original_filename', 'date_range_start', 'date_range_end']);

        // Pre-select a report via query param, or default to latest
        $reportId = $request->query('report_id')
            ?? $reports->first()?->id;

        // Establish or restore a session for this user
        $sessionId = $request->session()->get('chat_session_id', (string) Str::uuid());
        $request->session()->put('chat_session_id', $sessionId);

        // Load existing history for this session
        $history = $this->chatService->getHistory($sessionId);

        return view('chat.index', compact('reports', 'reportId', 'sessionId', 'history'))
            ->with('title', 'AI Assistant');
    }

    /**
     * Handle an incoming chat message and return a response.
     */
    public function message(Request $request): JsonResponse
    {
        $request->validate([
            'message'    => ['required', 'string', 'max:2000'],
            'session_id' => ['required', 'string'],
            'report_id'  => ['nullable', 'integer', 'exists:reports,id'],
        ]);

        $message   = trim($request->input('message'));
        $sessionId = $request->input('session_id');
        $reportId  = $request->input('report_id');

        try {
            $reply = $this->chatService->respond(
                $message,
                $sessionId,
                $reportId ? (int) $reportId : null
            );

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
    public function clearHistory(Request $request): JsonResponse|RedirectResponse
    {
        $sessionId = $request->input('session_id')
            ?? $request->session()->get('chat_session_id');

        if ($sessionId) {
            $this->chatService->clearSession($sessionId);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Chat history cleared.');
    }
}
