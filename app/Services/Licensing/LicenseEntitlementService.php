<?php

namespace App\Services\Licensing;

use App\Models\School;

class LicenseEntitlementService
{
    public function __construct(
        private LicenseValidationService $validation,
        private LicenseAuditService $audit,
    ) {}

    public function hasEntitlement(string $feature, ?School $school = null): bool
    {
        return $this->explicitAccess($feature, $school) === true;
    }

    public function entitlements(?School $school = null): array
    {
        if (! (bool) config('sanfaani.license_validation_enabled', false)) {
            return [];
        }

        $license = $this->validLicense($school);

        return $license ? (array) ($license->entitlements ?: []) : [];
    }

    public function features(?School $school = null): array
    {
        if (! (bool) config('sanfaani.license_validation_enabled', false)) {
            return [];
        }

        $license = $this->validLicense($school);

        return $license ? (array) ($license->features ?: []) : [];
    }

    public function explicitAccess(string $feature, ?School $school = null): ?bool
    {
        if (! (bool) config('sanfaani.license_validation_enabled', false)) {
            return null;
        }

        $feature = $this->normalize($feature);
        $license = $this->validLicense($school);

        if (! $license) {
            return null;
        }

        $value = $this->lookup($feature, $this->entitlements($school));

        if ($value === null) {
            $value = $this->lookup($feature, $this->features($school));
        }

        if ($value === null) {
            return null;
        }

        if ((bool) config('licensing.audit_entitlement_checks', false)) {
            $this->audit->log('license.entitlement_checked', "License entitlement [{$feature}] was checked.", $license, $school, 'info', [
                'feature' => $feature,
                'allowed' => $value,
            ]);
        }

        return $value;
    }

    private function validLicense(?School $school)
    {
        if (! $this->validation->isValid($school)) {
            return null;
        }

        return $this->validation->current($school);
    }

    private function lookup(string $feature, array $values): ?bool
    {
        foreach ($values as $key => $value) {
            if ($this->normalize((string) $key) !== $feature) {
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

    private function normalize(string $value): string
    {
        return str($value)
            ->trim()
            ->lower()
            ->replace(['-', ' '], '_')
            ->toString();
    }
}
