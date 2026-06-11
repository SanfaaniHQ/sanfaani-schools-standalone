<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFeeInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'student_fee_invoice_id',
        'fee_item_id',
        'description',
        'amount',
        'discount_amount',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
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

    public function feeItem(): BelongsTo
    {
        return $this->belongsTo(FinanceFeeItem::class, 'fee_item_id');
    }
}
