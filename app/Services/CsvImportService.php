'campaign_name',
        'campaign id'                          => 'campaign_id',
        'ad set name'                          => 'ad_set_name',
        'ad set id'                            => 'ad_set_id',
        'ad name'                              => 'ad_name',
        'ad id'                                => 'ad_id',
        'day'                                  => 'date',
        'date'                                 => 'date',
        'reporting starts'                     => 'date',
        'impressions'                          => 'impressions',
        'reach'                                => 'reach',
        'frequency'                            => 'frequency',
        'clicks (all)'                         => 'clicks',
        'clicks'                               => 'clicks',
        'link clicks'                          => 'link_clicks',
        'unique link clicks'                   => 'link_clicks',
        'ctr (all)'                            => 'ctr',
        'ctr (link click-through rate)'        => 'ctr',
        'cpc (all)'                            => 'cpc',
        'cpc (cost per link click)'            => 'cpc',
        'cpm (cost per 1,000 impressions)'     => 'cpm',
        'cost per 1,000 people reached'        => 'cpm',
        'amount spent (usd)'                   => 'spend',
        'amount spent (gbp)'                   => 'spend',
        'amount spent'                         => 'spend',
        'spend'                                => 'spend',
        'results'                              => 'conversions',
        'conversions'                          => 'conversions',
        'website conversions'                  => 'conversions',
        'cost per result'                      => 'cost_per_conversion',
        'cost per conversion'                  => 'cost_per_conversion',
        'website purchase roas'                => 'roas',
        'purchase roas (return on ad spend)'   => 'roas',
        'roas'                                 => 'roas',
        'revenue'                              => 'revenue',
        'conversion value'                     => 'revenue',
        'website purchases conversion value'   => 'revenue',
        'platform'                             => 'device_platform',
        'impression device'                    => 'device_platform',
        'publisher platform'                   => 'device_platform',
        'region'                               => 'region',
        'country'                              => 'country',
        'objective'                            => 'objective',
    ];

    private const REQUIRED_FIELDS = [
        'campaign_name', 'campaign_id',
        'ad_set_name',   'ad_set_id',
        'ad_name',       'ad_id',
        'date',          'impressions', 'spend',
    ];

    /**
     * Validate CSV structure before committing to import.
     * Returns array ['valid' => bool, 'missing' => array, 'headers' => array].
     */
    public function validateStructure(UploadedFile $file): array
    {
        $headers = $this->extractHeaders($file);

        $mapped = array_map(
            fn($h) => self::COLUMN_MAP[strtolower(trim($h))] ?? null,
            $headers
        );
        $mapped = array_filter($mapped);

        $missing = array_diff(self::REQUIRED_FIELDS, $mapped);

        return [
            'valid'   => empty($missing),
            'missing' => array_values($missing),
            'headers' => $headers,
        ];
    }

    /**
     * Run the full import. Updates the ImportLog throughout.
     */
    public function import(UploadedFile $file, ImportLog $log, AdAccount $account): void
    {
        $log->update(['status' => 'processing']);

        try {
            $rows = $this->parseFile($file);

            if (empty($rows)) {
                $log->update(['status' => 'failed', 'error_message' => 'No data rows found.']);
                return;
            }

            $headers       = array_shift($rows);
            $mappedHeaders = $this->mapHeaders($headers);

            $log->update(['total_rows' => count($rows)]);

            $imported   = 0;
            $failed     = 0;
            $duplicates = 0;

            // Process in chunks to avoid memory exhaustion
            foreach (array_chunk($rows, 200) as $chunk) {
                foreach ($chunk as $index => $rawRow) {
                    $rowNumber = $index + 2; // 1-based, accounting for header row

                    try {
                        $result = $this->processRow(
                            $rawRow, $mappedHeaders, $rowNumber, $log, $account
                        );

                        match ($result) {
                            'imported'  => $imported++,
                            'duplicate' => $duplicates++,
                            default     => null,
                        };
                    } catch (\Throwable $e) {
                        $failed++;
                        $this->logFailedRow($log->id, $rowNumber, $rawRow, $headers, $e->getMessage());
                        Log::warning("Import row {$rowNumber} failed: " . $e->getMessage());
                    }
                }
            }

            $log->update([
                'status'         => 'completed',
                'imported_rows'  => $imported,
                'failed_rows'    => $failed,
                'duplicate_rows' => $duplicates,
            ]);

        } catch (\Throwable $e) {
            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error('Import failed: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function processRow(
        array $rawRow,
        array $mappedHeaders,
        int $rowNumber,
        ImportLog $log,
        AdAccount $account
    ): string {
        $data = $this->buildDataMap($rawRow, $mappedHeaders);

        // Skip empty rows
        if (empty(array_filter($data))) {
            return 'skipped';
        }

        $this->validateRow($data, $rowNumber);

        // ── Upsert Campaign ───────────────────────────────────────────────────
        $campaign = Campaign::firstOrCreate(
            ['campaign_id' => $data['campaign_id']],
            [
                'ad_account_id' => $account->id,
                'name'          => $data['campaign_name'],
                'status'        => 'ACTIVE',
                'objective'     => $data['objective'] ?? null,
            ]
        );

        // ── Upsert AdSet ──────────────────────────────────────────────────────
        $adSet = AdSet::firstOrCreate(
            ['ad_set_id' => $data['ad_set_id']],
            [
                'campaign_id' => $campaign->id,
                'name'        => $data['ad_set_name'],
                'status'      => 'ACTIVE',
            ]
        );

        // ── Upsert Ad ─────────────────────────────────────────────────────────
        $ad = Ad::firstOrCreate(
            ['ad_id' => $data['ad_id']],
            [
                'ad_set_id' => $adSet->id,
                'name'      => $data['ad_name'],
                'status'    => 'ACTIVE',
            ]
        );

        // ── Build performance record ──────────────────────────────────────────
        $platform = $data['device_platform'] ?? null;
        $region   = $data['region']          ?? null;

        // Detect duplicate
        $exists = PerformanceRecord::where('ad_id',          $ad->id)
            ->where('date',            $data['date'])
            ->where('device_platform', $platform)
            ->where('region',          $region)
            ->exists();

        if ($exists) {
            return 'duplicate';
        }

        $spend       = (float) ($data['spend']   ?? 0);
        $clicks      = (int)   ($data['clicks']  ?? 0);
        $impressions = (int)   ($data['impressions'] ?? 0);
        $conversions = (int)   ($data['conversions'] ?? 0);
        $revenue     = (float) ($data['revenue'] ?? 0);

        // Derived metrics when not supplied directly
        $ctr  = isset($data['ctr'])  ? (float) $data['ctr']
            : ($impressions > 0 ? ($clicks / $impressions) * 100 : 0);
        $cpc  = isset($data['cpc'])  ? (float) $data['cpc']
            : ($clicks > 0 ? $spend / $clicks : 0);
        $cpm  = isset($data['cpm'])  ? (float) $data['cpm']
            : ($impressions > 0 ? ($spend / $impressions) * 1000 : 0);
        $roas = isset($data['roas']) ? (float) $data['roas']
            : ($spend > 0 && $revenue > 0 ? $revenue / $spend : 0);

        PerformanceRecord::create([
            'ad_id'               => $ad->id,
            'ad_set_id'           => $adSet->id,
            'campaign_id'         => $campaign->id,
            'date'                => $data['date'],
            'impressions'         => $impressions,
            'reach'               => (int) ($data['reach']             ?? 0),
            'frequency'           => (float) ($data['frequency']       ?? 0),
            'clicks'              => $clicks,
            'link_clicks'         => (int) ($data['link_clicks']       ?? 0),
            'ctr'                 => $ctr,
            'spend'               => $spend,
            'cpc'                 => $cpc,
            'cpm'                 => $cpm,
            'conversions'         => $conversions,
            'cost_per_conversion' => (float) ($data['cost_per_conversion'] ?? 0),
            'revenue'             => $revenue,
            'roas'                => $roas,
            'device_platform'     => $platform,
            'region'              => $region,
            'country'             => $data['country'] ?? null,
        ]);

        return 'imported';
    }

    private function validateRow(array $data, int $rowNumber): void
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (empty($data[$field])) {
                throw new \RuntimeException(
                    "Row {$rowNumber}: required field '{$field}' is empty."
                );
            }
        }

        if (! strtotime($data['date'])) {
            throw new \RuntimeException(
                "Row {$rowNumber}: invalid date format '{$data['date']}'."
            );
        }
    }

    private function parseFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['xlsx', 'xls'])) {
            return $this->parseExcel($file);
        }

        return $this->parseCsv($file);
    }

    private function parseCsv(UploadedFile $file): array
    {
        $rows   = [];
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            throw new \RuntimeException('Cannot open uploaded file.');
        }

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    private function parseExcel(UploadedFile $file): array
    {
        // Requires: composer require phpoffice/phpspreadsheet
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = [];

        foreach ($sheet->toArray() as $row) {
            // Filter completely empty rows
            if (array_filter($row, fn($cell) => $cell !== null && $cell !== '')) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function extractHeaders(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['xlsx', 'xls'])) {
            $rows = $this->parseExcel($file);
            return $rows[0] ?? [];
        }

        $handle  = fopen($file->getPathname(), 'r');
        $headers = fgetcsv($handle, 0, ',') ?: [];
        fclose($handle);
        return $headers;
    }

    private function mapHeaders(array $headers): array
    {
        $map = [];
        foreach ($headers as $index => $header) {
            $key = strtolower(trim($header));
            $map[$index] = self::COLUMN_MAP[$key] ?? $key;
        }
        return $map;
    }

    private function buildDataMap(array $row, array $mappedHeaders): array
    {
        $data = [];
        foreach ($mappedHeaders as $index => $field) {
            $data[$field] = isset($row[$index]) ? trim((string) $row[$index]) : null;
        }
        return $data;
    }

    private function logFailedRow(
        int $logId,
        int $rowNumber,
        array $rawRow,
        array $headers,
        string $error
    ): void {
        $rowData = [];
        foreach ($headers as $i => $header) {
            $rowData[$header] = $rawRow[$i] ?? null;
        }

        FailedImportRow::create([
            'import_log_id' => $logId,
            'row_number'    => $rowNumber,
            'row_data'      => $rowData,
            'error_message' => $error,
        ]);
    }
}