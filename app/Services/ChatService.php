<?php

namespace App\Services;

use App\Models\ChatHistory;
use App\Models\Recommendation;
use App\Models\Report;
use Illuminate\Support\Facades\Log;

class ChatService
{
    public function __construct(
        private readonly AnalyticsService $analytics
    ) {}

    /**
     * Process a user message and return a reply.
     */
    public function respond(string $message, string $sessionId, ?int $reportId): string
    {
        // Save the user message to history
        ChatHistory::create([
            'session_id' => $sessionId,
            'report_id'  => $reportId,
            'role'       => 'user',
            'content'    => $message,
        ]);

        $reply = $this->generateRuleBasedReply($message, $reportId);

        $this->saveAssistantMessage($reply, $sessionId, $reportId);

        return $reply;
    }

    /**
     * Generate a rule-based reply based on the message content and report context.
     */
    private function generateRuleBasedReply(string $message, ?int $reportId): string
    {
        $lower = strtolower($message);

        // No report selected
        if (! $reportId) {
            return "I don't have a report loaded to analyse yet. Please upload a Meta Ads CSV or XLSX file via the **Upload** page, then come back here to ask questions about your data.";
        }

        $report = Report::find($reportId);
        if (! $report || ! $report->isProcessed()) {
            return "The selected report hasn't finished processing yet. Please try again in a moment.";
        }

        try {
            $kpis      = $this->analytics->getKpis($reportId);
            $campaigns = $this->analytics->getCampaignBreakdown($reportId);
        } catch (\Throwable $e) {
            Log::error('ChatService analytics error: ' . $e->getMessage());
            return "I encountered an error reading your report data. Please try again.";
        }

        // ── Keyword-based routing ─────────────────────────────────────────────

        if ($this->contains($lower, ['spend', 'cost', 'budget', 'money', 'spent'])) {
            return $this->replySpend($kpis, $campaigns);
        }

        if ($this->contains($lower, ['ctr', 'click through', 'click-through', 'clicks'])) {
            return $this->replyCtr($kpis, $campaigns);
        }

        if ($this->contains($lower, ['cpc', 'cost per click'])) {
            return $this->replyCpc($kpis, $campaigns);
        }

        if ($this->contains($lower, ['roas', 'return on ad', 'revenue', 'return'])) {
            return $this->replyRoas($kpis, $campaigns);
        }

        if ($this->contains($lower, ['conversion', 'lead', 'purchase', 'cac', 'acquisition'])) {
            return $this->replyConversions($kpis, $campaigns);
        }

        if ($this->contains($lower, ['conversation', 'message', 'messaging', 'chat'])) {
            return $this->replyConversations($kpis);
        }

        if ($this->contains($lower, ['campaign', 'campaigns', 'best', 'top', 'worst', 'performing'])) {
            return $this->replyCampaigns($campaigns);
        }

        if ($this->contains($lower, ['recommend', 'suggestion', 'improve', 'optimis', 'optimiz', 'tip', 'advice'])) {
            return $this->replyRecommendations($reportId, $kpis);
        }

        if ($this->contains($lower, ['summary', 'overview', 'how', 'performance', 'report', 'overall'])) {
            return $this->replySummary($kpis, $report, $campaigns);
        }

        if ($this->contains($lower, ['impression', 'reach', 'audience'])) {
            return $this->replyImpressions($kpis);
        }

        if ($this->contains($lower, ['hello', 'hi', 'hey', 'help', 'what can you'])) {
            return $this->replyGreeting($report);
        }

        // Default: summary
        return $this->replySummary($kpis, $report, $campaigns);
    }

    private function replyGreeting(Report $report): string
    {
        return "Hi! I'm your Meta Ads analytics assistant 👋\n\nI'm currently analysing **{$report->original_filename}** ({$report->date_range_label}).\n\nYou can ask me about:\n• **Spend & Budget** — how much was spent\n• **CTR & Clicks** — click-through performance\n• **CPC** — cost per click\n• **ROAS & Revenue** — return on ad spend\n• **Conversions & CAC** — conversion performance\n• **Campaigns** — top/worst performers\n• **Recommendations** — what to improve\n• **Summary** — overall performance overview\n\nWhat would you like to know?";
    }

