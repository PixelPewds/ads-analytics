<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\RecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(protected RecommendationService $recommendationService) {}

    public function index(): View
    {
        $reports = Report::latest()->paginate(15);
        return view('reports.index', compact('reports'))->with('title', 'Reports');
    }

    public function destroy(Report $report): RedirectResponse
    {
        $report->delete();
        return back()->with('success', 'Report deleted successfully.');
    }

    public function regenerate(Report $report): RedirectResponse
    {
        if (! $report->isProcessed()) {
            return back()->with('error', 'Cannot generate recommendations for an unprocessed report.');
        }

        $previousReport = Report::where('status', 'processed')
            ->where('id', '!=', $report->id)
            ->where('created_at', '<', $report->created_at)
            ->latest()
            ->first();

        $this->recommendationService->generate($report, $previousReport);

        return back()->with('success', 'AI recommendations regenerated.');
    }
}