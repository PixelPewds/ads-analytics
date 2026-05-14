latest()->get();
        $reportId  = $request->get('report_id') ?? $reports->first()?->id;
        $sessionId = $request->session()->get('chat_session_id', (string)\Illuminate\Support\Str::uuid());
        $request->session()->put('chat_session_id', $sessionId);

        $history = $this->chatService->getHistory($sessionId);

        return view('chat.index', compact('reports', 'reportId', 'sessionId', 'history'))
            ->with('title', 'AI Assistant');
    }

    public function message(Request $request): JsonResponse
    {
        $request->validate([
            'message'    => ['required', 'string', 'max:2000'],
            'session_id' => ['required', 'string'],
            'report_id'  => ['nullable', 'integer', 'exists:reports,id'],
        ]);

        $reply = $this->chatService->sendMessage(
            $request->input('message'),
            $request->input('session_id'),
            $request->input('report_id'),
        );

        return response()->json(['reply' => $reply]);
    }

    public function clearHistory(Request $request): JsonResponse
    {
        $sessionId = $request->input('session_id');
        if ($sessionId) {
            $this->chatService->clearSession($sessionId);
        }
        return response()->json(['status' => 'cleared']);
    }
}