    private function replySummary(array $kpis, Report $report, array $campaigns): string
    {
        $topCampaign = ! empty($campaigns) ? $campaigns[0]['name'] : 'N/A';

        return "**Performance Summary for {$report->original_filename}**\n"
            . "📅 {$report->date_range_label}\n\n"
            . "💰 **Total Spend:** \$" . number_format($kpis['total_spend'], 2) . "\n"
            . "👁 **Impressions:** " . number_format($kpis['total_impressions']) . "\n"
            . "🖱 **Clicks:** " . number_format($kpis['total_clicks']) . "\n"
            . "📊 **CTR:** {$kpis['ctr']}%\n"
            . "💵 **CPC:** \${$kpis['cpc']}\n"
            . "🎯 **Conversions:** " . number_format($kpis['total_conversions']) . "\n"
            . "💲 **CAC:** \${$kpis['cac']}\n"
            . "💬 **Conversations:** " . number_format($kpis['total_conversations']) . "\n"
            . "📈 **Revenue:** \$" . number_format($kpis['total_revenue'], 2) . "\n"
            . "🚀 **ROAS:** {$kpis['roas']}x\n"
            . "🏆 **Top Campaign:** {$topCampaign}\n\n"
            . "Ask me about any specific metric for deeper insights!";
    }

    private function replySpend(array $kpis, array $campaigns): string
    {
        $reply = "💰 **Spend Analysis**\n\n"
            . "Total spend: **\$" . number_format($kpis['total_spend'], 2) . "**\n"
            . "This generated " . number_format($kpis['total_clicks']) . " clicks at \${$kpis['cpc']} CPC.\n\n";

        if (! empty($campaigns)) {
            $reply .= "**Top campaigns by spend:**\n";
            foreach (array_slice($campaigns, 0, 5) as $c) {
                $reply .= "• {$c['name']}: \$" . number_format($c['spend'], 2) . " ({$c['conversions']} conv, {$c['roas']}x ROAS)\n";
            }
        }

        if ($kpis['roas'] > 0) {
            $reply .= "\nYour spend generated \$" . number_format($kpis['total_revenue'], 2) . " in revenue ({$kpis['roas']}x ROAS).";
        }

        return $reply;
    }

    private function replyCtr(array $kpis, array $campaigns): string
    {
        $benchmark = 1.5;
        $status    = $kpis['ctr'] >= $benchmark ? "above" : "below";
        $emoji     = $kpis['ctr'] >= $benchmark ? "✅" : "⚠️";

        $reply = "{$emoji} **CTR Analysis**\n\n"
            . "Overall CTR: **{$kpis['ctr']}%** ({$status} the ~{$benchmark}% Meta benchmark)\n"
            . "Total clicks: " . number_format($kpis['total_clicks']) . " from " . number_format($kpis['total_impressions']) . " impressions.\n\n";

        if ($kpis['ctr'] < $benchmark) {
            $reply .= "💡 **To improve CTR:**\n• Refresh your ad creatives\n• Test different headlines and copy\n• Narrow your audience targeting\n• Try video or carousel formats";
        } else {
            $reply .= "Great click-through performance! Make sure your landing page matches the ad promise to convert that traffic.";
        }

        if (! empty($campaigns)) {
            $topByCtr = collect($campaigns)->sortByDesc('ctr')->first();
            $reply .= "\n\n🏆 Best CTR campaign: **{$topByCtr['name']}** at {$topByCtr['ctr']}%";
        }

        return $reply;
    }

