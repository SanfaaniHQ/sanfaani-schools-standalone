<?php

namespace App\Models\Admissions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionApplicantGuardian extends Model
{
    protected $fillable = [
        'admission_application_id',
        'name',
        'relationship',
        'phone',
        'email',
        'address',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }
}
