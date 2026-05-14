<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'report_id',
        'role',
        'content',
        'token_count',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}