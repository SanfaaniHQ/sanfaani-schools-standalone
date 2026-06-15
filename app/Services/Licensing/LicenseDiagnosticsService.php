<?php

namespace App\Services\Licensing;

use App\Models\License;
use App\Models\School;
use App\Services\System\DeploymentModeService;
use App\Services\System\FeatureAccessService;
use App\Support\Licensing\LicenseValidationResult;

class LicenseDiagnosticsService
{
    public function __construct(
        private DeploymentModeService $deployment,
        private FeatureAccessService $features,
        private LicenseValidationService $validation,
    ) {}

    public function supportSummary(?School $school, LicenseValidationResult $result, ?License $license): array
    {
        $entitlements = (array) ($license?->entitlements ?: []);
        $featureFlags = (array) ($license?->features ?: []);

        return [
            $this->item('Validation status', $this->label($result->status), $result->valid() ? 'pass' : 'warning'),
            $this->item('Portal mode', $this->label($this->deployment->mode()), 'info'),
            $this->item('License mode', $this->label($this->deployment->licenseMode()), 'info'),
            $this->item('Local license record', $license ? 'Present' : 'Missing', $license ? 'pass' : 'warning'),
            $this->item('Key storage', $license ? 'Hashed and masked' : 'No key stored', 'pass'),
            $this->item('Domain matching', config('licensing.require_domain_match') ? 'Required' : 'Disabled', 'info'),
            $this->item('Offline grace', $license?->offline_grace_until?->isFuture() ? 'Available' : 'Not available', $license?->offline_grace_until?->isFuture() ? 'warning' : 'info'),
            $this->item('Remote license server', (bool) config('licensing.remote_validation_enabled', false) ? 'Enabled by config' : 'Not enabled', 'info'),
            $this->item('External server URL', filled(config('licensing.server_url')) ? 'Configured, hidden' : 'Not configured', 'info'),
            $this->item('Activation records', $license ? (string) $license->activations()->count() : '0', 'info'),
            $this->item('Enabled entitlements', (string) $this->enabledCount($entitlements), $this->enabledCount($entitlements) > 0 ? 'pass' : 'info'),
            $this->item('Enabled feature flags', (string) $this->enabledCount($featureFlags), $this->enabledCount($featureFlags) > 0 ? 'pass' : 'info'),
            $this->item('Local/test safety', app()->environment(['local', 'testing']) ? 'Not hard-blocked' : 'Production checks active', 'info'),
        ];
    }

    public function entitlementRows(?School $school, ?License $license): array
    {
        $licenseValues = array_merge(
            (array) ($license?->entitlements ?: []),
            (array) ($license?->features ?: []),
        );

        $hasLicense = $license !== null;

        return collect(config('features.features', []))
            ->map(function (array $config, string $feature) use ($school, $licenseValues, $hasLicense): array {
                $keys = array_values(array_unique(array_filter([
                    $this->normalize($feature),
                    ...collect($config['entitlement_keys'] ?? [])
                        ->map(fn (mixed $key): string => $this->normalize((string) $key))
                        ->all(),
                ])));
                $explicit = $this->lookup($keys, $licenseValues);
                $enabled = $this->features->enabled($feature, $school, auth()->user());

                return [
                    'feature' => $feature,
                    'label' => $this->label($feature),
                    'category' => (string) ($config['category'] ?? 'general'),
                    'entitlement_keys' => $keys,
                    'license_value' => $explicit,
                    'license_label' => $this->licenseValueLabel($explicit, $hasLicense),
                    'access_enabled' => $enabled,
                    'access_label' => $enabled ? 'Enabled' : 'Disabled',
                    'reason' => $this->features->reason($feature, $school, auth()->user()),
                ];
            })
            ->values()
            ->all();
    }

    public function statusLine(?School $school): string
    {
        $license = $this->validation->current($school);

        if (! $license) {
            return $this->validation->requiresValidation()
                ? 'No local license found'
                : 'Validation disabled by configuration';
        }

        $days = $this->validation->daysUntilExpiry($license);

        return $days === null
            ? $this->label($license->license_type).' license, no expiry recorded'
            : max(0, $days).' day(s) until expiry';
    }

    private function lookup(array $keys, array $values): ?bool
    {
        foreach ($values as $key => $value) {
            if (! in_array($this->normalize((string) $key), $keys, true)) {
                continue;
            }

            if (is_bool($value)) {
                return $value;
            }

            if (is_array($value) && array_key_exists('enabled', $value)) {
                return (bool) $value['enabled'];
            }

            return (bool) $value;
        }

        return null;
    }

    private function enabledCount(array $values): int
    {
        return collect($values)
            ->filter(fn (mixed $value): bool => is_array($value) ? (bool) ($value['enabled'] ?? false) : (bool) $value)
            ->count();
    }

    private function licenseValueLabel(?bool $value, bool $hasLicense): string
    {
        if (! $hasLicense) {
            return 'No local license';
        }

        return match ($value) {
            true => 'Enabled by license',
            false => 'Disabled by license',
            default => 'Not specified',
        };
    }

    private function item(string $label, string $value, string $status): array
    {
        return compact('label', 'value', 'status');
    }

    private function label(string $value): string
    {
        return str($value)->replace('_', ' ')->title()->toString();
    }

    private function normalize(string $value): string
    {
        return str($value)
            ->trim()
            ->lower()
            ->replace(['-', ' '], '_')
            ->toString();
    }
}
