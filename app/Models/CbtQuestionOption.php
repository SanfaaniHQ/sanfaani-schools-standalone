<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtQuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'cbt_question_id',
        'option_key',
        'body',
        'body_html',
        'locale',
        'direction',
        'is_correct',
        'score_weight',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'score_weight' => 'decimal:4',
        'metadata' => 'array',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(CbtQuestion::class, 'cbt_question_id');
    }
}
