<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultAccessRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';

    public const METHOD_MANUAL_APPROVAL = 'manual_approval';
    public const METHOD_PAYMENT_REQUEST = 'payment_request';
    public const METHOD_SCRATCH_CARD = 'scratch_card';

    protected $fillable = [
        'school_id',
        'student_id',
        'requester_user_id',
        'academic_session_id',
        'term_id',
        'result_type',
        'access_method',
        'status',
        'payment_transaction_id',
        'scratch_card_id',
        'approved_by',
        'rejected_by',
        'approved_at',
        'rejected_at',
        'expires_at',
        'request_note',
        'decision_note',
        'metadata',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    public function scratchCard(): BelongsTo
    {
        return $this->belongsTo(ScratchCard::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED
            && (! $this->expires_at || $this->expires_at->isFuture());
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending approval',
            self::STATUS_PENDING_PAYMENT => 'Awaiting payment confirmation',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function methodLabel(): string
    {
        return match ($this->access_method) {
            self::METHOD_SCRATCH_CARD => 'Scratch card',
            self::METHOD_PAYMENT_REQUEST => 'Payment request',
            self::METHOD_MANUAL_APPROVAL => 'Manual approval',
            default => ucfirst(str_replace('_', ' ', (string) $this->access_method)),
        };
    }
}
