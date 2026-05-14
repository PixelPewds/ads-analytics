limit(10)->get();
        return view('upload.index', compact('recentReports'))->with('title', 'Upload Report');
    }

    public function store(UploadReportRequest $request): RedirectResponse
    {
        try {
            $report = $this->uploadService->handle($request->file('report'));

            // Generate AI recommendations (gracefully degrades without API key)
            $previousReport = Report::where('status', 'processed')
                ->where('id', '!=', $report->id)
                ->latest()
                ->first();

            $this->recommendationService->generate($report, $previousReport);

            return redirect()
                ->route('dashboard', ['report_id' => $report->id])
                ->with('success', "Report "{$report->original_filename}" uploaded successfully with {$report->row_count} rows.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to process the report. Please check the file format and try again.')->withInput();
        }
    }
}