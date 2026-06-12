<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsCbtActivity extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    public const TARGET_CLASSROOM = 'classroom';
    public const TARGET_MATERIAL = 'material';

    protected $fillable = [
        'school_id',
        'lms_classroom_id',
        'lms_material_id',
        'cbt_exam_id',
        'school_class_id',
        'subject_id',
        'academic_session_id',
        'term_id',
        'target_type',
        'target_id',
        'title',
        'description',
        'status',
        'created_by',
        'updated_by',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function scopeForSchool(Builder $query, School|int $school): Builder
    {
        return $query->where('school_id', $school instanceof School ? $school->id : $school);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(LmsClassroom::class, 'lms_classroom_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(LmsMaterial::class, 'lms_material_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
