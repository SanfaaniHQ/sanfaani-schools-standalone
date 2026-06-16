<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Events\PasswordResetEmailRequested;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['school_id', 'staff_code', 'name', 'email', 'password', 'must_change_password', 'disabled_at', 'archived_at', 'preferred_locale', 'avatar_path'])]
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

    public function scopeActiveAccount(Builder $query): Builder
    {
        return $query
            ->whereNull('disabled_at')
            ->whereNull('archived_at');
    }

    public function scopeDisabledAccount(Builder $query): Builder
    {
        return $query
            ->whereNull('archived_at')
            ->whereNotNull('disabled_at');
    }

    public function scopeArchivedAccount(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function isArchived(): bool
    {
        return filled($this->archived_at);
    }

    public function isDisabled(): bool
    {
        return filled($this->disabled_at);
    }

    public function isActiveAccount(): bool
    {
        return ! $this->isArchived() && ! $this->isDisabled();
    }

    public function accountStatus(): string
    {
        if ($this->isArchived()) {
            return 'archived';
        }

        if ($this->isDisabled()) {
            return 'disabled';
        }

        return 'active';
    }

    public function schoolAccessStatus(School|int|null $school = null, array $roles = []): string
    {
        if ($this->isArchived()) {
            return 'archived';
        }

        if ($this->isDisabled()) {
            return 'disabled';
        }

        if (! $school) {
            return 'active';
        }

        $schoolId = $school instanceof School ? $school->id : $school;
        $schoolRoles = $this->schoolRoles()
            ->where('school_id', $schoolId)
            ->when($roles !== [], fn ($query) => $query->whereIn('role_name', $roles))
            ->get();

        if ($schoolRoles->isNotEmpty() && $schoolRoles->every(fn (UserSchoolRole $role): bool => $role->status !== 'active')) {
            return 'disabled';
        }

        return 'active';
    }

    public function avatarUrl(): ?string
    {
        $path = str_replace('\\', '/', ltrim((string) $this->avatar_path, '/'));

        if ($path === '' || Str::contains($path, ['..', '.env', 'storage/app/private'])) {
            return null;
        }

        if (! Str::startsWith($path, 'avatars/')) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $initials = collect($words)
            ->filter()
            ->take(2)
            ->map(fn (string $word): string => Str::substr($word, 0, 1))
            ->implode('');

        return mb_strtoupper($initials ?: 'U');
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
            'disabled_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }
}
