<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class School extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'school_code',
        'email',
        'phone',
        'address',
        'logo',
        'status',
        'subscription_status',
        'default_language',
        'supports_rtl',
    ];

    protected $casts = [
        'supports_rtl' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function schoolClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function classSubjectAssignments(): HasMany
    {
        return $this->hasMany(ClassSubjectAssignment::class);
    }

    public function studentElectiveSubjects(): HasMany
    {
        return $this->hasMany(StudentElectiveSubject::class);
    }

    public function userSchoolRoles(): HasMany
    {
        return $this->hasMany(UserSchoolRole::class);
    }

    public function languagePreferences(): HasMany
    {
        return $this->hasMany(LanguagePreference::class);
    }

    public function publicPage(): HasOne
    {
        return $this->hasOne(SchoolPublicPage::class);
    }

    public function websiteSetting(): HasOne
    {
        return $this->hasOne(SchoolWebsiteSetting::class);
    }

    public function teacherClassAssignments(): HasMany
    {
        return $this->hasMany(TeacherClassAssignment::class);
    }

    public function teacherSubjectAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubjectAssignment::class);
    }

    public function teacherResultSubmissions(): HasMany
    {
        return $this->hasMany(TeacherResultSubmission::class);
    }

    public function supportThreads(): HasMany
    {
        return $this->hasMany(SupportThread::class);
    }

    public function supportMessages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }

    public function academicSessions(): HasMany
    {
        return $this->hasMany(AcademicSession::class);
    }

    public function terms(): HasMany
    {
        return $this->hasMany(Term::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function studentClassEnrollments(): HasMany
    {
        return $this->hasMany(StudentClassEnrollment::class);
    }

    public function studentPromotionBatches(): HasMany
    {
        return $this->hasMany(StudentPromotionBatch::class);
    }

    public function reportCardSetting(): HasOne
    {
        return $this->hasOne(SchoolReportCardSetting::class);
    }

    public function reportCardCommentRules(): HasMany
    {
        return $this->hasMany(ReportCardCommentRule::class);
    }

    public function admissionNumberSetting(): HasOne
    {
        return $this->hasOne(AdmissionNumberSetting::class);
    }

    public function studentResults(): HasMany
    {
        return $this->hasMany(StudentResult::class);
    }

    public function gradingScales(): HasMany
    {
        return $this->hasMany(GradingScale::class);
    }

    public function resultPublications(): HasMany
    {
        return $this->hasMany(ResultPublication::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SchoolSubscription::class);
    }

    public function featureOverrides(): HasMany
    {
        return $this->hasMany(SchoolFeatureOverride::class);
    }

    public function resultAccessPolicies(): HasMany
    {
        return $this->hasMany(SchoolResultAccessPolicy::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function scratchCardBatches(): HasMany
    {
        return $this->hasMany(ScratchCardBatch::class);
    }

    public function scratchCards(): HasMany
    {
        return $this->hasMany(ScratchCard::class);
    }

    public function scratchCardUsages(): HasMany
    {
        return $this->hasMany(ScratchCardUsage::class);
    }

    public function resultVerifications(): HasMany
    {
        return $this->hasMany(ResultVerification::class);
    }

    public function logoUrl(): ?string
    {
        if (! filled($this->logo)) {
            return null;
        }

        if (Str::startsWith($this->logo, ['http://', 'https://'])) {
            return $this->logo;
        }

        return Storage::disk('public')->url(ltrim($this->logo, '/'));
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name)) ?: [];
        $initials = collect($words)
            ->filter()
            ->take(2)
            ->map(fn (string $word) => mb_substr($word, 0, 1))
            ->implode('');

        return mb_strtoupper($initials ?: 'S');
    }
}
