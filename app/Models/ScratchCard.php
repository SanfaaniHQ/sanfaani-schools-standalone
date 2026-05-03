<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScratchCard extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'scratch_card_batch_id',
        'school_id',
        'school_class_id',
        'academic_session_id',
        'term_id',
        'result_type',
        'serial_number',
        'pin_code',
        'pin_hash',
        'max_uses',
        'used_count',
        'status',
        'used_by_student_id',
        'first_used_at',
        'last_used_at',
        'revoked_at',
        'revoked_by',
        'revoke_reason',
        'expires_at',
        'generated_by',
        'metadata',
    ];

    protected $casts = [
        'pin_code' => 'encrypted',
        'first_used_at' => 'datetime',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ScratchCardBatch::class, 'scratch_card_batch_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
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

    public function usedByStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'used_by_student_id');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(ScratchCardUsage::class);
    }
}
