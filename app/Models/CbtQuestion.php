<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CbtQuestion extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const AUTO_GRADED_TYPES = [
        'multiple_choice',
        'checkbox',
        'true_false',
        'fill_blank',
        'short_answer',
        'matching',
    ];

    public const MANUAL_TYPES = [
        'long_answer',
        'essay',
        'theory',
        'practical_instruction',
        'image_based',
        'diagram_based',
        'table_based',
        'comprehension',
    ];

    protected $fillable = [
        'school_id',
        'cbt_question_bank_id',
        'subject_id',
        'school_class_id',
        'question_type',
        'prompt',
        'prompt_html',
        'explanation',
        'default_locale',
        'direction',
        'difficulty',
        'topic',
        'tags',
        'content',
        'media',
        'scoring',
        'default_marks',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'content' => 'array',
        'media' => 'array',
        'scoring' => 'array',
        'default_marks' => 'decimal:2',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(CbtQuestionBank::class, 'cbt_question_bank_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(CbtQuestionOption::class)->orderBy('sort_order')->orderBy('id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function requiresManualMarking(): bool
    {
        return ! in_array($this->question_type, self::AUTO_GRADED_TYPES, true);
    }
}
