<?php

namespace App\Models\Admissions;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdmissionApplication extends Model
{
    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_UNDER_REVIEW = 'under_review';

    public const STATUS_MISSING_DOCUMENTS = 'missing_documents';

    public const STATUS_ENTRANCE_EXAM_SCHEDULED = 'entrance_exam_scheduled';

    public const STATUS_INTERVIEW_SCHEDULED = 'interview_scheduled';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_WAITLISTED = 'waitlisted';

    public const STATUS_PAYMENT_PENDING = 'payment_pending';

    public const STATUS_ADMITTED = 'admitted';

    public const STATUS_CONVERTED = 'converted_to_student';

    public const STATUSES = [
        self::STATUS_SUBMITTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_MISSING_DOCUMENTS,
        self::STATUS_ENTRANCE_EXAM_SCHEDULED,
        self::STATUS_INTERVIEW_SCHEDULED,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
        self::STATUS_WAITLISTED,
        self::STATUS_PAYMENT_PENDING,
        self::STATUS_ADMITTED,
        self::STATUS_CONVERTED,
    ];

    public const PAYMENT_NOT_REQUIRED = 'not_required';

    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_FAILED = 'failed';

    public const PAYMENT_WAIVED = 'waived';

    public const PAYMENT_CONFIRMED = 'confirmed';

    public const PAYMENT_STATUSES = [
        self::PAYMENT_NOT_REQUIRED,
        self::PAYMENT_PENDING,
        self::PAYMENT_PAID,
        self::PAYMENT_FAILED,
        self::PAYMENT_WAIVED,
        self::PAYMENT_CONFIRMED,
    ];

    protected $fillable = [
        'school_id',
        'admission_cycle_id',
        'application_number',
        'tracking_token',
        'first_name',
        'last_name',
        'other_names',
        'gender',
        'date_of_birth',
        'requested_class_id',
        'previous_school',
        'status',
        'source_channel',
        'payment_status',
        'submitted_at',
        'reviewed_at',
        'decided_at',
        'converted_student_id',
        'meta',
    ];

    protected $hidden = ['tracking_token'];

    protected $casts = [
        'date_of_birth' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'decided_at' => 'datetime',
        'meta' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(AdmissionCycle::class, 'admission_cycle_id');
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(AdmissionApplicantGuardian::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AdmissionDocument::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(AdmissionStatusLog::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(AdmissionNote::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(AdmissionInterview::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AdmissionPayment::class);
    }

    public function requestedClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'requested_class_id');
    }

    public function convertedStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'converted_student_id');
    }

    public function fullName(): string
    {
        return collect([$this->first_name, $this->other_names, $this->last_name])->filter()->implode(' ');
    }
}
