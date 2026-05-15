<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(protected AnalyticsService $analytics) {}

    public function chartData(Request $request): JsonResponse
    {
        $request->validate([
            'report_id'  => ['required', 'integer', 'exists:reports,id'],
            'date_start' => ['nullable', 'date'],
            'date_end'   => ['nullable', 'date'],
            'campaign'   => ['nullable', 'string'],
            'adset'      => ['nullable', 'string'],
            'ad'         => ['nullable', 'string'],
        ]);

        $filters = $request->only(['date_start', 'date_end', 'campaign', 'adset', 'ad']);

        return response()->json([
            'kpis'      => $this->analytics->getKpis((int) $request->report_id, $filters),
            'timeline'  => $this->analytics->getTimeline((int) $request->report_id, $filters),
            'campaigns' => $this->analytics->getCampaignBreakdown((int) $request->report_id, $filters),
        ]);
    }
}
