<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;

class OnboardingStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'onboarding_checklist_id',
        'key',
        'title',
        'description',
        'action_label',
        'action_url',
        'route_name',
        'feature_key',
        'deployment_modes',
        'license_modes',
        'required',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'deployment_modes' => 'array',
        'license_modes' => 'array',
        'required' => 'boolean',
        'metadata' => 'array',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(OnboardingChecklist::class, 'onboarding_checklist_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(UserOnboardingProgress::class);
    }

    public function resolvedActionUrl(): ?string
    {
        if ($this->route_name && Route::has($this->route_name)) {
            return route($this->route_name);
        }

        return $this->action_url;
    }
}
