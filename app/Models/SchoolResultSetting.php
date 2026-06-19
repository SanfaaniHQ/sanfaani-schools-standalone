<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolResultSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'pass_mark',
        'maximum_score',
        'ca_max_score',
        'exam_max_score',
        'default_result_type',
        'require_all_subjects',
        'show_positions',
        'updated_by',
        'metadata',
    ];

    protected $casts = [
        'pass_mark' => 'decimal:2',
        'maximum_score' => 'decimal:2',
        'ca_max_score' => 'decimal:2',
        'exam_max_score' => 'decimal:2',
        'require_all_subjects' => 'boolean',
        'show_positions' => 'boolean',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
