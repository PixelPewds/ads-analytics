analytics->parseDateRange(
            $request->input('from'),
            $request->input('to')
        );

        $campaigns   = $this->analytics->getCampaignPerformance($from, $to);
        $adSets      = $this->analytics->getAdSetPerformance($from, $to);
        $topAds      = $this->analytics->getTopAds($from, $to, 50);
        $regions     = $this->analytics->getRegionPerformance($from, $to);

        return view('reports.index', compact('campaigns', 'adSets', 'topAds', 'regions', 'from', 'to'));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->analytics->parseDateRange(
            $request->input('from'),
            $request->input('to')
        );

        $type = $request->input('type', 'campaigns');

        $data = match ($type) {
            'adsets'   => $this->analytics->getAdSetPerformance($from, $to),
            'ads'      => $this->analytics->getTopAds($from, $to, 1000),
            'regions'  => $this->analytics->getRegionPerformance($from, $to),
            default    => $this->analytics->getCampaignPerformance($from, $to),
        };

        $filename = "ads_report_{$type}_{$from->toDateString()}_{$to->toDateString()}.csv";

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            if ($data->isNotEmpty()) {
                // Header row from first object keys
                fputcsv($handle, array_keys((array) $data->first()));
                foreach ($data as $row) {
                    fputcsv($handle, array_values((array) $row));
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}