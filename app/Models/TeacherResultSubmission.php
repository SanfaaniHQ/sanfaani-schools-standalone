<?php

namespace App\Models;

use App\Enums\ResultWorkflowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherResultSubmission extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = [
        ResultWorkflowStatus::Draft->value,
        ResultWorkflowStatus::Submitted->value,
        ResultWorkflowStatus::Returned->value,
        ResultWorkflowStatus::Reviewed->value,
        ResultWorkflowStatus::Approved->value,
        ResultWorkflowStatus::Published->value,
        ResultWorkflowStatus::Unpublished->value,
        ResultWorkflowStatus::Voided->value,
        ResultWorkflowStatus::Archived->value,
    ];

    protected $fillable = [
        'school_id',
        'teacher_user_id',
        'school_class_id',
        'subject_id',
        'academic_session_id',
        'term_id',
        'result_type',
        'status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'published_by',
        'published_at',
        'returned_by',
        'returned_at',
        'return_reason',
        'metadata',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'returned_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function studentResults(): HasMany
    {
        return $this->hasMany(StudentResult::class);
    }

    public function workflowStatus(): ?ResultWorkflowStatus
    {
        return ResultWorkflowStatus::fromValue($this->status);
    }

    public function isTeacherEditable(): bool
    {
        return (bool) $this->workflowStatus()?->isTeacherEditable();
    }

    public function isLockedAfterApproval(): bool
    {
        return (bool) $this->workflowStatus()?->isLockedAfterApproval();
    }

    public function canTransitionTo(ResultWorkflowStatus $target): bool
    {
        return (bool) $this->workflowStatus()?->canTransitionTo($target);
    }
}
