<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'student_id',
        'payable_type',
        'payable_id',
        'amount',
        'currency',
        'payment_method',
        'payment_gateway',
        'gateway_reference',
        'payment_reference',
        'status',
        'paid_at',
        'confirmed_by',
        'confirmed_at',
        'payment_proof_path',
        'manual_payment_note',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
