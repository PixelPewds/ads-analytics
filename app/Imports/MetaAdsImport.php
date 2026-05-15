<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class MetaAdsImport implements ToCollection, WithHeadingRow
{
    private array $data = [];

    /**
     * Called by Maatwebsite Excel with the full collection of rows.
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $normalized = [];
            foreach ($row->toArray() as $key => $value) {
                $normalKey              = $this->normalizeKey((string) $key);
                $normalized[$normalKey] = $value;
            }
            $this->data[] = $normalized;
        }
    }

    /**
     * Return all parsed rows as an array of associative arrays.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Normalize a column header key to lowercase_with_underscores.
     */
    private function normalizeKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        return trim($key, '_');
    }
}
