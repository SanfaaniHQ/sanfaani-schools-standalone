<?php

namespace App\Models;

use App\Enums\ResultWorkflowStatus;
use App\Services\CurrentSchoolService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentResult extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        ResultWorkflowStatus::Locked->value,
    ];

    protected $fillable = [
        'school_id',
        'student_id',
        'school_class_id',
        'subject_id',
        'academic_session_id',
        'term_id',
        'result_type',
        'ca_score',
        'exam_score',
        'total_score',
        'grade',
        'remark',
        'teacher_remark',
        'officer_remark',
        'admin_remark',
        'status',
        'published_at',
        'published_by',
        'unpublished_at',
        'unpublished_by',
        'unpublish_reason',
        'recorded_by',
        'updated_by',
        'approved_by',
        'result_version',
        'teacher_result_submission_id',
    ];

    protected $casts = [
        'ca_score' => 'decimal:2',
        'exam_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'published_at' => 'datetime',
        'unpublished_at' => 'datetime',
        'result_version' => 'integer',
    ];

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

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function unpublishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unpublished_by');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function teacherResultSubmission(): BelongsTo
    {
        return $this->belongsTo(TeacherResultSubmission::class);
    }

    public function workflowStatus(): ?ResultWorkflowStatus
    {
        return ResultWorkflowStatus::fromValue($this->status);
    }

    public function isLockedAfterApproval(): bool
    {
        return (bool) $this->workflowStatus()?->isLockedAfterApproval();
    }

    public function canTransitionTo(ResultWorkflowStatus $target): bool
    {
        return (bool) $this->workflowStatus()?->canTransitionTo($target);
    }

    protected static function booted(): void
    {
        static::updating(function (StudentResult $result): bool {
            $actor = auth()->user();

            $originalStatus = ResultWorkflowStatus::fromValue((string) $result->getOriginal('status'));

            $roleContext = $actor ? app(CurrentSchoolService::class)->roleContext($actor) : null;

            if ($roleContext === 'teacher' && $originalStatus?->isLockedAfterApproval()) {
                return false;
            }

            if (! $result->isDirty('result_version')) {
                $result->result_version = max(1, (int) $result->getOriginal('result_version')) + 1;
            }

            return true;
        });
    }
}
