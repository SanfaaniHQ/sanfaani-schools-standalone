<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'school_class_id',
        'admission_number',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'address',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(StudentResult::class);
    }

    public function classEnrollments(): HasMany
    {
        return $this->hasMany(StudentClassEnrollment::class);
    }

    public function currentEnrollment(): HasOne
    {
        return $this->hasOne(StudentClassEnrollment::class)
            ->whereIn('status', StudentClassEnrollment::CURRENT_STATUSES)
            ->whereNull('end_term_id')
            ->latestOfMany();
    }

    public function promotionItems(): HasMany
    {
        return $this->hasMany(StudentPromotionItem::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function usedScratchCards(): HasMany
    {
        return $this->hasMany(ScratchCard::class, 'used_by_student_id');
    }

    public function scratchCardUsages(): HasMany
    {
        return $this->hasMany(ScratchCardUsage::class);
    }

    public function resultVerifications(): HasMany
    {
        return $this->hasMany(ResultVerification::class);
    }

    public function reportCardSnapshots(): HasMany
    {
        return $this->hasMany(ReportCardSnapshot::class);
    }

    public function electiveSubjects(): HasMany
    {
        return $this->hasMany(StudentElectiveSubject::class);
    }

    public function fullName(): string
    {
        return collect([$this->first_name, $this->middle_name, $this->last_name])
            ->filter(fn ($part) => filled($part))
            ->implode(' ');
    }
}
