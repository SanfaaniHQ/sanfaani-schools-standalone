<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentClassEnrollment extends Model
{
    public const CURRENT_STATUSES = ['active', 'repeating'];

    public const TERMINAL_STATUSES = ['completed', 'graduated', 'transferred', 'withdrawn'];

    protected $fillable = [
        'school_id',
        'student_id',
        'school_class_id',
        'academic_session_id',
        'start_term_id',
        'end_term_id',
        'status',
        'created_by',
        'enrolled_at',
        'promoted_from_enrollment_id',
        'metadata',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function scopeCurrent(Builder $query): Builder
    {
        return $query
            ->whereIn('status', self::CURRENT_STATUSES)
            ->whereNull('end_term_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function startTerm(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'start_term_id');
    }

    public function endTerm(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'end_term_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function promotedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'promoted_from_enrollment_id');
    }

    public function promotedToEnrollments(): HasMany
    {
        return $this->hasMany(self::class, 'promoted_from_enrollment_id');
    }

    public function promotionItemsFrom(): HasMany
    {
        return $this->hasMany(StudentPromotionItem::class, 'from_student_class_enrollment_id');
    }

    public function promotionItemsTo(): HasMany
    {
        return $this->hasMany(StudentPromotionItem::class, 'to_student_class_enrollment_id');
    }
}
