<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadReportRequest;
use App\Models\Report;
use App\Services\RecommendationService;
use App\Services\UploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UploadController extends Controller
{
    public function __construct(
        protected UploadService         $uploadService,
        protected RecommendationService $recommendationService
    ) {}

    /**
     * Show the upload form.
     */
    public function index(): View
    {
        $recentReports = Report::latest()->limit(10)->get();

        return view('upload.index', compact('recentReports'))
            ->with('title', 'Upload Report');
    }

    /**
     * Handle the file upload and process the report.
     *
     * Full execution flow:
     * 1. Request validated by UploadReportRequest (file present, correct mime, ≤20MB)
     * 2. UploadService::handle() stores file, parses CSV/XLSX rows, inserts metrics
     * 3. RecommendationService::generate() builds rule-based recommendations
     * 4. Redirect to dashboard with report_id so the new report is pre-selected
     */
    public function store(UploadReportRequest $request): RedirectResponse
    {
        try {
            // ── Step 1: Parse + persist file ────────────────────────────────
            $report = $this->uploadService->handle($request->file('report'));

            // ── Step 2: Generate recommendations immediately ─────────────────
            $this->recommendationService->generate($report);

            return redirect()
                ->route('dashboard', ['report_id' => $report->id])
                ->with('success', "Report \"{$report->original_filename}\" uploaded and analysed successfully with {$report->row_count} rows.");

        } catch (\RuntimeException $e) {
            return back()
                ->with('error', $e->getMessage())
                ->withInput();

        } catch (\Throwable $e) {
            // Log for developer visibility; show safe message to user
            report($e);
            return back()
                ->with('error', 'Failed to process the report. Please check the file format and try again. Error: ' . $e->getMessage())
                ->withInput();
        }
    }
}
