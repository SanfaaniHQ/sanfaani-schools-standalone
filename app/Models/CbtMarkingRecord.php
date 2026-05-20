<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtMarkingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'cbt_exam_id',
        'cbt_attempt_id',
        'cbt_attempt_answer_id',
        'marked_by',
        'score',
        'max_score',
        'rubric',
        'comments',
        'moderation_status',
        'moderated_by',
        'moderated_at',
        'is_final',
        'metadata',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'rubric' => 'array',
        'moderated_at' => 'datetime',
        'is_final' => 'boolean',
        'metadata' => 'array',
    ];

    public function answer(): BelongsTo
    {
        return $this->belongsTo(CbtAttemptAnswer::class, 'cbt_attempt_answer_id');
    }
}
