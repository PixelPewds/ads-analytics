<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'type',
        'title',
        'content',
        'sort_order',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'working'         => "What's Working",
            'not_working'     => "What's Not Working",
            'at_risk'         => 'At Risk',
            'needs_scaling'   => 'Needs Scaling',
            'recommendations' => 'Recommendations',
            default           => ucfirst($type),
        };
    }
}