<?php

namespace App\Services;

use App\Models\Recommendation;
use App\Models\Report;
use Illuminate\Support\Facades\Log;

class RecommendationService
{
    public function __construct(
        private readonly AnalyticsService $analytics
    ) {}

    /**
     * Generate rule-based recommendations for a report.
     * Falls back gracefully if anything fails.
     */
    public function generate(Report $report): bool
    {
        if (! $report->isProcessed()) {
            return false;
        }

        try {
            $kpis      = $this->analytics->getKpis($report->id);
            $campaigns = $this->analytics->getCampaignBreakdown($report->id);

            // Clear any existing recommendations for this report
            Recommendation::where('report_id', $report->id)->delete();

            $this->generateRuleBased($report, $kpis, $campaigns);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Recommendation generation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Rule-based recommendation engine — no AI required.
     */
    private function generateRuleBased(Report $report, array $kpis, array $campaigns): void
    {
        $order = 0;

        $spend         = $kpis['total_spend'];
        $ctr           = $kpis['ctr'];
        $cpc           = $kpis['cpc'];
        $cac           = $kpis['cac'];
        $roas          = $kpis['roas'];
        $conversions   = $kpis['total_conversions'];
        $conversations = $kpis['total_conversations'];
        $impressions   = $kpis['total_impressions'];
        $clicks        = $kpis['total_clicks'];
        $revenue       = $kpis['total_revenue'];
        $cpc_conv      = $kpis['cost_per_conversation'];

        // ── WHAT'S WORKING ────────────────────────────────────────────────────
        if ($ctr >= 2.0) {
            $this->save($report, 'working', 'Strong Click-Through Rate', "Your campaigns are achieving a {$ctr}% CTR, which is above the 2% benchmark for Meta Ads. Your creative and targeting are resonating well with your audience.", $order++);
        }

        if ($roas >= 3.0) {
            $this->save($report, 'working', 'Excellent ROAS Performance', "Your return on ad spend is {$roas}x, meaning every \$1 spent generates \${$roas} in revenue. This is a strong indicator that your campaigns are profitable and scalable.", $order++);
        }

        if ($conversions > 10 && $cac > 0 && $cac < 50) {
            $this->save($report, 'working', 'Efficient Customer Acquisition Cost', "Your CAC of \${$cac} across {$conversions} conversions is within a healthy range. Your funnel is converting traffic into customers effectively.", $order++);
        }

        if ($conversations > 5) {
            $this->save($report, 'working', 'Active Conversation Volume', "Your campaigns generated {$conversations} conversations. Messaging-based engagement is working — consider scaling ad sets that drive conversation volume.", $order++);
        }

        // ── WHAT'S NOT WORKING ────────────────────────────────────────────────
        if ($spend > 100 && $conversions == 0 && $revenue == 0) {
            $this->save($report, 'not_working', 'Spend With Zero Conversions', "You have spent \$" . number_format($spend, 2) . " with no recorded conversions or revenue. Review your conversion tracking setup and landing page quality urgently.", $order++);
        }

        if ($ctr > 0 && $ctr < 0.5) {
            $this->save($report, 'not_working', 'Below-Average CTR', "Your CTR of {$ctr}% is significantly below the 1–2% Meta Ads average. Consider refreshing ad creatives, testing new copy, or refining your audience targeting.", $order++);
        }

        if ($cpc > 5 && $clicks > 0) {
            $this->save($report, 'not_working', 'High Cost Per Click', "At \${$cpc} CPC, you are paying above average for traffic. Identify underperforming campaigns and pause them to reduce wasted spend.", $order++);
        }

        if ($cac > 100 && $conversions > 0) {
            $this->save($report, 'not_working', 'High Customer Acquisition Cost', "Your CAC of \${$cac} per conversion may be unsustainable. Review your highest-spending ad sets and compare their conversion rates against top performers.", $order++);
        }

        // ── AT RISK ───────────────────────────────────────────────────────────
        if ($roas > 0 && $roas < 1.5 && $spend > 50) {
            $this->save($report, 'at_risk', 'ROAS Below Breakeven Threshold', "A ROAS of {$roas}x on \$" . number_format($spend, 2) . " spend suggests you may not be covering your costs. Investigate which campaigns are dragging performance down.", $order++);
        }

        if ($impressions > 10000 && $clicks < 10) {
            $this->save($report, 'at_risk', 'High Impressions, Very Low Clicks', "Your ads have received " . number_format($impressions) . " impressions but only {$clicks} clicks. Creative fatigue or poor audience-message fit may be the cause. Test new creatives immediately.", $order++);
        }

        if ($conversations > 0 && $cpc_conv > 20) {
            $this->save($report, 'at_risk', 'High Cost Per Conversation', "At \${$cpc_conv} per conversation, your messaging campaigns may be too expensive to scale. Test different audiences or creative approaches to reduce this cost.", $order++);
        }

        // ── NEEDS SCALING ─────────────────────────────────────────────────────
        if ($roas >= 4.0 && $spend < 1000) {
            $this->save($report, 'needs_scaling', 'High ROAS — Ready to Scale Budget', "With a {$roas}x ROAS and relatively modest spend of \$" . number_format($spend, 2) . ", this is an excellent candidate for budget scaling. Increase daily budgets by 20–30% and monitor performance.", $order++);
        }

        // Find top performing campaigns
        foreach (array_slice($campaigns, 0, 3) as $campaign) {
            if (($campaign['roas'] ?? 0) >= 4.0 && ($campaign['spend'] ?? 0) > 20) {
                $this->save(
                    $report,
                    'needs_scaling',
                    "Scale: {$campaign['name']}",
                    "This campaign is delivering a {$campaign['roas']}x ROAS on \$" . round($campaign['spend'], 2) . " spend with {$campaign['conversions']} conversions. Increase its budget to capture more of this high-performing audience.",
                    $order++
                );
                break; // only flag the top one
            }
        }

        // ── GENERAL RECOMMENDATIONS ───────────────────────────────────────────
        if ($spend > 0) {
            $this->save($report, 'recommendations', 'Review Campaign Frequency', "If your CPM is rising or CTR is declining, your audience may be experiencing ad fatigue. Expand your audience, refresh creatives, or introduce new ad formats to maintain performance.", $order++);
        }

        if (count($campaigns) > 1) {
            $this->save($report, 'recommendations', 'Consolidate Underperforming Campaigns', "With " . count($campaigns) . " active campaigns, consolidate your budget into the top 2–3 performers. Facebook's algorithm performs better with fewer, higher-budget campaigns.", $order++);
        }

        $this->save($report, 'recommendations', 'Test Advantage+ Audiences', "Meta's Advantage+ audience targeting can find new high-value customers outside your manually defined audiences. Consider running an Advantage+ campaign alongside your existing structure.", $order++);
    }

    /**
     * Helper to create a single recommendation record.
     */
    private function save(Report $report, string $type, string $title, string $content, int $order): void
    {
        Recommendation::create([
            'report_id'  => $report->id,
            'type'       => $type,
            'title'      => substr($title, 0, 255),
            'content'    => $content,
            'sort_order' => $order,
        ]);
    }
}
