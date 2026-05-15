<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScratchCardBatch extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'batch_code',
        'school_id',
        'requested_by',
        'school_class_id',
        'academic_session_id',
        'term_id',
        'result_type',
        'school_result_access_policy_id',
        'title',
        'quantity',
        'amount',
        'currency',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_confirmed_at',
        'payment_confirmed_by',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'approval_note',
        'status',
        'expires_at',
        'generated_by',
        'failed_generation_at',
        'failed_generation_reason',
        'last_exported_at',
        'last_exported_by',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_confirmed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expires_at' => 'datetime',
        'failed_generation_at' => 'datetime',
        'last_exported_at' => 'datetime',
        'metadata' => 'array',
    ];

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

    public function accessPolicy(): BelongsTo
    {
        return $this->belongsTo(SchoolResultAccessPolicy::class, 'school_result_access_policy_id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(ScratchCard::class);
    }

    public function paymentConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_confirmed_by');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function lastExportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_exported_by');
    }
}
