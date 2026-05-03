<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCardCommentRule extends Model
{
    protected $fillable = [
        'school_id',
        'comment_type',
        'min_average',
        'max_average',
        'comment',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'min_average' => 'decimal:2',
        'max_average' => 'decimal:2',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
