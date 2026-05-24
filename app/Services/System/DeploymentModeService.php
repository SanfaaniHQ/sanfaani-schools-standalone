<?php

namespace App\Services\System;

use InvalidArgumentException;

class DeploymentModeService
{
    public const MODE_SAAS = 'saas';

    public const MODE_SINGLE_SCHOOL = 'single_school';

    public const MODE_MANAGED = 'managed';

    public const LICENSE_SUBSCRIPTION = 'subscription';

    public const LICENSE_ANNUAL = 'annual';

    public const LICENSE_LIFETIME = 'lifetime';

    public const LICENSE_MANAGED_CONTRACT = 'managed_contract';

    public const LICENSE_WHITE_LABEL = 'white_label';

    public const LICENSE_TRIAL = 'trial';

    public const LICENSE_DEMO = 'demo';

    public const DEPLOYMENT_MODES = [
        self::MODE_SAAS,
        self::MODE_SINGLE_SCHOOL,
        self::MODE_MANAGED,
    ];

    public const LICENSE_MODES = [
        self::LICENSE_SUBSCRIPTION,
        self::LICENSE_ANNUAL,
        self::LICENSE_LIFETIME,
        self::LICENSE_MANAGED_CONTRACT,
        self::LICENSE_WHITE_LABEL,
        self::LICENSE_TRIAL,
        self::LICENSE_DEMO,
    ];

    public function mode(): string
    {
        return $this->validatedValue(
            value: config('sanfaani.deployment.mode', self::MODE_SAAS),
            allowed: self::DEPLOYMENT_MODES,
            default: self::MODE_SAAS,
            label: 'deployment mode'
        );
    }

    public function licenseMode(): string
    {
        return $this->validatedValue(
            value: config('sanfaani.deployment.license_mode', self::LICENSE_SUBSCRIPTION),
            allowed: self::LICENSE_MODES,
            default: self::LICENSE_SUBSCRIPTION,
            label: 'license mode'
        );
    }

    public function brandMode(): string
    {
        return $this->normalize(config('sanfaani.deployment.brand_mode', 'default')) ?: 'default';
    }

    public function isSaas(): bool
    {
        return $this->mode() === self::MODE_SAAS;
    }

    public function isSingleSchool(): bool
    {
        return $this->mode() === self::MODE_SINGLE_SCHOOL;
    }

    public function isManaged(): bool
    {
        return $this->mode() === self::MODE_MANAGED;
    }

    public function isSubscription(): bool
    {
        return $this->licenseMode() === self::LICENSE_SUBSCRIPTION;
    }

    public function isAnnual(): bool
    {
        return $this->licenseMode() === self::LICENSE_ANNUAL;
    }

    public function isLifetime(): bool
    {
        return $this->licenseMode() === self::LICENSE_LIFETIME;
    }

    public function isTrial(): bool
    {
        return $this->licenseMode() === self::LICENSE_TRIAL;
    }

    public function isDemo(): bool
    {
        return $this->licenseMode() === self::LICENSE_DEMO;
    }

    public function updatesEnabled(): bool
    {
        return (bool) config('sanfaani.deployment.updates_enabled', true);
    }

    public function demoEnabled(): bool
    {
        return (bool) config('sanfaani.deployment.demo_enabled', false);
    }

    public function requiresLicense(): bool
    {
        return ! $this->isDemo();
    }

    public function allowsMultiSchool(): bool
    {
        return $this->isSaas() || $this->isManaged();
    }

    public function allowsInstaller(): bool
    {
        return $this->isSingleSchool() || $this->isManaged();
    }

    public function allowsCentralBilling(): bool
    {
        return $this->isSaas();
    }

    public function allowsManagedTools(): bool
    {
        return $this->isManaged();
    }

    private function validatedValue(mixed $value, array $allowed, string $default, string $label): string
    {
        $normalized = $this->normalize($value) ?: $default;

        if (in_array($normalized, $allowed, true)) {
            return $normalized;
        }

        throw new InvalidArgumentException(sprintf(
            'Unsupported Sanfaani %s [%s]. Supported values: %s.',
            $label,
            (string) $value,
            implode(', ', $allowed)
        ));
    }

    private function normalize(mixed $value): string
    {
        return str((string) $value)
            ->trim()
            ->lower()
            ->replace(['-', ' '], '_')
            ->toString();
    }
}
