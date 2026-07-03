<?php

namespace App\Services\System;

use App\Models\School;
use App\Models\SchoolFeatureOverride;
use App\Models\User;
use App\Services\CurrentSchoolService;
use App\Services\Licensing\LicenseEntitlementService;
use App\Services\SchoolAuthorizationService;
use App\Services\SchoolFeatureAccessService;

class FeatureAccessService
{
    public function __construct(
        private DeploymentModeService $deployment,
        private CurrentSchoolService $currentSchool,
        private SchoolFeatureAccessService $schoolFeatures,
    ) {}

    public function enabled(string $feature, ?School $school = null, ?User $user = null): bool
    {
        return $this->evaluate($feature, $school, $user)['enabled'];
    }

    public function disabled(string $feature, ?School $school = null, ?User $user = null): bool
    {
        return ! $this->enabled($feature, $school, $user);
    }

    public function reason(string $feature, ?School $school = null, ?User $user = null): string
    {
        return $this->evaluate($feature, $school, $user)['reason'];
    }

    public function all(?School $school = null, ?User $user = null): array
    {
        return collect(config('features.features', []))
            ->mapWithKeys(function (array $config, string $feature) use ($school, $user): array {
                $enabled = $this->enabled($feature, $school, $user);

                return [
                    $feature => $config + [
                        'key' => $feature,
                        'enabled_for_context' => $enabled,
                        'reason' => $this->reason($feature, $school, $user),
                        'visible' => $enabled || ! (bool) ($config['hidden_when_disabled'] ?? true),
                    ],
                ];
            })
            ->all();
    }

    public function enabledForDeploymentMode(string $feature): bool
    {
        $config = $this->featureConfig($feature);

        if ($config === null || ! (bool) ($config['enabled'] ?? false)) {
            return false;
        }

        $modes = $this->normalizedList($config['deployment_modes'] ?? []);

        return $modes === [] || in_array($this->deployment->mode(), $modes, true);
    }

    public function enabledForLicenseMode(string $feature): bool
    {
        $config = $this->featureConfig($feature);

        if ($config === null || ! (bool) ($config['enabled'] ?? false)) {
            return false;
        }

        if (! $this->licenseValidationEnabled()) {
            return $this->normalize($feature) !== 'license_activation';
        }

        $modes = $this->normalizedList($config['license_modes'] ?? []);

        return $modes === [] || in_array($this->deployment->licenseMode(), $modes, true);
    }

    public function enabledForSchool(string $feature, School $school): bool
    {
        $config = $this->featureConfig($feature);

        if ($config === null || ! (bool) ($config['enabled'] ?? false)) {
            return false;
        }

        if (! $this->enabledForDeploymentMode($feature) || ! $this->enabledForLicenseMode($feature)) {
            return false;
        }

        $override = $this->activeOverride($feature, $school, $config);

        if ($override) {
            return (bool) $override->is_enabled;
        }

        $explicitAccess = $this->explicitSchoolAccess($feature, $school, $config);

        if ($explicitAccess !== null) {
            return $explicitAccess;
        }

        if (! $this->licenseValidationEnabled()) {
            return true;
        }

        return $this->explicitLicenseAccess($feature, $school, $config) ?? true;
    }

    public function isOverriddenForSchool(string $feature, School $school): bool
    {
        $config = $this->featureConfig($feature);

        return $config !== null && $this->activeOverride($feature, $school, $config) !== null;
    }

