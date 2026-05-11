belongsTo(AdAccount::class);
    }

    public function adSets(): HasMany
    {
        return $this->hasMany(AdSet::class);
    }

    public function ads(): HasManyThrough
    {
        return $this->hasManyThrough(Ad::class, AdSet::class);
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