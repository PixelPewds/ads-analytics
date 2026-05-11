'positive|warning|info', 'message' => string].
     */
    public function generate(
        array      $kpis,
        Collection $topAds,
        Collection $underperformers,
        Collection $campaigns,
        Collection $devices
    ): array {
        $insights = [];

        // ── CTR insight ───────────────────────────────────────────────────────
        $avgCtr = round((float) ($kpis['avg_ctr'] ?? 0), 2);
        $benchmark = 1.5; // Facebook Ads industry average ~1–2%

        if ($avgCtr >= $benchmark) {
            $insights[] = [
                'type'    => 'positive',
                'message' => "Your average CTR of {$avgCtr}% is above the industry benchmark of {$benchmark}%.",
            ];
        } else {
            $insights[] = [
                'type'    => 'warning',
                'message' => "Your average CTR of {$avgCtr}% is below the {$benchmark}% benchmark — consider refreshing ad creatives.",
            ];
        }

        // ── Top ad insight ────────────────────────────────────────────────────
        if ($topAds->isNotEmpty()) {
            $top = $topAds->first();
            $insights[] = [
                'type'    => 'positive',
                'message' => "Ad "{$top->ad_name}" is your top performer with a ROAS of "
                    . number_format((float) $top->roas, 2) . 'x and '
                    . number_format((float) $top->ctr, 2) . '% CTR.',
            ];
        }

        // ── Underperformer insight ────────────────────────────────────────────
        if ($underperformers->isNotEmpty()) {
            $worst = $underperformers->first();
            $insights[] = [
                'type'    => 'warning',
                'message' => "Ad "{$worst->ad_name}" is spending $"
                    . number_format((float) $worst->spend, 2)
                    . ' with a ROAS of only '
                    . number_format((float) $worst->roas, 2) . 'x — review or pause this ad.',
            ];
        }

        // ── ROAS insight ──────────────────────────────────────────────────────
        $avgRoas = round((float) ($kpis['avg_roas'] ?? 0), 2);
        if ($avgRoas > 0) {
            $type = $avgRoas >= 2.0 ? 'positive' : ($avgRoas >= 1.0 ? 'info' : 'warning');
            $insights[] = [
                'type'    => $type,
                'message' => "Overall ROAS is {$avgRoas}x. "
                    . ($avgRoas < 1.0 ? 'You are currently spending more than you earn — review campaign targeting.' : 'Keep optimising for higher return.'),
            ];
        }

        // ── Top campaign insight ──────────────────────────────────────────────
        if ($campaigns->isNotEmpty()) {
            $topCampaign = $campaigns->sortByDesc('roas')->first();
            $insights[] = [
                'type'    => 'positive',
                'message' => "Campaign "{$topCampaign->name}" leads with a ROAS of "
                    . number_format((float) $topCampaign->roas, 2) . 'x.',
            ];

            $bottomCampaign = $campaigns->sortBy('roas')->first();
            if ($bottomCampaign && (float) $bottomCampaign->roas < 1.0 && $campaigns->count() > 1) {
                $insights[] = [
                    'type'    => 'warning',
                    'message' => "Campaign "{$bottomCampaign->name}" underperformed with a ROAS of "
                        . number_format((float) $bottomCampaign->roas, 2) . 'x.',
                ];
            }
        }

        // ── Device insight ────────────────────────────────────────────────────
        if ($devices->isNotEmpty()) {
            $topDevice      = $devices->sortByDesc('clicks')->first();
            $totalClicks    = $devices->sum('clicks');
            $deviceSharePct = $totalClicks > 0
                ? round(($topDevice->clicks / $totalClicks) * 100)
                : 0;

            $insights[] = [
                'type'    => 'info',
                'message' => "{$topDevice->device_platform} accounts for {$deviceSharePct}% of all clicks — ensure your landing pages are optimised for this platform.",
            ];
        }

        return $insights;
    }
}