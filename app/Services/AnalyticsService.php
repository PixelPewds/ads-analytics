buildQuery($reportId, $filters);

        $totals = (clone $query)
            ->selectRaw('
                COALESCE(SUM(spend), 0)         AS total_spend,
                COALESCE(SUM(impressions), 0)   AS total_impressions,
                COALESCE(SUM(reach), 0)         AS total_reach,
                COALESCE(SUM(clicks), 0)        AS total_clicks,
                COALESCE(SUM(conversions), 0)   AS total_conversions,
                COALESCE(SUM(conversations), 0) AS total_conversations,
                COALESCE(SUM(revenue), 0)       AS total_revenue
            ')
            ->first();

        $spend         = (float)$totals->total_spend;
        $impressions   = (int)$totals->total_impressions;
        $clicks        = (int)$totals->total_clicks;
        $conversions   = (float)$totals->total_conversions;
        $conversations = (float)$totals->total_conversations;
        $revenue       = (float)$totals->total_revenue;

        return [
            'total_spend'           => $spend,
            'total_impressions'     => $impressions,
            'total_reach'           => (int)$totals->total_reach,
            'total_clicks'          => $clicks,
            'total_conversions'     => $conversions,
            'total_conversations'   => $conversations,
            'total_revenue'         => $revenue,
            'ctr'                   => $impressions > 0 ? round($clicks / $impressions * 100, 3) : 0,
            'cpc'                   => $clicks > 0 ? round($spend / $clicks, 2) : 0,
            'cac'                   => $conversions > 0 ? round($spend / $conversions, 2) : 0,
            'cost_per_conversation' => $conversations > 0 ? round($spend / $conversations, 2) : 0,
            'roas'                  => $spend > 0 ? round($revenue / $spend, 2) : 0,
        ];
    }

    public function getTimeline(int $reportId, array $filters = []): array
    {
        $rows = $this->buildQuery($reportId, $filters)
            ->selectRaw('
                date,
                COALESCE(SUM(spend), 0)         AS daily_spend,
                COALESCE(SUM(impressions), 0)   AS daily_impressions,
                COALESCE(SUM(clicks), 0)        AS daily_clicks,
                COALESCE(SUM(conversions), 0)   AS daily_conversions,
                COALESCE(SUM(conversations), 0) AS daily_conversations,
                COALESCE(SUM(revenue), 0)       AS daily_revenue
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels        = [];
        $spend         = [];
        $conversions   = [];
        $conversations = [];
        $ctr           = [];
        $cpc           = [];

        foreach ($rows as $row) {
            $labels[]        = Carbon::parse($row->date)->format('M d');
            $spend[]         = round((float)$row->daily_spend, 2);
            $conversions[]   = (float)$row->daily_conversions;
            $conversations[] = (float)$row->daily_conversations;
            $imp             = (int)$row->daily_impressions;
            $clk             = (int)$row->daily_clicks;
            $spd             = (float)$row->daily_spend;
            $ctr[]           = $imp > 0 ? round($clk / $imp * 100, 3) : 0;
            $cpc[]           = $clk > 0 ? round($spd / $clk, 2) : 0;
        }

        return compact('labels', 'spend', 'conversions', 'conversations', 'ctr', 'cpc');
    }

    public function getCampaignBreakdown(int $reportId, array $filters = []): array
    {
        return $this->buildBreakdown($reportId, $filters, 'campaign_name');
    }

    public function getAdsetBreakdown(int $reportId, array $filters = []): array
    {
        return $this->buildBreakdown($reportId, $filters, 'adset_name');
    }

    public function getAdBreakdown(int $reportId, array $filters = []): array
    {
        return $this->buildBreakdown($reportId, $filters, 'ad_name');
    }

    private function buildBreakdown(int $reportId, array $filters, string $groupField): array
    {
        $rows = $this->buildQuery($reportId, $filters)
            ->selectRaw("
                {$groupField}                               AS entity_name,
                COALESCE(SUM(spend), 0)                     AS total_spend,
                COALESCE(SUM(impressions), 0)               AS total_impressions,
                COALESCE(SUM(clicks), 0)                    AS total_clicks,
                COALESCE(SUM(conversions), 0)               AS total_conversions,
                COALESCE(SUM(conversations), 0)             AS total_conversations,
                COALESCE(SUM(revenue), 0)                   AS total_revenue
            ")
            ->whereNotNull($groupField)
            ->groupBy($groupField)
            ->orderByRaw('SUM(spend) DESC')
            ->limit(20)
            ->get();

        return $rows->map(function ($row) {
            $spend         = (float)$row->total_spend;
            $impressions   = (int)$row->total_impressions;
            $clicks        = (int)$row->total_clicks;
            $conversions   = (float)$row->total_conversions;
            $conversations = (float)$row->total_conversations;
            $revenue       = (float)$row->total_revenue;

            return [
                'name'                  => $row->entity_name ?? 'Unknown',
                'spend'                 => $spend,
                'impressions'           => $impressions,
                'clicks'                => $clicks,
                'conversions'           => $conversions,
                'conversations'         => $conversations,
                'revenue'               => $revenue,
                'ctr'                   => $impressions > 0 ? round($clicks / $impressions * 100, 3) : 0,
                'cpc'                   => $clicks > 0 ? round($spend / $clicks, 2) : 0,
                'cac'                   => $conversions > 0 ? round($spend / $conversions, 2) : 0,
                'cost_per_conversation' => $conversations > 0 ? round($spend / $conversations, 2) : 0,
                'roas'                  => $spend > 0 ? round($revenue / $spend, 2) : 0,
            ];
        })->toArray();
    }

    public function compareReports(Report $current, Report $previous): array
    {
        $curr = $this->getKpis($current->id);
        $prev = $this->getKpis($previous->id);

        $changes = [];
        $metrics = ['total_spend', 'ctr', 'cpc', 'cac', 'roas', 'cost_per_conversation', 'total_conversions', 'total_conversations'];

        foreach ($metrics as $metric) {
            $curr_val = $curr[$metric] ?? 0;
            $prev_val = $prev[$metric] ?? 0;

            $changes[$metric] = [
                'current'    => $curr_val,
                'previous'   => $prev_val,
                'change'     => $curr_val - $prev_val,
                'pct_change' => $prev_val != 0
                    ? round((($curr_val - $prev_val) / abs($prev_val)) * 100, 1)
                    : null,
            ];
        }

        return [
            'current_kpis'  => $curr,
            'previous_kpis' => $prev,
            'changes'       => $changes,
        ];
    }

    public function getFilters(int $reportId): array
    {
        return [
            'campaigns' => Metric::where('report_id', $reportId)
                ->whereNotNull('campaign_name')
                ->distinct()
                ->pluck('campaign_name')
                ->sort()
                ->values()
                ->toArray(),

            'adsets' => Metric::where('report_id', $reportId)
                ->whereNotNull('adset_name')
                ->distinct()
                ->pluck('adset_name')
                ->sort()
                ->values()
                ->toArray(),

            'ads' => Metric::where('report_id', $reportId)
                ->whereNotNull('ad_name')
                ->distinct()
                ->pluck('ad_name')
                ->sort()
                ->values()
                ->toArray(),
        ];
    }

    private function buildQuery(int $reportId, array $filters): Builder
    {
        $query = Metric::where('report_id', $reportId);

        if (!empty($filters['date_start'])) {
            $query->whereDate('date', '>=', $filters['date_start']);
        }
        if (!empty($filters['date_end'])) {
            $query->whereDate('date', '<=', $filters['date_end']);
        }
        if (!empty($filters['campaign'])) {
            $query->where('campaign_name', $filters['campaign']);
        }
        if (!empty($filters['adset'])) {
            $query->where('adset_name', $filters['adset']);
        }
        if (!empty($filters['ad'])) {
            $query->where('ad_name', $filters['ad']);
        }

        return $query;
    }
}