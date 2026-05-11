toDateString()}_{$to->toDateString()}";

        return Cache::store('file')->remember($cacheKey, self::CACHE_TTL, function () use ($from, $to) {
            $row = DB::table('performance_records')
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('
                    COALESCE(SUM(impressions), 0)                           AS total_impressions,
                    COALESCE(SUM(clicks), 0)                                AS total_clicks,
                    COALESCE(SUM(link_clicks), 0)                           AS total_link_clicks,
                    COALESCE(SUM(spend), 0)                                 AS total_spend,
                    COALESCE(SUM(conversions), 0)                           AS total_conversions,
                    COALESCE(SUM(revenue), 0)                               AS total_revenue,
                    COALESCE(SUM(reach), 0)                                 AS total_reach,
                    COALESCE(
                        SUM(clicks) / NULLIF(SUM(impressions), 0) * 100, 0
                    )                                                       AS avg_ctr,
                    COALESCE(
                        SUM(spend) / NULLIF(SUM(clicks), 0), 0
                    )                                                       AS avg_cpc,
                    COALESCE(
                        SUM(spend) / NULLIF(SUM(impressions), 0) * 1000, 0
                    )                                                       AS avg_cpm,
                    COALESCE(
                        SUM(revenue) / NULLIF(SUM(spend), 0), 0
                    )                                                       AS avg_roas
                ')
                ->first();

            return (array) $row;
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Spend / Impression Trend (daily)
    // ──────────────────────────────────────────────────────────────────────────

    public function getSpendTrend(Carbon $from, Carbon $to): Collection
    {
        $cacheKey = "spend_trend_{$from->toDateString()}_{$to->toDateString()}";

        return Cache::store('file')->remember($cacheKey, self::CACHE_TTL, function () use ($from, $to) {
            return DB::table('performance_records')
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('
                    date,
                    SUM(spend)       AS daily_spend,
                    SUM(impressions) AS daily_impressions,
                    SUM(clicks)      AS daily_clicks,
                    SUM(conversions) AS daily_conversions
                ')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Campaign Comparison
    // ──────────────────────────────────────────────────────────────────────────

    public function getCampaignPerformance(Carbon $from, Carbon $to): Collection
    {
        $cacheKey = "campaigns_{$from->toDateString()}_{$to->toDateString()}";

        return Cache::store('file')->remember($cacheKey, self::CACHE_TTL, function () use ($from, $to) {
            return DB::table('performance_records AS pr')
                ->join('campaigns AS c', 'pr.campaign_id', '=', 'c.id')
                ->whereBetween('pr.date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('
                    c.id,
                    c.name,
                    c.status,
                    SUM(pr.impressions)                                       AS impressions,
                    SUM(pr.clicks)                                            AS clicks,
                    SUM(pr.spend)                                             AS spend,
                    SUM(pr.conversions)                                       AS conversions,
                    SUM(pr.revenue)                                           AS revenue,
                    SUM(pr.clicks) / NULLIF(SUM(pr.impressions), 0) * 100    AS ctr,
                    SUM(pr.spend)  / NULLIF(SUM(pr.clicks), 0)               AS cpc,
                    SUM(pr.revenue)/ NULLIF(SUM(pr.spend), 0)                AS roas
                ')
                ->groupBy('c.id', 'c.name', 'c.status')
                ->orderByDesc('spend')
                ->get();
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Top Performing Ads (by ROAS then CTR)
    // ──────────────────────────────────────────────────────────────────────────

    public function getTopAds(Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        $cacheKey = "top_ads_{$from->toDateString()}_{$to->toDateString()}_{$limit}";

        return Cache::store('file')->remember($cacheKey, self::CACHE_TTL, function () use ($from, $to, $limit) {
            return DB::table('performance_records AS pr')
                ->join('ads AS a',       'pr.ad_id',     '=', 'a.id')
                ->join('ad_sets AS s',   'pr.ad_set_id', '=', 's.id')
                ->join('campaigns AS c', 'pr.campaign_id','=', 'c.id')
                ->whereBetween('pr.date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('
                    a.id, a.name AS ad_name, a.status,
                    c.name AS campaign_name,
                    s.name AS ad_set_name,
                    SUM(pr.impressions)                                       AS impressions,
                    SUM(pr.clicks)                                            AS clicks,
                    SUM(pr.spend)                                             AS spend,
                    SUM(pr.conversions)                                       AS conversions,
                    SUM(pr.revenue)                                           AS revenue,
                    SUM(pr.clicks) / NULLIF(SUM(pr.impressions), 0) * 100    AS ctr,
                    SUM(pr.spend)  / NULLIF(SUM(pr.clicks), 0)               AS cpc,
                    SUM(pr.revenue)/ NULLIF(SUM(pr.spend), 0)                AS roas
                ')
                ->groupBy('a.id', 'a.name', 'a.status', 'c.name', 's.name')
                ->having('spend', '>', 0)
                ->orderByDesc('roas')
                ->limit($limit)
                ->get();
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Underperforming Ads (high spend, low ROAS & CTR)
    // ──────────────────────────────────────────────────────────────────────────

    public function getUnderperformingAds(Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        $cacheKey = "under_ads_{$from->toDateString()}_{$to->toDateString()}_{$limit}";

        return Cache::store('file')->remember($cacheKey, self::CACHE_TTL, function () use ($from, $to, $limit) {
            // Average ROAS for benchmark
            $avgRoas = (float) DB::table('performance_records')
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->whereRaw('spend > 0')
                ->selectRaw('SUM(revenue) / NULLIF(SUM(spend), 0) AS avg_roas')
                ->value('avg_roas');

            $threshold = max(1.0, $avgRoas * 0.5); // bottom 50% of average

            return DB::table('performance_records AS pr')
                ->join('ads AS a',       'pr.ad_id',      '=', 'a.id')
                ->join('ad_sets AS s',   'pr.ad_set_id',  '=', 's.id')
                ->join('campaigns AS c', 'pr.campaign_id','=', 'c.id')
                ->whereBetween('pr.date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('
                    a.id, a.name AS ad_name, a.status,
                    c.name AS campaign_name,
                    SUM(pr.spend)                                             AS spend,
                    SUM(pr.clicks)                                            AS clicks,
                    SUM(pr.conversions)                                       AS conversions,
                    SUM(pr.clicks) / NULLIF(SUM(pr.impressions), 0) * 100    AS ctr,
                    SUM(pr.revenue)/ NULLIF(SUM(pr.spend), 0)                AS roas
                ')
                ->groupBy('a.id', 'a.name', 'a.status', 'c.name')
                ->having('spend', '>=', 10)
                ->having('roas', '<=', $threshold)
                ->orderByDesc('spend')
                ->limit($limit)
                ->get();
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Region Performance
    // ──────────────────────────────────────────────────────────────────────────

    public function getRegionPerformance(Carbon $from, Carbon $to): Collection
    {
        $cacheKey = "regions_{$from->toDateString()}_{$to->toDateString()}";

        return Cache::store('file')->remember($cacheKey, self::CACHE_TTL, function () use ($from, $to) {
            return DB::table('performance_records')
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->whereNotNull('region')
                ->selectRaw('
                    region,
                    SUM(impressions)                                       AS impressions,
                    SUM(clicks)                                            AS clicks,
                    SUM(spend)                                             AS spend,
                    SUM(conversions)                                       AS conversions,
                    SUM(clicks) / NULLIF(SUM(impressions), 0) * 100       AS ctr,
                    SUM(revenue)/ NULLIF(SUM(spend), 0)                   AS roas
                ')
                ->groupBy('region')
                ->orderByDesc('spend')
                ->limit(20)
                ->get();
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Device / Platform Breakdown
    // ──────────────────────────────────────────────────────────────────────────

    public function getDeviceBreakdown(Carbon $from, Carbon $to): Collection
    {
        $cacheKey = "devices_{$from->toDateString()}_{$to->toDateString()}";

        return Cache::store('file')->remember($cacheKey, self::CACHE_TTL, function () use ($from, $to) {
            return DB::table('performance_records')
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->whereNotNull('device_platform')
                ->selectRaw('
                    device_platform,
                    SUM(impressions) AS impressions,
                    SUM(clicks)      AS clicks,
                    SUM(spend)       AS spend,
                    SUM(conversions) AS conversions
                ')
                ->groupBy('device_platform')
                ->orderByDesc('spend')
                ->get();
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Ad Set Performance
    // ──────────────────────────────────────────────────────────────────────────

    public function getAdSetPerformance(Carbon $from, Carbon $to): Collection
    {
        $cacheKey = "adsets_{$from->toDateString()}_{$to->toDateString()}";

        return Cache::store('file')->remember($cacheKey, self::CACHE_TTL, function () use ($from, $to) {
            return DB::table('performance_records AS pr')
                ->join('ad_sets AS s',   'pr.ad_set_id',  '=', 's.id')
                ->join('campaigns AS c', 'pr.campaign_id','=', 'c.id')
                ->whereBetween('pr.date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('
                    s.id, s.name, s.status,
                    c.name AS campaign_name,
                    SUM(pr.impressions)                                       AS impressions,
                    SUM(pr.clicks)                                            AS clicks,
                    SUM(pr.spend)                                             AS spend,
                    SUM(pr.conversions)                                       AS conversions,
                    SUM(pr.revenue)                                           AS revenue,
                    SUM(pr.clicks) / NULLIF(SUM(pr.impressions), 0) * 100    AS ctr,
                    SUM(pr.spend)  / NULLIF(SUM(pr.clicks), 0)               AS cpc,
                    SUM(pr.revenue)/ NULLIF(SUM(pr.spend), 0)                AS roas
                ')
                ->groupBy('s.id', 's.name', 's.status', 'c.name')
                ->orderByDesc('spend')
                ->get();
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helper: parse date range from request
    // ──────────────────────────────────────────────────────────────────────────

    public function parseDateRange(?string $from, ?string $to): array
    {
        $start = $from ? Carbon::parse($from)->startOfDay()
            : Carbon::now()->subDays(29)->startOfDay();
        $end   = $to   ? Carbon::parse($to)->endOfDay()
            : Carbon::now()->endOfDay();

        // Clamp: start ≤ end
        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end];
    }
}