<?php

namespace App\Services;

use App\Imports\MetaAdsImport;
use App\Models\Metric;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class UploadService
{
    /**
     * Column mapping: internal field name => list of possible CSV/XLSX header names
     * (after normalization to lowercase_with_underscores)
     */
    private const COLUMN_MAP = [
        'date'                  => ['day', 'date', 'reporting_starts', 'reporting_start', 'report_starts'],
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

    /**
     * Handle the uploaded file: store it, parse it, persist metrics.
     */
    public function handle(UploadedFile $file): Report
    {
        $hash = md5_file($file->getRealPath());

        if (Report::where('file_hash', $hash)->exists()) {
            throw new \RuntimeException('This report has already been uploaded.');
        }

        $path = $file->storeAs(
            'reports',
            $hash . '_' . time() . '.' . $file->getClientOriginalExtension(),
            'local'
        );

        $report = Report::create([
            'filename'          => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_hash'         => $hash,
            'status'            => 'processing',
        ]);

        try {
            $ext  = strtolower($file->getClientOriginalExtension());
            $rows = in_array($ext, ['xlsx', 'xls'])
                ? $this->parseXlsx($file->getRealPath())
                : $this->parseCsv($file->getRealPath());

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

    /**
     * Parse a CSV file into an array of associative rows.
     */
    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
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

    /**
     * Parse an XLSX/XLS file using Maatwebsite Excel.
     */
    private function parseXlsx(string $path): array
    {
        $import = new MetaAdsImport();
        Excel::import($import, $path);
        return $import->getData();
    }

    /**
     * Batch-insert metric rows for a report.
     */
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
            if (! empty($insert)) {
                Metric::insert($insert);
            }
        }
    }

    /**
     * Map a raw CSV/XLSX row to the internal metric field names.
     */
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
            } elseif (in_array($field, ['spend', 'ctr', 'cpc', 'conversions', 'cost_per_conversion', 'revenue', 'roas', 'conversations', 'cost_per_conversation'], true)) {
                $value = $this->cleanNumeric($value);
            } elseif (in_array($field, ['impressions', 'reach', 'clicks'], true)) {
                $value = (int) $this->cleanNumeric($value);
            }

            $mapped[$field] = $value;
        }

        // Skip rows where both spend and impressions are empty / zero
        if (empty($mapped['spend']) && empty($mapped['impressions'])) {
            return [];
        }

        return $mapped;
    }

    /**
     * Find the first matching field from a list of candidate header names.
     */
    private function findField(array $row, array $candidates): mixed
    {
        foreach ($candidates as $key) {
            if (isset($row[$key]) && $row[$key] !== '') {
                return $row[$key];
            }
        }
        return null;
    }

    /**
     * Normalize a CSV/XLSX header to lowercase_with_underscores.
     */
    private function normalizeHeader(string $header): string
    {
        $header = strtolower(trim($header));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        return trim($header, '_');
    }

    /**
     * Clean a value to a float (strip currency symbols, commas, etc.).
     */
    private function cleanNumeric(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        $clean = preg_replace('/[^0-9.\-]/', '', (string) $value);
        return (float) $clean;
    }
}
