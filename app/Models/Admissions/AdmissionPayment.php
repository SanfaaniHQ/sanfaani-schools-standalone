<?php

namespace App\Models\Admissions;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionPayment extends Model
{
    public const STATUSES = ['pending', 'paid', 'failed', 'waived', 'confirmed'];

    public const METHODS = ['manual', 'online', 'waived'];

    protected $fillable = [
        'admission_application_id',
        'amount',
        'currency',
        'method',
        'status',
        'reference',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
