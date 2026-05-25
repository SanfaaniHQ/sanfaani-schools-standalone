<?php

namespace App\Services\Updates;

use App\Models\School;
use App\Models\User;
use App\Services\Licensing\LicenseEntitlementService;
use App\Services\Licensing\LicenseValidationService;
use App\Services\System\DeploymentBehaviorService;
use App\Services\System\DeploymentModeService;
use App\Services\System\FeatureAccessService;
use Throwable;

class UpdateEntitlementService
{
    public function __construct(
        private DeploymentModeService $deployment,
        private DeploymentBehaviorService $behavior,
        private FeatureAccessService $features,
        private LicenseValidationService $licenses,
        private LicenseEntitlementService $entitlements,
    ) {}

    public function check(?User $user = null): array
    {
        $user ??= auth()->user();
        $school = $this->defaultSchool();

        if (! (bool) config('updates.enabled', true) || ! $this->deployment->updatesEnabled()) {
            return $this->deny('disabled', 'Guided updates are disabled by configuration.');
        }

        if ($this->deployment->isDemo() || $this->isDemoUser($user)) {
            return $this->deny('demo_blocked', 'Demo environments cannot access the update manager.');
        }

        if ($this->deployment->isTrial() && ! (bool) config('updates.trial_allowed', false)) {
            return $this->deny('trial_blocked', 'Trial licenses are not allowed to access guided updates.');
        }

        $feature = (string) config('updates.feature', 'update_manager');
        if (! $this->features->enabled($feature, $school, $user)) {
            return $this->deny('feature_disabled', $this->features->reason($feature, $school, $user));
        }

        $routeGroup = $this->routeGroup();
        if ($routeGroup && ! $this->behavior->allowsRouteGroup($routeGroup, $school, $user)) {
            return $this->deny('deployment_blocked', 'Update manager is not available for this deployment behavior.');
        }

        if ($this->licenses->requiresValidation()) {
            $licenseResult = $this->licenses->validate($school);

            if (! $licenseResult->valid()) {
                return $this->deny('license_invalid', $licenseResult->message);
            }

            if ((bool) config('updates.require_license_entitlement', true) && ! $this->hasUpdateEntitlement($school)) {
                return $this->deny('entitlement_missing', 'The active license does not include update manager entitlement.');
            }
        }

        return [
            'allowed' => true,
            'status' => 'allowed',
            'message' => 'Update manager access is allowed.',
            'school_id' => $school?->id,
            'route_group' => $routeGroup,
            'label' => $this->label(),
        ];
    }

    public function defaultSchool(): ?School
    {
        if ($this->deployment->isSingleSchool() || $this->deployment->isManaged()) {
            return School::query()->orderBy('id')->first();
        }

        return null;
    }

    public function label(): string
    {
        return (string) config('updates.labels.'.$this->deployment->mode(), 'Guided Updates');
    }

    public function routeGroup(): ?string
    {
        $group = config('updates.deployment_route_groups.'.$this->deployment->mode());

        return filled($group) ? (string) $group : null;
    }

    private function hasUpdateEntitlement(?School $school): bool
    {
        foreach ((array) config('updates.entitlement_keys', ['update_manager']) as $key) {
            if ($this->entitlements->explicitAccess((string) $key, $school) === true) {
                return true;
            }
        }

        return false;
    }

    private function isDemoUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        try {
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
        } catch (Throwable) {
            return false;
        }
    }

    private function deny(string $status, string $message): array
    {
        return [
            'allowed' => false,
            'status' => $status,
            'message' => $message,
            'school_id' => null,
            'route_group' => $this->routeGroup(),
            'label' => $this->label(),
        ];
    }
}
