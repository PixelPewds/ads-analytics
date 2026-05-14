['day', 'date', 'reporting_starts', 'reporting_start', 'report_starts'],
        'campaign_id'           => ['campaign_id'],
        'campaign_name'         => ['campaign_name'],
        'adset_id'              => ['ad_set_id', 'adset_id'],
        'adset_name'            => ['ad_set_name', 'adset_name'],
        'ad_id'                 => ['ad_id'],
        'ad_name'               => ['ad_name'],
        'spend'                 => ['amount_spent_usd', 'amount_spent_eur', 'amount_spent', 'spend', 'cost'],
        'impressions'           => ['impressions'],
        'reach'                 => ['reach'],
        'clicks'                => ['link_clicks', 'clicks_all', 'clicks'],
        'ctr'                   => ['ctr_link_click_through_rate', 'ctr_all', 'ctr'],
        'cpc'                   => ['cpc_cost_per_link_click', 'cpc_all', 'cpc'],
        'conversions'           => ['website_purchases', 'purchases', 'results', 'conversions', 'leads'],
        'cost_per_conversion'   => ['cost_per_website_purchase', 'cost_per_purchase', 'cost_per_result', 'cost_per_conversion', 'cost_per_lead'],
        'revenue'               => ['purchase_conversion_value', 'website_purchases_conversion_value', 'conversion_value', 'revenue'],
        'roas'                  => ['purchase_roas_return_on_ad_spend', 'roas_return_on_ad_spend', 'purchase_roas', 'roas'],
        'conversations'         => ['messaging_conversations_started', 'new_messaging_conversations', 'conversations_started'],
        'cost_per_conversation' => ['cost_per_messaging_conversation_started', 'cost_per_new_messaging_conversation'],
    ];

    public function handle(UploadedFile $file): Report
    {
        $hash = md5_file($file->getRealPath());

        if (Report::where('file_hash', $hash)->exists()) {
            throw new \RuntimeException('This report has already been uploaded.');
        }

        $path = $file->storeAs('reports', $hash . '_' . time() . '.' . $file->getClientOriginalExtension(), 'local');

        $report = Report::create([
            'filename'          => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_hash'         => $hash,
            'status'            => 'processing',
        ]);

        try {
            $ext  = strtolower($file->getClientOriginalExtension());
            $rows = $ext === 'csv'
                ? $this->parseCsv($file->getRealPath())
                : $this->parseXlsx($file->getRealPath());

            if (empty($rows)) {
                throw new \RuntimeException('No data rows found in the uploaded file.');
            }

            $this->storeMetrics($report, $rows);

            $dates = Metric::where('report_id', $report->id)
                ->whereNotNull('date')
                ->selectRaw('MIN(date) as min_date, MAX(date) as max_date')
                ->first();

            $report->update([
                'status'           => 'processed',
                'row_count'        => count($rows),
                'date_range_start' => $dates?->min_date,
                'date_range_end'   => $dates?->max_date,
            ]);
        } catch (\Throwable $e) {
            $report->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            throw $e;
        }

        return $report;
    }

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \RuntimeException('Cannot open CSV file.');
        }

        $headers = null;
        $rows    = [];

        while (($line = fgetcsv($handle, 4096, ',')) !== false) {
            if ($headers === null) {
                $headers = array_map([$this, 'normalizeHeader'], $line);
                continue;
            }
            if (count($line) === count($headers)) {
                $rows[] = array_combine($headers, $line);
            }
        }

        fclose($handle);
        return $rows;
    }

    private function parseXlsx(string $path): array
    {
        $import = new MetaAdsImport();
        Excel::import($import, $path);
        return $import->getData();
    }

    private function storeMetrics(Report $report, array $rows): void
    {
        $chunks = array_chunk($rows, 500);

        foreach ($chunks as $chunk) {
            $insert = [];
            foreach ($chunk as $row) {
                $mapped = $this->mapRow($row);
                if (empty($mapped)) {
                    continue;
                }
                $mapped['report_id']  = $report->id;
                $mapped['created_at'] = now();
                $mapped['updated_at'] = now();
                $insert[] = $mapped;
            }
            if (!empty($insert)) {
                Metric::insert($insert);
            }
        }
    }

    private function mapRow(array $row): array
    {
        $mapped = [];

        foreach (self::COLUMN_MAP as $field => $candidates) {
            $value = $this->findField($row, $candidates);

            if ($field === 'date' && $value) {
                try {
                    $value = Carbon::parse($value)->format('Y-m-d');
                } catch (\Throwable) {
                    $value = null;
                }
            } elseif (in_array($field, ['spend','ctr','cpc','conversions','cost_per_conversion','revenue','roas','conversations','cost_per_conversation'], true)) {
                $value = $this->cleanNumeric($value);
            } elseif (in_array($field, ['impressions','reach','clicks'], true)) {
                $value = (int) $this->cleanNumeric($value);
            }

            $mapped[$field] = $value;
        }

        // Skip rows where both spend and impressions are empty
        if (empty($mapped['spend']) && empty($mapped['impressions'])) {
            return [];
        }

        return $mapped;
    }

    private function findField(array $row, array $candidates): mixed
    {
        foreach ($candidates as $key) {
            if (isset($row[$key]) && $row[$key] !== '') {
                return $row[$key];
            }
        }
        return null;
    }

    private function normalizeHeader(string $header): string
    {
        $header = strtolower(trim($header));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        return trim($header, '_');
    }

    private function cleanNumeric(mixed $value): float
    {
        if ($value === null || $value === '') return 0;
        $clean = preg_replace('/[^0-9.\-]/', '', (string)$value);
        return (float)$clean;
    }
}