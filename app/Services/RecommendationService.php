generateFallback($report);
            return false;
        }

        try {
            $kpis     = $this->analytics->getKpis($report->id);
            $timeline = $this->analytics->getTimeline($report->id);
            $campaigns = $this->analytics->getCampaignBreakdown($report->id);

            $context  = $this->buildContext($kpis, $timeline, $campaigns, $report, $previousReport);
            $response = OpenAI::chat()->create([
                'model'       => 'gpt-4o-mini',
                'temperature' => 0.4,
                'messages'    => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $context],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content;
            $data    = json_decode($content, true);

            if (!$data || !is_array($data)) {
                throw new \RuntimeException('Invalid AI response format.');
            }

            $this->storeRecommendations($report, $data);
            return true;
        } catch (\Throwable $e) {
            Log::warning('AI recommendation generation failed: ' . $e->getMessage());
            $this->generateFallback($report);
            return false;
        }
    }

    private function storeRecommendations(Report $report, array $data): void
    {
        Recommendation::where('report_id', $report->id)->delete();

        $types = ['working', 'not_working', 'at_risk', 'needs_scaling', 'recommendations'];
        $order = 0;

        foreach ($types as $type) {
            $items = $data[$type] ?? [];
            if (!is_array($items)) continue;

            foreach ($items as $item) {
                if (empty($item['title']) || empty($item['content'])) continue;

                Recommendation::create([
                    'report_id'  => $report->id,
                    'type'       => $type,
                    'title'      => substr($item['title'], 0, 255),
                    'content'    => $item['content'],
                    'sort_order' => $order++,
                ]);
            }
        }
    }

    private function buildContext(array $kpis, array $timeline, array $campaigns, Report $report, ?Report $previousReport): string
    {
        $dateRange = $report->date_range_start
            ? $report->date_range_start->format('Y-m-d') . ' to ' . $report->date_range_end?->format('Y-m-d')
            : 'Unknown';

        $lines   = ["META ADS PERFORMANCE REPORT — {$dateRange}", ""];
        $lines[] = "=== OVERALL KPIs ===";
        $lines[] = "Total Spend: $" . number_format($kpis['total_spend'], 2);
        $lines[] = "Total Impressions: " . number_format($kpis['total_impressions']);
        $lines[] = "Total Clicks: " . number_format($kpis['total_clicks']);
        $lines[] = "CTR: " . $kpis['ctr'] . "%";
        $lines[] = "CPC: $" . $kpis['cpc'];
        $lines[] = "Total Conversions: " . $kpis['total_conversions'];
        $lines[] = "CAC (Cost per Conversion): $" . $kpis['cac'];
        $lines[] = "Total Conversations: " . $kpis['total_conversations'];
        $lines[] = "Cost per Conversation: $" . $kpis['cost_per_conversation'];
        $lines[] = "Total Revenue: $" . number_format($kpis['total_revenue'], 2);
        $lines[] = "ROAS: " . $kpis['roas'] . "x";

        if (!empty($campaigns)) {
            $lines[] = "";
            $lines[] = "=== TOP CAMPAIGNS ===";
            foreach (array_slice($campaigns, 0, 5) as $c) {
                $lines[] = "• {$c['name']}: Spend $" . round($c['spend'], 2) . " | Conv: {$c['conversions']} | ROAS: {$c['roas']}x | CPC: $" . $c['cpc'];
            }
        }

        if ($previousReport) {
            $comparison = (new AnalyticsService())->compareReports($report, $previousReport);
            $changes    = $comparison['changes'];
            $lines[]    = "";
            $lines[]    = "=== COMPARISON WITH PREVIOUS REPORT ({$previousReport->date_range_label}) ===";
            foreach ($changes as $metric => $ch) {
                if ($ch['pct_change'] !== null) {
                    $arrow   = $ch['pct_change'] >= 0 ? '▲' : '▼';
                    $lines[] = "• " . ucwords(str_replace('_', ' ', $metric)) . ": {$arrow} " . abs($ch['pct_change']) . "% (was {$ch['previous']}, now {$ch['current']})";
                }
            }
        }

        $lines[] = "";
        $lines[] = "Analyze the above data and return structured recommendations in the required JSON format.";

        return implode("\n", $lines);
    }

    private function systemPrompt(): string
    {
        return <<<prompt>id)->delete();

        $kpis = (new AnalyticsService())->getKpis($report->id);

        $fallbacks = [
            [
                'type'    => 'recommendations',
                'title'   => 'Connect OpenAI to enable AI recommendations',
                'content' => 'Add your OpenAI API key to the .env file (OPENAI_API_KEY) to generate intelligent, data-driven recommendations for this report. Your campaign data is ready to be analyzed.',
            ],
        ];

        if ($kpis['ctr'] > 0) {
            $fallbacks[] = [
                'type'    => 'working',
                'title'   => "Campaign generating clicks with {$kpis['ctr']}% CTR",
                'content' => "Your ads are generating impressions and " . number_format($kpis['total_clicks']) . " total clicks with a CTR of {$kpis['ctr']}%. Total spend stands at $" . number_format($kpis['total_spend'], 2) . " across this reporting period.",
            ];
        }

        foreach ($fallbacks as $i => $fb) {
            Recommendation::create(array_merge($fb, ['report_id' => $report->id, 'sort_order' => $i]));
        }
    }
}