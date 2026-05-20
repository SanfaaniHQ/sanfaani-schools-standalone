<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'cbt_exam_id',
        'cbt_question_id',
        'section_title',
        'marks',
        'sort_order',
        'is_required',
        'metadata',
    ];

    protected $casts = [
        'marks' => 'decimal:2',
        'is_required' => 'boolean',
        'metadata' => 'array',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CbtQuestion::class, 'cbt_question_id');
    }
}
