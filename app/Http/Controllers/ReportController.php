<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly RecommendationService $recommender
    ) {}

    /**
     * List all reports with summary KPIs.
     */
    public function index(): View
    {
        $reports = Report::orderByDesc('created_at')->paginate(15);

        return view('reports.index', compact('reports'));
    }

    /**
     * Delete a report and all its associated data.
     */
    public function destroy(Report $report): RedirectResponse
    {
        // Delete stored file from disk
        if ($report->filename && \Storage::disk('local')->exists($report->filename)) {
            \Storage::disk('local')->delete($report->filename);
        }

        $report->delete(); // cascades to metrics, recommendations, chat_histories

        return back()->with('success', "Report \"{$report->original_filename}\" deleted.");
    }

    /**
     * Regenerate recommendations for an existing report.
     */
    public function regenerate(Report $report): RedirectResponse
    {
        if (! $report->isProcessed()) {
            return back()->with('error', 'Only processed reports can have recommendations regenerated.');
        }

        try {
            // Clear existing and regenerate
            $report->recommendations()->delete();
            $this->recommender->generate($report);

            return back()->with('success', "Recommendations regenerated for \"{$report->original_filename}\".");
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Failed to regenerate recommendations.');
        }
    }
}
