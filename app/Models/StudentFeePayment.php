<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFeePayment extends Model
{
    use HasFactory;

    public const METHODS = ['manual', 'cash', 'bank_transfer', 'card', 'other'];

    protected $fillable = [
        'school_id',
        'student_fee_invoice_id',
        'student_id',
        'amount',
        'payment_date',
        'method',
        'reference',
        'received_by',
        'note',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(StudentFeeInvoice::class, 'student_fee_invoice_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
