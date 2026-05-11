analytics->parseDateRange(
            $request->input('from'),
            $request->input('to')
        );

        $kpis        = $this->analytics->getKpis($from, $to);
        $spendTrend  = $this->analytics->getSpendTrend($from, $to);
        $campaigns   = $this->analytics->getCampaignPerformance($from, $to);
        $topAds      = $this->analytics->getTopAds($from, $to, 10);
        $underperfs  = $this->analytics->getUnderperformingAds($from, $to, 5);
        $regions     = $this->analytics->getRegionPerformance($from, $to);
        $devices     = $this->analytics->getDeviceBreakdown($from, $to);
        $insights    = $this->insights->generate($kpis, $topAds, $underperfs, $campaigns, $devices);

        // Prepare chart JSON payloads
        $spendChartData = [
            'labels'   => $spendTrend->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M j'))->toArray(),
            'spend'    => $spendTrend->pluck('daily_spend')->map(fn($v) => round((float) $v, 2))->toArray(),
            'clicks'   => $spendTrend->pluck('daily_clicks')->toArray(),
        ];

        $campaignChartData = [
            'labels'  => $campaigns->pluck('name')->toArray(),
            'spend'   => $campaigns->pluck('spend')->map(fn($v) => round((float) $v, 2))->toArray(),
            'roas'    => $campaigns->pluck('roas')->map(fn($v) => round((float) $v, 2))->toArray(),
        ];

        $deviceChartData = [
            'labels'  => $devices->pluck('device_platform')->toArray(),
            'clicks'  => $devices->pluck('clicks')->toArray(),
        ];

        return view('dashboard.index', compact(
            'kpis', 'spendTrend', 'campaigns', 'topAds', 'underperfs',
            'regions', 'devices', 'insights',
            'spendChartData', 'campaignChartData', 'deviceChartData',
            'from', 'to'
        ));
    }
}