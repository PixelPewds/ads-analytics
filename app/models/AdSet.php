'decimal:2',
        'lifetime_budget' => 'decimal:2',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    public function performanceRecords(): HasMany
    {
        return $this->hasMany(PerformanceRecord::class);
    }
}
app/Models/Ad.php
belongsTo(AdSet::class);
    }

    public function performanceRecords(): HasMany
    {
        return $this->hasMany(PerformanceRecord::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match (strtoupper($this->status)) {
            'ACTIVE'   => 'badge-green',
            'PAUSED'   => 'badge-yellow',
            'ARCHIVED' => 'badge-gray',
            default    => 'badge-gray',
        };
    }
}