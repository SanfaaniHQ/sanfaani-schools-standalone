<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_uuid',
        'school_id',
        'cbt_exam_id',
        'cbt_candidate_id',
        'student_id',
        'user_id',
        'attempt_no',
        'status',
        'access_channel',
        'started_at',
        'expires_at',
        'last_autosaved_at',
        'submitted_at',
        'graded_at',
        'objective_score',
        'theory_score',
        'total_score',
        'max_score',
        'grade',
        'remark',
        'result_release_status',
        'student_result_id',
        'answers_hash',
        'client_snapshot',
        'security_snapshot',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_autosaved_at' => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'objective_score' => 'decimal:2',
        'theory_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'client_snapshot' => 'array',
        'security_snapshot' => 'array',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(CbtCandidate::class, 'cbt_candidate_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studentResult(): BelongsTo
    {
        return $this->belongsTo(StudentResult::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CbtAttemptAnswer::class);
    }

    public function isOpen(): bool
    {
        if (! in_array($this->status, ['in_progress', 'resumed'], true)) {
            return false;
        }

        return ! ($this->expires_at && now()->gt($this->expires_at));
    }
}
