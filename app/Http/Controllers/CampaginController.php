analytics->parseDateRange(
            $request->input('from'),
            $request->input('to')
        );

        $campaigns = $this->analytics->getCampaignPerformance($from, $to);

        return view('campaigns.index', compact('campaigns', 'from', 'to'));
    }

    public function show(Campaign $campaign, Request $request)
    {
        [$from, $to] = $this->analytics->parseDateRange(
            $request->input('from'),
            $request->input('to')
        );

        // Campaign-level KPIs
        $kpis = \Illuminate\Support\Facades\DB::table('performance_records')
            ->where('campaign_id', $campaign->id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('
                SUM(impressions) AS impressions,
                SUM(clicks)      AS clicks,
                SUM(spend)       AS spend,
                SUM(conversions) AS conversions,
                SUM(revenue)     AS revenue,
                SUM(clicks) / NULLIF(SUM(impressions),0)*100 AS ctr,
                SUM(spend)  / NULLIF(SUM(clicks),0)         AS cpc,
                SUM(revenue)/ NULLIF(SUM(spend),0)          AS roas
            ')
            ->first();

        // Ad sets within campaign
        $adSets = $this->analytics->getAdSetPerformance($from, $to)
            ->filter(fn($row) => Campaign::where('id', $campaign->id)->value('name') === $row->campaign_name);

        // Daily trend
        $trend = \Illuminate\Support\Facades\DB::table('performance_records')
            ->where('campaign_id', $campaign->id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('date, SUM(spend) AS spend, SUM(clicks) AS clicks, SUM(conversions) AS conversions')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $trendChartData = [
            'labels'  => $trend->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M j'))->toArray(),
            'spend'   => $trend->pluck('spend')->map(fn($v) => round((float) $v, 2))->toArray(),
            'clicks'  => $trend->pluck('clicks')->toArray(),
        ];

        $campaign->load(['adSets.ads']);

        return view('campaigns.show', compact('campaign', 'kpis', 'adSets', 'trendChartData', 'from', 'to'));
    }
}