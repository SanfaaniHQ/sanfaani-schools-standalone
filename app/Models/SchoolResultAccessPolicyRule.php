<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolResultAccessPolicyRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_result_access_policy_id',
        'academic_session_id',
        'term_id',
        'result_type',
        'access_scope',
        'max_access_per_student',
        'max_access_per_card',
        'requires_scratch_card',
        'allows_parent_payment',
        'allows_school_paid_access',
        'allows_pdf_download',
        'status',
        'starts_at',
        'ends_at',
        'metadata',
    ];

    protected $casts = [
        'requires_scratch_card' => 'boolean',
        'allows_parent_payment' => 'boolean',
        'allows_school_paid_access' => 'boolean',
        'allows_pdf_download' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(SchoolResultAccessPolicy::class, 'school_result_access_policy_id');
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }
}