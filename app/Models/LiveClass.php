<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveClass extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const PROVIDER_MANUAL = 'manual';

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_LIVE = 'live';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_LIVE,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'school_id',
        'school_class_id',
        'subject_id',
        'academic_session_id',
        'term_id',
        'lms_classroom_id',
        'lms_material_id',
        'teacher_user_id',
        'title',
        'description',
        'provider',
        'meeting_url',
        'meeting_password',
        'starts_at',
        'ends_at',
        'timezone',
        'status',
        'recording_url',
        'created_by',
        'updated_by',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function scopeForSchool(Builder $query, School|int $school): Builder
    {
        return $query->where('school_id', $school instanceof School ? $school->id : $school);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now());
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
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

    public function lmsClassroom(): BelongsTo
    {
        return $this->belongsTo(LmsClassroom::class);
    }

    public function lmsMaterial(): BelongsTo
    {
        return $this->belongsTo(LmsMaterial::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
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
