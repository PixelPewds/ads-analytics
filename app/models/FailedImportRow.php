'array'];

    public function importLog(): BelongsTo
    {
        return $this->belongsTo(ImportLog::class);
    }
}