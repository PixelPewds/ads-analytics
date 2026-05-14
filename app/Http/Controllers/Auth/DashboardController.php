<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Recommendation;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected AnalyticsService $analytics) {}

    public function index(Request $request): View
    {
        $reports       = Report::latest()->get();
        $activeReport  = null;
        $kpis          = [];
        $timeline      = [];
        $campaignData  = [];
        $adsetData     = [];
        $adData        = [];
        $filters       = [];
        $activeFilters = [];
        $recommendations = collect();

        if ($reports->isNotEmpty()) {
            $reportId     = (int) $request->get('report_id', $reports->first()->id);
            $activeReport = $reports->firstWhere('id', $reportId) ?? $reports->first();

            $activeFilters = [
                'date_start' => $request->get('date_start'),
                'date_end'   => $request->get('date_end'),
                'campaign'   => $request->get('campaign'),
                'adset'      => $request->get('adset'),
                'ad'         => $request->get('ad'),
            ];

            $level = $request->get('level', 'campaign');

            $kpis     = $this->analytics->getKpis($activeReport->id, $activeFilters);
            $timeline = $this->analytics->getTimeline($activeReport->id, $activeFilters);
            $filters  = $this->analytics->getFilters($activeReport->id);

            $campaignData = $this->analytics->getCampaignBreakdown($activeReport->id, $activeFilters);

            if ($level === 'adset' || $level === 'all') {
                $adsetData = $this->analytics->getAdsetBreakdown($activeReport->id, $activeFilters);
            }
            if ($level === 'ad' || $level === 'all') {
                $adData = $this->analytics->getAdBreakdown($activeReport->id, $activeFilters);
            }

            $recommendations = $activeReport->recommendations()->get()->groupBy('type');
        }

        return view('dashboard.index', compact(
            'reports', 'activeReport', 'kpis', 'timeline',
            'campaignData', 'adsetData', 'adData',
            'filters', 'activeFilters', 'recommendations'
        ))->with('title', 'Dashboard');
    }
}