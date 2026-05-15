<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\UploadService;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UploadController extends Controller
{
    public function __construct(
        private readonly UploadService         $uploader,
        private readonly RecommendationService $recommender
    ) {}

    /**
     * Show the upload form.
     */
    public function index(): View
    {
        $recentReports = Report::orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('upload.index', compact('recentReports'));
    }

    /**
     * Handle the file upload and process the report.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'report' => [
                'required',
                'file',
                'max:20480', // 20 MB
                'mimes:csv,xlsx,xls,txt',
            ],
        ], [
            'report.required' => 'Please select a file to upload.',
            'report.max'      => 'The file may not be larger than 20 MB.',
            'report.mimes'    => 'Only CSV and Excel (XLSX/XLS) files are supported.',
        ]);

        try {
            $report = $this->uploader->handle($request->file('report'));

            // Generate rule-based recommendations immediately after processing
            $this->recommender->generate($report);

            return redirect()
                ->route('dashboard', ['report_id' => $report->id])
                ->with('success', "Report \"{$report->original_filename}\" uploaded and analysed successfully.");

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'An unexpected error occurred while processing your report. Please try again.');
        }
    }
}
