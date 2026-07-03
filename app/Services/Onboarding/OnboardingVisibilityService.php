<?php

namespace App\Services\Onboarding;

use App\Models\OnboardingChecklist;
use App\Models\OnboardingStep;
use App\Models\School;
use App\Models\User;
use App\Services\CurrentSchoolService;
use App\Services\Licensing\LicenseValidationService;
use App\Services\System\DeploymentModeService;
use App\Services\System\FeatureAccessService;
use InvalidArgumentException;

class OnboardingVisibilityService
{
    public function __construct(
        private DeploymentModeService $deployment,
        private FeatureAccessService $features,
        private CurrentSchoolService $currentSchool,
        private LicenseValidationService $licenses,
    ) {}

    public function enabled(?User $user = null, ?School $school = null): bool
    {
        try {
            if (! (bool) config('onboarding.enabled', true)) {
                return false;
            }

            if (! $this->features->enabled('guided_onboarding', $school, $user)) {
                return false;
            }

            $licenseMode = $this->deployment->licenseMode();

            if ($this->licenses->requiresValidation()
                && $licenseMode === DeploymentModeService::LICENSE_DEMO
                && ! (bool) config('onboarding.demo_enabled', true)) {
                return false;
            }

            if ($this->licenses->requiresValidation()
                && $licenseMode === DeploymentModeService::LICENSE_TRIAL
                && ! (bool) config('onboarding.trial_enabled', true)) {
                return false;
            }

            $license = $this->licenses->current($school);

            return ! $license || $this->licenses->isValid($school);
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function checklistVisible(OnboardingChecklist $checklist, ?User $user = null, ?School $school = null): bool
    {
        if (! $this->enabled($user, $school) || ! $checklist->is_active) {
            return false;
        }

        return $this->matchesMode($checklist->deployment_modes, $this->deployment->mode())
            && (! $this->licenses->requiresValidation()
                || $this->matchesMode($checklist->license_modes, $this->deployment->licenseMode()));
    }

    public function stepVisible(OnboardingStep $step, ?User $user = null, ?School $school = null): bool
    {
        if (! $this->checklistVisible($step->checklist, $user, $school)) {
            return false;
        }

        if (! $this->matchesMode($step->deployment_modes, $this->deployment->mode())) {
            return false;
        }

        if ($this->licenses->requiresValidation()
            && ! $this->matchesMode($step->license_modes, $this->deployment->licenseMode())) {
            return false;
        }

        return ! $step->feature_key || $this->features->enabled($step->feature_key, $school, $user);
    }

    public function roleFor(User $user): ?string
    {
        $role = $this->currentSchool->roleContext($user) ?: $user->roles()->pluck('name')->first();
        $role = $role ? $this->normalize($role) : null;

        return in_array($role, config('onboarding.roles', []), true) ? $role : null;
    }

    public function isDemoUser(User $user): bool
    {
        return $user->demoCredentials()
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereHas('demoSession', function ($query): void {
                $query->where('status', 'active')
                    ->where(function ($query): void {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
            })
            ->exists();
    }

    private function matchesMode(mixed $configured, string $current): bool
    {
        $values = collect((array) $configured)
            ->map(fn (mixed $value): string => $this->normalize((string) $value))
            ->filter()
            ->values()
            ->all();

        return $values === [] || in_array($current, $values, true);
    }

    private function normalize(string $value): string
    {
        return str($value)->trim()->lower()->replace(['-', ' '], '_')->toString();
    }
}
