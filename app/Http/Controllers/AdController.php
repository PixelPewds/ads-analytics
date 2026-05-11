analytics->parseDateRange(
            $request->input('from'),
            $request->input('to')
        );

        $ads = $this->analytics->getTopAds($from, $to, 100);

        return view('ads.index', compact('ads', 'from', 'to'));
    }

    public function show(Ad $ad, Request $request)
    {
        [$from, $to] = $this->analytics->parseDateRange(
            $request->input('from'),
            $request->input('to')
        );

        $kpis = DB::table('performance_records')
            ->where('ad_id', $ad->id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('
                SUM(impressions) AS impressions,
                SUM(clicks) AS clicks,
                SUM(spend) AS spend,
                SUM(conversions) AS conversions,
                SUM(revenue) AS revenue,
                SUM(clicks)/NULLIF(SUM(impressions),0)*100 AS ctr,
                SUM(spend)/NULLIF(SUM(clicks),0) AS cpc,
                SUM(revenue)/NULLIF(SUM(spend),0) AS roas
            ')->first();

        $trend = DB::table('performance_records')
            ->where('ad_id', $ad->id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('date, SUM(spend) AS spend, SUM(clicks) AS clicks, SUM(impressions) AS impressions')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $trendChartData = [
            'labels'      => $trend->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M j'))->toArray(),
            'spend'       => $trend->pluck('spend')->map(fn($v) => round((float) $v, 2))->toArray(),
            'impressions' => $trend->pluck('impressions')->toArray(),
        ];

        $ad->load(['adSet.campaign']);

        return view('ads.show', compact('ad', 'kpis', 'trendChartData', 'from', 'to'));
    }
}