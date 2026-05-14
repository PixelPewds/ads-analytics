<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Metric extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'date',
        'campaign_id',
        'campaign_name',
        'adset_id',
        'adset_name',
        'ad_id',
        'ad_name',
        'spend',
        'impressions',
        'reach',
        'clicks',
        'ctr',
        'cpc',
        'conversions',
        'cost_per_conversion',
        'revenue',
        'roas',
        'conversations',
        'cost_per_conversation',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}