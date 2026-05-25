<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnboardingChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'role_name',
        'deployment_modes',
        'license_modes',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'deployment_modes' => 'array',
        'license_modes' => 'array',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(OnboardingStep::class)->orderBy('sort_order')->orderBy('id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(UserOnboardingProgress::class);
    }
}
