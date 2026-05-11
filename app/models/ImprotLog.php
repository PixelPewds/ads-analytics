hasMany(FailedImportRow::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'completed'  => 'badge-green',
            'processing' => 'badge-blue',
            'failed'     => 'badge-coral',
            default      => 'badge-gray',
        };
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows === 0) return 0;
        return round(($this->imported_rows / $this->total_rows) * 100, 1);
    }
}