    private function replyCpc(array $kpis, array $campaigns): string
    {
        $benchmark = 1.0;
        $status    = $kpis['cpc'] <= $benchmark ? "efficient" : "above average";
        $emoji     = $kpis['cpc'] <= $benchmark ? "✅" : "⚠️";

        $reply = "{$emoji} **Cost Per Click Analysis**\n\n"
            . "Overall CPC: **\${$kpis['cpc']}** ({$status})\n\n";

        if ($kpis['cpc'] > 3.0) {
            $reply .= "Your CPC is relatively high. Consider:\n• Improving ad relevance scores\n• Testing broader audiences to reduce competition\n• Pausing high-CPC, low-conversion ad sets\n• Testing different bidding strategies";
        } else {
            $reply .= "Your CPC is within a healthy range. Focus on converting that traffic efficiently.";
        }

        if (! empty($campaigns)) {
            $cheapest = collect($campaigns)->where('cpc', '>', 0)->sortBy('cpc')->first();
            $mostExp  = collect($campaigns)->sortByDesc('cpc')->first();
            if ($cheapest && $mostExp) {
                $reply .= "\n\n📊 CPC range across campaigns:\n• Lowest: **{$cheapest['name']}** at \${$cheapest['cpc']}\n• Highest: **{$mostExp['name']}** at \${$mostExp['cpc']}";
            }
        }

        return $reply;
    }

    private function replyRoas(array $kpis, array $campaigns): string
    {
        $emoji  = $kpis['roas'] >= 3.0 ? "🚀" : ($kpis['roas'] >= 1.5 ? "✅" : "⚠️");
        $status = $kpis['roas'] >= 3.0 ? "excellent" : ($kpis['roas'] >= 1.5 ? "positive" : "below breakeven");

        $reply = "{$emoji} **ROAS & Revenue Analysis**\n\n"
            . "Overall ROAS: **{$kpis['roas']}x** ({$status})\n"
            . "Revenue generated: **\$" . number_format($kpis['total_revenue'], 2) . "** on \$" . number_format($kpis['total_spend'], 2) . " spend\n\n";

        if ($kpis['roas'] < 1.0 && $kpis['total_spend'] > 0) {
            $reply .= "⚠️ You're currently spending more than you're generating in tracked revenue. Verify your conversion tracking and attribution setup.";
        } elseif ($kpis['roas'] >= 4.0) {
            $reply .= "Excellent ROAS! This is a strong signal to scale your budget. Increase daily budgets by 20–30% while monitoring performance.";
        }

        if (! empty($campaigns)) {
            $topRoas = collect($campaigns)->where('roas', '>', 0)->sortByDesc('roas')->first();
            if ($topRoas) {
                $reply .= "\n\n🏆 Highest ROAS campaign: **{$topRoas['name']}** at {$topRoas['roas']}x";
            }
        }

        return $reply;
    }

    private function replyConversions(array $kpis, array $campaigns): string
    {
        $reply = "🎯 **Conversion Analysis**\n\n"
            . "Total conversions: **" . number_format($kpis['total_conversions']) . "**\n"
            . "Cost per conversion (CAC): **\${$kpis['cac']}**\n"
            . "Conversion value (revenue): \$" . number_format($kpis['total_revenue'], 2) . "\n\n";

        if ($kpis['total_conversions'] == 0) {
            $reply .= "⚠️ No conversions recorded. Check:\n• Is your Meta Pixel correctly installed?\n• Are conversion events firing properly?\n• Is your landing page optimised for conversion?";
        } elseif ($kpis['cac'] > 50) {
            $reply .= "Your CAC is on the higher side. Focus budget on campaigns with the lowest CAC and pause the rest.";
        } else {
            $reply .= "Solid conversion performance! Keep monitoring CAC as you scale.";
        }

        if (! empty($campaigns)) {
            $topConv = collect($campaigns)->sortByDesc('conversions')->first();
            $reply .= "\n\n🏆 Top converting campaign: **{$topConv['name']}** with {$topConv['conversions']} conversions at \${$topConv['cac']} CAC";
        }

        return $reply;
    }

    private function replyConversations(array $kpis): string
    {
        if ($kpis['total_conversations'] == 0) {
            return "💬 **Conversations**\n\nNo messaging conversations recorded in this report period. If you're running Click-to-Message campaigns, ensure your conversation event is being tracked correctly in Meta Events Manager.";
        }

        return "💬 **Conversation Analysis**\n\n"
            . "Total conversations: **" . number_format($kpis['total_conversations']) . "**\n"
            . "Cost per conversation: **\${$kpis['cost_per_conversation']}**\n\n"
            . ($kpis['cost_per_conversation'] <= 10
                ? "✅ Great cost per conversation! Consider scaling these messaging campaigns."
                : "⚠️ High cost per conversation. Test different audiences or creatives to reduce this cost.");
    }

