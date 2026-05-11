limit(5)->get();
        return view('import.index', compact('recentLogs'));
    }

    public function store(ImportRequest $request)
    {
        $file = $request->file('file');

        // 1. Validate CSV structure before any DB writes
        $validation = $this->importer->validateStructure($file);

        if (! $validation['valid']) {
            return back()
                ->withInput()
                ->withErrors([
                    'file' => 'Missing required columns: ' . implode(', ', $validation['missing']),
                ]);
        }

        // 2. Persist file to disk (storage/app/imports)
        $stored = $file->store('imports', 'local');

        // 3. Find or create ad account
        $account = AdAccount::firstOrCreate(
            ['account_id' => $request->input('account_id')],
            [
                'name'     => $request->input('account_name'),
                'platform' => 'facebook',
                'currency' => 'USD',
            ]
        );

        // 4. Create import log record
        $log = ImportLog::create([
            'filename'          => $stored,
            'original_filename' => $file->getClientOriginalName(),
            'status'            => 'pending',
        ]);

        // 5. Run import synchronously (suitable for shared hosting)
        //    For large files consider queue::later if host supports it.
        $this->importer->import(
            new \Illuminate\Http\UploadedFile(
                Storage::path($stored),
                $file->getClientOriginalName(),
                $file->getMimeType(),
                null,
                true
            ),
            $log,
            $account
        );

        return redirect()
            ->route('import.logs')
            ->with('success', "Import completed. {$log->fresh()->imported_rows} rows imported.");
    }

    public function logs(Request $request)
    {
        $logs = ImportLog::latest()->paginate(20);
        return view('import.logs', compact('logs'));
    }

    public function failedRows(ImportLog $log)
    {
        $rows = $log->failedRows()->paginate(50);
        return view('import.failed-rows', compact('log', 'rows'));
    }
}