<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CbtExam extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPES = [
        'objective',
        'theory',
        'practical',
        'mixed',
        'timed',
        'open',
        'scheduled',
        'competition',
        'assignment',
        'continuous_assessment',
        'mock',
        'entrance',
        'certification',
    ];

    public const ACCESS_TYPES = [
        'internal_student',
        'public_candidate',
        'generated_code',
        'email_invitation',
        'scratch_access',
        'session_token',
        'admission_number',
    ];

    protected $fillable = [
        'school_id',
        'subject_id',
        'school_class_id',
        'academic_session_id',
        'term_id',
        'title',
        'slug',
        'description',
        'instructions',
        'exam_type',
        'access_type',
        'result_type',
        'status',
        'starts_at',
        'ends_at',
        'duration_minutes',
        'max_attempts',
        'question_count',
        'total_marks',
        'pass_mark',
        'randomize_questions',
        'randomize_options',
        'allow_resume',
        'auto_submit',
        'show_result_immediately',
        'supports_public_candidates',
        'require_fullscreen',
        'release_results_at',
        'language_settings',
        'anti_cheat_settings',
        'metadata',
        'created_by',
        'updated_by',
        'published_by',
        'published_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'published_at' => 'datetime',
        'release_results_at' => 'datetime',
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'allow_resume' => 'boolean',
        'auto_submit' => 'boolean',
        'show_result_immediately' => 'boolean',
        'supports_public_candidates' => 'boolean',
        'require_fullscreen' => 'boolean',
        'total_marks' => 'decimal:2',
        'pass_mark' => 'decimal:2',
        'language_settings' => 'array',
        'anti_cheat_settings' => 'array',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(CbtExamQuestion::class)->orderBy('sort_order')->orderBy('id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(CbtCandidate::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(CbtAttempt::class);
    }

    public function accessCodes(): HasMany
    {
        return $this->hasMany(CbtAccessCode::class);
    }

    public function lmsCbtActivities(): HasMany
    {
        return $this->hasMany(LmsCbtActivity::class);
    }

    public function isOpenForEntry(): bool
    {
        if (! in_array($this->status, ['scheduled', 'open', 'published'], true)) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        return ! ($this->ends_at && now()->gt($this->ends_at));
    }
}