    private function replyCampaigns(array $campaigns): string
    {
        if (empty($campaigns)) {
            return "No campaign data found in this report. Ensure your export includes the Campaign Name column.";
        }

        $reply = "📊 **Campaign Performance Breakdown**\n\n";

        $reply .= "**Top performers (by ROAS):**\n";
        $byRoas = collect($campaigns)->where('roas', '>', 0)->sortByDesc('roas')->take(5);
        foreach ($byRoas as $c) {
            $reply .= "• **{$c['name']}**: {$c['roas']}x ROAS | \$" . number_format($c['spend'], 2) . " spend | {$c['conversions']} conv\n";
        }

        $reply .= "\n**Top spenders:**\n";
        $bySpend = collect($campaigns)->sortByDesc('spend')->take(5);
        foreach ($bySpend as $c) {
            $reply .= "• **{$c['name']}**: \$" . number_format($c['spend'], 2) . " | {$c['ctr']}% CTR | {$c['roas']}x ROAS\n";
        }

        $worst = collect($campaigns)->where('spend', '>', 10)->where('conversions', '==', 0)->sortByDesc('spend')->first();
        if ($worst) {
            $reply .= "\n⚠️ **Needs attention:** **{$worst['name']}** spent \$" . number_format($worst['spend'], 2) . " with 0 conversions.";
        }

        return $reply;
    }

    private function replyRecommendations(int $reportId, array $kpis): string
    {
        $recs = Recommendation::where('report_id', $reportId)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');

        if ($recs->isEmpty()) {
            return "No recommendations have been generated for this report yet. Go to the **Reports** page and click **Regenerate Recommendations** to generate them.";
        }

        $reply = "💡 **Recommendations for Your Campaigns**\n\n";

        $typeLabels = [
            'working'       => "✅ What's Working",
            'not_working'   => "❌ What's Not Working",
            'at_risk'       => "⚠️ At Risk",
            'needs_scaling' => "🚀 Needs Scaling",
            'recommendations' => "💡 Suggestions",
        ];

        foreach ($typeLabels as $type => $label) {
            if (isset($recs[$type]) && $recs[$type]->count()) {
                $reply .= "**{$label}:**\n";
                foreach ($recs[$type] as $rec) {
                    $reply .= "• {$rec->title}\n";
                }
                $reply .= "\n";
            }
        }

        return rtrim($reply);
    }

    private function replyImpressions(array $kpis): string
    {
        return "👁 **Reach & Impressions**\n\n"
            . "Total impressions: **" . number_format($kpis['total_impressions']) . "**\n"
            . "Total reach: **" . number_format($kpis['total_reach']) . "** unique people\n"
            . "Clicks from impressions: " . number_format($kpis['total_clicks']) . " ({$kpis['ctr']}% CTR)\n\n"
            . (($kpis['total_impressions'] > 0 && $kpis['total_reach'] > 0)
                ? "Average frequency: " . round($kpis['total_impressions'] / $kpis['total_reach'], 1) . "x per person. "
                    . (($kpis['total_impressions'] / $kpis['total_reach'] > 3)
                        ? "⚠️ High frequency may indicate ad fatigue. Consider refreshing creatives or expanding your audience."
                        : "✅ Frequency is in a healthy range.")
                : "");
    }

    /**
     * Check if message contains any of the keywords.
     */
    private function contains(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Save an assistant message to the history.
     */
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

    /**
     * Retrieve conversation history for a session.
     */
    public function getHistory(string $sessionId, int $limit = 50): array
    {
        return ChatHistory::where('session_id', $sessionId)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($m) => [
                'role'    => $m->role,
                'content' => $m->content,
                'time'    => $m->created_at->format('H:i'),
            ])
            ->toArray();
    }

    /**
     * Clear all history for a session.
     */
    public function clearSession(string $sessionId): void
    {
        ChatHistory::where('session_id', $sessionId)->delete();
    }
}
