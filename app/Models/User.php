<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Events\PasswordResetEmailRequested;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['school_id', 'staff_code', 'name', 'email', 'password', 'must_change_password', 'preferred_locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolRoles(): HasMany
    {
        return $this->hasMany(UserSchoolRole::class);
    }

    public function activeSchoolRoles(): HasMany
    {
        return $this->schoolRoles()->where('status', 'active');
    }

    public function teacherClassAssignments(): HasMany
    {
        return $this->hasMany(TeacherClassAssignment::class, 'teacher_user_id');
    }

    public function teacherSubjectAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubjectAssignment::class, 'teacher_user_id');
    }

    public function teacherResultSubmissions(): HasMany
    {
        return $this->hasMany(TeacherResultSubmission::class, 'teacher_user_id');
    }

    public function lmsTeachingMaterials(): HasMany
    {
        return $this->hasMany(LmsMaterial::class, 'teacher_user_id');
    }

    public function liveClasses(): HasMany
    {
        return $this->hasMany(LiveClass::class, 'teacher_user_id');
    }

    public function createdLiveClasses(): HasMany
    {
        return $this->hasMany(LiveClass::class, 'created_by');
    }

    public function createdLmsClassrooms(): HasMany
    {
        return $this->hasMany(LmsClassroom::class, 'created_by');
    }

    public function createdLmsMaterials(): HasMany
    {
        return $this->hasMany(LmsMaterial::class, 'created_by');
    }

    public function uploadedLmsResources(): HasMany
    {
        return $this->hasMany(LmsResource::class, 'uploaded_by');
    }

    public function recordedAttendanceRecords(): HasMany
    {
        return $this->hasMany(StudentAttendanceRecord::class, 'recorded_by');
    }

    public function createdFinanceFeeItems(): HasMany
    {
        return $this->hasMany(FinanceFeeItem::class, 'created_by');
    }

    public function createdFinanceFeeAssignments(): HasMany
    {
        return $this->hasMany(FinanceFeeAssignment::class, 'created_by');
    }

    public function createdFeeInvoices(): HasMany
    {
        return $this->hasMany(StudentFeeInvoice::class, 'created_by');
    }

    public function receivedFeePayments(): HasMany
    {
        return $this->hasMany(StudentFeePayment::class, 'received_by');
    }

    public function cbtAttempts(): HasMany
    {
        return $this->hasMany(CbtAttempt::class);
    }

    public function supportThreadsCreated(): HasMany
    {
        return $this->hasMany(SupportThread::class, 'created_by');
    }

    public function supportMessages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'sender_id');
    }

    public function assignedSupportThreads(): HasMany
    {
        return $this->hasMany(SupportThread::class, 'assigned_to');
    }

    public function escalatedSupportThreads(): HasMany
    {
        return $this->hasMany(SupportThread::class, 'escalated_by');
    }

    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class, 'sender_id');
    }

    public function bulkCommunicationBatches(): HasMany
    {
        return $this->hasMany(BulkCommunicationBatch::class, 'sender_id');
    }

    public function assignedLeadRequests(): HasMany
    {
        return $this->hasMany(LeadRequest::class, 'assigned_to');
    }

    public function demoCredentials(): HasMany
    {
        return $this->hasMany(DemoCredential::class);
    }

    public function demoActivities(): HasMany
    {
        return $this->hasMany(DemoActivity::class);
    }

    public function onboardingProgress(): HasMany
    {
        return $this->hasMany(UserOnboardingProgress::class);
    }

    public function onboardingEventLogs(): HasMany
    {
        return $this->hasMany(OnboardingEventLog::class);
    }

    public function leadNotes(): HasMany
    {
        return $this->hasMany(LeadNote::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        PasswordResetEmailRequested::dispatch($this, $token);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }
}
