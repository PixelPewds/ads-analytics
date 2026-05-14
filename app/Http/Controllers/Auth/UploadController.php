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
        protected UploadService $uploadService,
        protected RecommendationService $recommendationService
    ) {}

    public function index(): View
    {
        $recentReports = Report::latest()->limit(10)->get();
        return view('upload.index', compact('recentReports'))->with('title', 'Upload Report');
    }

    public function store(UploadReportRequest $request): RedirectResponse
    {
        try {
            $report = $this->uploadService->handle($request->file('report'));

            $previousReport = Report::where('status', 'processed')
                ->where('id', '!=', $report->id)
                ->latest()
                ->first();

            $this->recommendationService->generate($report, $previousReport);

            return redirect()
                ->route('dashboard', ['report_id' => $report->id])
                ->with('success', "Report \"{$report->original_filename}\" uploaded successfully with {$report->row_count} rows.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to process the report. Please check the file format and try again.')->withInput();
        }
    }
}