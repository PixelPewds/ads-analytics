analytics->parseDateRange(
            $request->input('from'),
            $request->input('to')
        );

        $adSets = $this->analytics->getAdSetPerformance($from, $to);

        return view('adsets.index', compact('adSets', 'from', 'to'));
    }

    public function show(AdSet $adSet, Request $request)
    {
        [$from, $to] = $this->analytics->parseDateRange(
            $request->input('from'),
            $request->input('to')
        );

        $kpis = DB::table('performance_records')
            ->where('ad_set_id', $adSet->id)
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

        $ads = $this->analytics->getTopAds($from, $to, 50)
            ->filter(fn($a) => AdSet::where('id', $adSet->id)->value('name') === $a->ad_set_name);

        $adSet->load('campaign');

        return view('adsets.show', compact('adSet', 'kpis', 'ads', 'from', 'to'));
    }
}