    private function evaluate(string $feature, ?School $school = null, ?User $user = null): array
    {
        $feature = $this->normalize($feature);
        $config = $this->featureConfig($feature);
        $user ??= auth()->user();
        $school ??= $user ? $this->currentSchool->get($user) : null;

        if ($config === null) {
            return $this->decision(false, "Unknown feature [{$feature}].");
        }

        if (! (bool) ($config['enabled'] ?? false)) {
            return $this->decision(false, "Feature [{$feature}] is globally disabled.");
        }

        if (! $this->enabledForDeploymentMode($feature)) {
            return $this->decision(false, "Feature [{$feature}] is disabled for portal mode [{$this->deployment->mode()}].");
        }

        if (! $this->enabledForLicenseMode($feature)) {
            return $this->decision(false, "Feature [{$feature}] is disabled for license mode [{$this->deployment->licenseMode()}].");
        }

        if ($this->hasSuperAdminBypass($user, $config)) {
            return $this->decision(true, "Feature [{$feature}] is enabled by Super Admin bypass.");
        }

        if ((bool) ($config['requires_school'] ?? false) && ! $school) {
            return $this->decision(false, "Feature [{$feature}] requires an active school context.");
        }

        if ($school) {
            $override = $this->activeOverride($feature, $school, $config);

            if ($override) {
                return $this->decision(
                    (bool) $override->is_enabled,
                    "Feature [{$feature}] is ".((bool) $override->is_enabled ? 'enabled' : 'disabled').' by school override.'
                );
            }

            $explicitAccess = $this->explicitSchoolAccess($feature, $school, $config);

            if ($explicitAccess !== null) {
                return $this->decision(
                    $explicitAccess,
                    "Feature [{$feature}] is ".($explicitAccess ? 'enabled' : 'disabled').' by subscription entitlement.'
                );
            }

            $licenseAccess = $this->licenseValidationEnabled()
                ? $this->explicitLicenseAccess($feature, $school, $config)
                : null;

            if ($licenseAccess !== null) {
                return $this->decision(
                    $licenseAccess,
                    "Feature [{$feature}] is ".($licenseAccess ? 'enabled' : 'disabled').' by license entitlement.'
                );
            }
        }

        if ($user && $school && $this->hasAuthorizationRequirements($config)) {
            $allowed = app(SchoolAuthorizationService::class)->canAny(
                $user,
                $school,
                $this->normalizedList($config['authorization_features'] ?? [])
            );

            if (! $allowed) {
                return $this->decision(false, "Feature [{$feature}] is not authorized for the current user.");
            }
        }

        return $this->decision(true, "Feature [{$feature}] is enabled.");
    }

    private function explicitSchoolAccess(string $feature, School $school, array $config): ?bool
    {
        $disabled = false;

        foreach ($this->schoolFeatureKeys($feature, $config) as $featureKey) {
            $access = $this->schoolFeatures->explicitAccess($school, $featureKey);

            if ($access === true) {
                return true;
            }

            if ($access === false) {
                $disabled = true;
            }
        }

        return $disabled ? false : null;
    }

    private function activeOverride(string $feature, School $school, array $config): ?SchoolFeatureOverride
    {
        foreach ($this->schoolFeatureKeys($feature, $config) as $featureKey) {
            $override = $school->featureOverrides()
                ->where('feature_key', $featureKey)
                ->where(function ($query) {
                    $query->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', now());
                })
                ->latest()
                ->first();

            if ($override) {
                return $override;
            }
        }

        return null;
    }

    private function explicitLicenseAccess(string $feature, School $school, array $config): ?bool
    {
        $disabled = false;
        $licenseEntitlements = app(LicenseEntitlementService::class);

        foreach ($this->schoolFeatureKeys($feature, $config) as $featureKey) {
            $access = $licenseEntitlements->explicitAccess($featureKey, $school);

            if ($access === true) {
                return true;
            }

            if ($access === false) {
                $disabled = true;
            }
        }

        return $disabled ? false : null;
    }

    private function hasAuthorizationRequirements(array $config): bool
    {
        return $this->normalizedList($config['authorization_features'] ?? []) !== [];
    }

    private function hasSuperAdminBypass(?User $user, array $config): bool
    {
        return (bool) ($config['super_admin_bypass'] ?? false)
            && (bool) $user?->hasRole('super_admin');
    }

    private function schoolFeatureKeys(string $feature, array $config): array
    {
        return array_values(array_unique(array_filter([
            $this->normalize($feature),
            ...$this->normalizedList($config['entitlement_keys'] ?? []),
        ])));
    }

    private function featureConfig(string $feature): ?array
    {
        $config = config('features.features.'.$this->normalize($feature));

        return is_array($config) ? $config : null;
    }

    private function normalizedList(mixed $values): array
    {
        return collect((array) $values)
            ->map(fn (mixed $value): string => $this->normalize((string) $value))
            ->filter()
            ->values()
            ->all();
    }

    private function normalize(string $value): string
    {
        return str($value)
            ->trim()
            ->lower()
            ->replace(['-', ' '], '_')
            ->toString();
    }

    private function decision(bool $enabled, string $reason): array
    {
        return [
            'enabled' => $enabled,
            'reason' => $reason,
        ];
    }

    private function licenseValidationEnabled(): bool
    {
        return (bool) config('sanfaani.license_validation_enabled', false);
    }
}
