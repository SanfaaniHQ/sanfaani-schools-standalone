<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOnboardingProgress extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_SKIPPED = 'skipped';

    protected $table = 'user_onboarding_progress';

    protected $fillable = [
        'user_id',
        'school_id',
        'onboarding_checklist_id',
        'onboarding_step_id',
        'status',
        'completed_at',
        'skipped_at',
        'metadata',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'skipped_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(OnboardingChecklist::class, 'onboarding_checklist_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(OnboardingStep::class, 'onboarding_step_id');
    }
}
