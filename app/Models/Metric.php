'date',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}