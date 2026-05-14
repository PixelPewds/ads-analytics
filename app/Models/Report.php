<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'original_filename',
        'file_hash',
        'date_range_start',
        'date_range_end',
        'status',
        'row_count',
        'error_message',
    ];

    protected $casts = [
        'date_range_start' => 'date',
        'date_range_end'   => 'date',
    ];

    public function metrics(): HasMany
    {
        return $this->hasMany(Metric::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class)->orderBy('sort_order');
    }

    public function chatHistories(): HasMany
    {
        return $this->hasMany(ChatHistory::class);
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function getDateRangeLabelAttribute(): string
    {
        if ($this->date_range_start && $this->date_range_end) {
            return $this->date_range_start->format('M d')
                . ' – '
                . $this->date_range_end->format('M d, Y');
        }
        return 'Date range unavailable';
    }
}