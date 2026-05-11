'date',
        'frequency'          => 'decimal:4',
        'ctr'                => 'decimal:4',
        'spend'              => 'decimal:4',
        'cpc'                => 'decimal:4',
        'cpm'                => 'decimal:4',
        'cost_per_conversion'=> 'decimal:4',
        'revenue'            => 'decimal:4',
        'roas'               => 'decimal:4',
    ];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function adSet(): BelongsTo
    {
        return $this->belongsTo(AdSet::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}