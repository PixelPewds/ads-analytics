<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Recommendation;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analytics
    ) {}

    /**
     * Display the main dashboard.
     */
    public function index(Request $request): View
    {
        // Resolve which report to show (latest processed, or user-selected)
        $reportId = $request->query('report_id');

        $report = $reportId
            ? Report::where('status', 'processed')->find($reportId)
            : Report::where('status', 'processed')->latest()->first();

        // All processed reports for the selector dropdown
        $allReports = Report::where('status', 'processed')
            ->orderByDesc('created_at')
            ->get(['id', 'original_filename', 'date_range_start', 'date_range_end', 'created_at']);

        // Previous report for comparison context
        $previousReport = null;
        $comparison     = null;

        if ($report) {
            $previousReport = Report::where('status', 'processed')
                ->where('id', '<', $report->id)
                ->latest()
                ->first();

            if ($previousReport) {
                $comparison = $this->analytics->compareReports($report, $previousReport);
            }
        }

        // KPIs & chart data
        $kpis         = $report ? $this->analytics->getKpis($report->id) : null;
        $timeline     = $report ? $this->analytics->getTimeline($report->id) : null;
        $campaigns    = $report ? $this->analytics->getCampaignBreakdown($report->id) : [];
        $adsets       = $report ? $this->analytics->getAdsetBreakdown($report->id) : [];
        $ads          = $report ? $this->analytics->getAdBreakdown($report->id) : [];
        $filters      = $report ? $this->analytics->getFilters($report->id) : [];

        // Recommendations
        $recommendations = $report
            ? $report->recommendations()->orderBy('sort_order')->get()->groupBy('type')
            : collect();

        return view('dashboard.index', compact(
            'report',
            'allReports',
            'previousReport',
            'comparison',
            'kpis',
            'timeline',
            'campaigns',
            'adsets',
            'ads',
            'filters',
            'recommendations'
        ));
    }
}
