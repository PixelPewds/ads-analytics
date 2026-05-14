$sessionId,
            'report_id'  => $reportId,
            'role'       => 'user',
            'content'    => $message,
        ]);

        if (!config('services.openai.api_key')) {
            $reply = "AI chat is not configured. Please add your OPENAI_API_KEY to the .env file to enable the AI assistant.";
            $this->saveAssistantMessage($reply, $sessionId, $reportId);
            return $reply;
        }

        try {
            $messages = $this->buildMessages($sessionId, $reportId, $message);

            $response = OpenAI::chat()->create([
                'model'       => 'gpt-4o-mini',
                'temperature' => 0.5,
                'max_tokens'  => 1024,
                'messages'    => $messages,
            ]);

            $reply = $response->choices[0]->message->content;
            $this->saveAssistantMessage($reply, $sessionId, $reportId, $response->usage->totalTokens ?? 0);

            return $reply;
        } catch (\Throwable $e) {
            Log::error('Chat AI error: ' . $e->getMessage());
            $reply = 'Sorry, I encountered an error processing your request. Please try again.';
            $this->saveAssistantMessage($reply, $sessionId, $reportId);
            return $reply;
        }
    }

    private function buildMessages(string $sessionId, ?int $reportId, string $userMessage): array
    {
        $systemContent = $this->buildSystemPrompt($reportId);

        $messages = [
            ['role' => 'system', 'content' => $systemContent],
        ];

        // Last 10 messages for context
        $history = ChatHistory::where('session_id', $sessionId)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->reverse();

        foreach ($history as $item) {
            $messages[] = ['role' => $item->role, 'content' => $item->content];
        }

        return $messages;
    }

    private function buildSystemPrompt(?int $reportId): string
    {
        $base = "You are an expert Meta Ads analytics assistant. You help users understand their advertising performance, identify opportunities, and optimize campaigns. Be concise, specific, and data-driven. When referencing metrics, always include the actual numbers from the data.";

        if (!$reportId) {
            return $base . "\n\nNo report is currently selected. Guide the user to upload a report first.";
        }

        $report = Report::find($reportId);
        if (!$report || !$report->isProcessed()) {
            return $base;
        }

        try {
            $kpis = $this->analytics->getKpis($reportId);
            $campaigns = $this->analytics->getCampaignBreakdown($reportId);

            $context = "\n\n=== CURRENT REPORT: {$report->original_filename} ===";
            $context .= "\nDate Range: {$report->date_range_label}";
            $context .= "\nTotal Spend: $" . number_format($kpis['total_spend'], 2);
            $context .= "\nTotal Impressions: " . number_format($kpis['total_impressions']);
            $context .= "\nTotal Clicks: " . number_format($kpis['total_clicks']);
            $context .= "\nCTR: {$kpis['ctr']}%";
            $context .= "\nCPC: $" . $kpis['cpc'];
            $context .= "\nTotal Conversions: " . $kpis['total_conversions'];
            $context .= "\nCAC: $" . $kpis['cac'];
            $context .= "\nConversations: " . $kpis['total_conversations'];
            $context .= "\nCost per Conversation: $" . $kpis['cost_per_conversation'];
            $context .= "\nROAS: " . $kpis['roas'] . "x";

            if (!empty($campaigns)) {
                $context .= "\n\n=== CAMPAIGNS ===";
                foreach (array_slice($campaigns, 0, 8) as $c) {
                    $context .= "\n• {$c['name']}: Spend $" . round($c['spend'], 2) . " | Conv: {$c['conversions']} | ROAS: {$c['roas']}x";
                }
            }

            $recs = Recommendation::where('report_id', $reportId)->get();
            if ($recs->count()) {
                $context .= "\n\n=== AI RECOMMENDATIONS ===";
                foreach ($recs->groupBy('type') as $type => $items) {
                    $context .= "\n" . Recommendation::typeLabel($type) . ":";
                    foreach ($items as $item) {
                        $context .= "\n  - {$item->title}";
                    }
                }
            }

            return $base . $context;
        } catch (\Throwable) {
            return $base;
        }
    }

    private function saveAssistantMessage(string $content, string $sessionId, ?int $reportId, int $tokens = 0): void
    {
        ChatHistory::create([
            'session_id'  => $sessionId,
            'report_id'   => $reportId,
            'role'        => 'assistant',
            'content'     => $content,
            'token_count' => $tokens,
        ]);
    }

    public function getHistory(string $sessionId, int $limit = 50): array
    {
        return ChatHistory::where('session_id', $sessionId)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content, 'time' => $m->created_at->format('H:i')])
            ->toArray();
    }

    public function clearSession(string $sessionId): void
    {
        ChatHistory::where('session_id', $sessionId)->delete();
    }
}