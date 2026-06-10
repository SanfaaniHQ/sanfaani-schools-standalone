<?php

namespace App\Models;

use App\Models\Admissions\AdmissionApiKey;
use App\Models\Admissions\AdmissionApplication;
use App\Models\Admissions\AdmissionChannel;
use App\Models\Admissions\AdmissionCycle;
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
        'logo_path',
        'favicon_path',
        'login_background_path',
        'report_header_path',
        'email_logo_path',
        'primary_color',
        'secondary_color',
        'school_motto',
        'status',
        'subscription_status',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'sender_email',
        'sender_name',
        'result_checker_slug',
        'is_result_checker_enabled',
        'custom_css',
        'default_language',
        'supports_rtl',
    ];

    protected $casts = [
        'supports_rtl' => 'boolean',
        'is_result_checker_enabled' => 'boolean',
        'smtp_password' => 'encrypted',
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

    public function brandingSettings(): HasMany
    {
        return $this->hasMany(BrandingSetting::class);
    }

    public function activeBrandingSetting(): HasOne
    {
        return $this->hasOne(BrandingSetting::class)
            ->where('scope', BrandingSetting::SCOPE_SCHOOL)
            ->where('is_active', true)
            ->latestOfMany();
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

    public function supportEscalationHistories(): HasMany
    {
        return $this->hasMany(SupportEscalationHistory::class);
    }

    public function supportThreadEvents(): HasMany
    {
        return $this->hasMany(SupportThreadEvent::class);
    }

    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class);
    }

    public function bulkCommunicationBatches(): HasMany
    {
        return $this->hasMany(BulkCommunicationBatch::class);
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

    public function admissionCycles(): HasMany
    {
        return $this->hasMany(AdmissionCycle::class);
    }

    public function admissionApplications(): HasMany
    {
        return $this->hasMany(AdmissionApplication::class);
    }

    public function admissionChannels(): HasMany
    {
        return $this->hasMany(AdmissionChannel::class);
    }

    public function admissionApiKeys(): HasMany
    {
        return $this->hasMany(AdmissionApiKey::class);
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

    public function reportCardSnapshots(): HasMany
    {
        return $this->hasMany(ReportCardSnapshot::class);
    }

    public function pdfSnapshots(): HasMany
    {
        return $this->hasMany(PdfSnapshot::class);
    }

    public function cbtQuestionBanks(): HasMany
    {
        return $this->hasMany(CbtQuestionBank::class);
    }

    public function cbtQuestions(): HasMany
    {
        return $this->hasMany(CbtQuestion::class);
    }

    public function cbtExams(): HasMany
    {
        return $this->hasMany(CbtExam::class);
    }

    public function cbtAttempts(): HasMany
    {
        return $this->hasMany(CbtAttempt::class);
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

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function licenseActivations(): HasMany
    {
        return $this->hasMany(LicenseActivation::class);
    }

    public function licenseAuditLogs(): HasMany
    {
        return $this->hasMany(LicenseAuditLog::class);
    }

    public function demoSessions(): HasMany
    {
        return $this->hasMany(DemoSession::class);
    }

    public function userOnboardingProgress(): HasMany
    {
        return $this->hasMany(UserOnboardingProgress::class);
    }

    public function onboardingEventLogs(): HasMany
    {
        return $this->hasMany(OnboardingEventLog::class);
    }

    public function marketingLeadScores(): HasMany
    {
        return $this->hasMany(MarketingLeadScore::class);
    }

    public function marketingActivities(): HasMany
    {
        return $this->hasMany(MarketingLeadActivity::class);
    }

    public function salesTasks(): HasMany
    {
        return $this->hasMany(SalesTask::class);
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
        return $this->assetUrl($this->logo_path ?: $this->logo);
    }

    public function faviconUrl(): ?string
    {
        return $this->assetUrl($this->favicon_path);
    }

    public function loginBackgroundUrl(): ?string
    {
        return $this->assetUrl($this->login_background_path);
    }

    public function reportHeaderUrl(): ?string
    {
        return $this->assetUrl($this->report_header_path);
    }

    public function emailLogoUrl(): ?string
    {
        return $this->assetUrl($this->email_logo_path ?: $this->logo_path ?: $this->logo);
    }

    private function assetUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $path = str_replace('\\', '/', ltrim((string) $path, '/'));

        if (Str::contains($path, ['..', '.env', 'storage/app/private', ':'])) {
            return null;
        }

        return Storage::disk('public')->url($path);
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
