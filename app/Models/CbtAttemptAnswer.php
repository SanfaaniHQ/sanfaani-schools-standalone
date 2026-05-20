<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtAttemptAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'cbt_attempt_id',
        'cbt_exam_question_id',
        'cbt_question_id',
        'question_type',
        'answer_payload',
        'answer_text',
        'selected_option_ids',
        'is_correct',
        'auto_score',
        'manual_score',
        'max_score',
        'marker_comment',
        'marked_by',
        'marked_at',
        'status',
        'autosaved_at',
        'metadata',
    ];

    protected $casts = [
        'answer_payload' => 'array',
        'selected_option_ids' => 'array',
        'is_correct' => 'boolean',
        'auto_score' => 'decimal:2',
        'manual_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'marked_at' => 'datetime',
        'autosaved_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(CbtAttempt::class, 'cbt_attempt_id');
    }

    public function examQuestion(): BelongsTo
    {
        return $this->belongsTo(CbtExamQuestion::class, 'cbt_exam_question_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CbtQuestion::class, 'cbt_question_id');
    }

    public function markingRecords(): HasMany
    {
        return $this->hasMany(CbtMarkingRecord::class);
    }
}
