<?php

namespace App\Services\Licensing;

use App\Models\License;
use App\Models\School;
use App\Models\SchoolSubscription;
use App\Services\System\DeploymentModeService;
use App\Support\Licensing\LicenseValidationResult;
use InvalidArgumentException;
use Throwable;

class LicenseValidationService
{
    public function __construct(
        private DeploymentModeService $deployment,
        private LicenseAuditService $audit,
    ) {}

    public function current(?School $school = null): ?License
    {
        $query = License::query()
            ->when($school, function ($query) use ($school) {
                $query->where(function ($query) use ($school) {
                    $query->where('school_id', $school->id)
                        ->orWhereNull('school_id');
                });
            })
            ->when(! $school, fn ($query) => $query->whereNull('school_id'))
            ->latest('id');

        return $query->first();
    }

    public function validate(?School $school = null): LicenseValidationResult
    {
        return $this->evaluate($school, true);
    }

    public function isValid(?School $school = null): bool
    {
        return $this->evaluate($school, false)->valid;
    }

    public function status(?School $school = null): string
    {
        return $this->evaluate($school, false)->status;
    }

    public function requiresValidation(): bool
    {
        if (! (bool) config('sanfaani.license_validation_enabled', false)) {
            return false;
        }

        try {
            if ($this->deployment->isSaas() && $this->deployment->isSubscription()) {
                return false;
            }

            $licenseMode = $this->deployment->licenseMode();

            return ! (bool) config("licensing.allow_unlicensed_modes.{$licenseMode}", false);
        } catch (Throwable) {
            return true;
        }
    }

    public function isWithinOfflineGrace(?License $license): bool
    {
        return (bool) ($license?->offline_grace_until && $license->offline_grace_until->isFuture());
    }

    public function shouldWarnExpiring(?License $license): bool
    {
        $days = $this->daysUntilExpiry($license);

        return $days !== null && $days >= 0 && $days <= (int) config('licensing.expiry_warning_days', 30);
    }

    public function daysUntilExpiry(?License $license): ?int
    {
        if (! $license?->expires_at) {
            return null;
        }

        return now()->startOfDay()->diffInDays($license->expires_at->copy()->startOfDay(), false);
    }

    private function evaluate(?School $school, bool $log): LicenseValidationResult
    {
        if (! (bool) config('sanfaani.license_validation_enabled', false)) {
            return $this->result(true, 'validation_disabled', 'License validation is disabled by configuration.', null, $school, 'info', [], false);
        }

        try {
            if ($this->deployment->isSaas() && $this->deployment->isSubscription()) {
                return $this->validateSaasSubscription($school, $log);
            }

            $licenseMode = $this->deployment->licenseMode();
        } catch (InvalidArgumentException $exception) {
            return $this->result(false, 'invalid_mode', $exception->getMessage(), null, $school, 'critical', [], $log);
        }

        if (! $this->requiresValidation()) {
            return $this->result(true, 'validation_disabled', 'License validation is disabled by configuration.', null, $school, 'info', [], false);
        }

        $license = $this->current($school);

        if (! $license) {
            return $this->result(false, 'missing', 'No school license has been activated yet.', null, $school, 'warning', [], $log);
        }

        if ($license->status === 'suspended' || $license->suspended_at) {
            return $this->result(false, 'suspended', 'This school license is suspended. Contact Sanfaani support.', $license, $school, 'critical', [], $log);
        }

        if (! in_array($license->status, ['active', 'trial', 'demo'], true)) {
            return $this->result(false, $license->status ?: 'invalid', 'This school license is not currently usable.', $license, $school, 'error', [], $log);
        }

        if (! $this->licenseTypeMatchesMode($license, $licenseMode)) {
            return $this->result(false, 'type_mismatch', 'This license does not match the portal edition. Contact Sanfaani support.', $license, $school, 'error', [
                'license_type' => $license->license_type,
                'license_mode' => $licenseMode,
            ], $log);
        }

        if ($license->starts_at && $license->starts_at->isFuture()) {
            return $this->result(false, 'not_started', 'This school license is not active yet.', $license, $school, 'warning', [], $log);
        }

        if ($this->domainMismatch($license)) {
            return $this->result(false, 'domain_mismatch', 'This license is not valid for the current portal domain.', $license, $school, 'error', [
                'host' => request()?->getHost(),
            ], $log);
        }

        if ($this->licenseExpired($license)) {
            if ($this->isWithinOfflineGrace($license)) {
                return $this->result(true, 'offline_grace', 'The license needs renewal, but temporary offline access is still available.', $license, $school, 'warning', [], $log);
            }

            return $this->result(false, 'expired', 'This school license has expired. Contact Sanfaani support to renew it.', $license, $school, 'error', [], $log);
        }

        if ($log) {
            $license->forceFill([
                'last_validated_at' => now(),
                'offline_grace_until' => now()->addDays((int) config('licensing.offline_grace_days', 7)),
            ])->save();
        }

        return $this->result(true, 'valid', 'This license is valid for this school portal.', $license, $school, 'info', [], $log);
    }

    private function validateSaasSubscription(?School $school, bool $log): LicenseValidationResult
    {
        if (! $school) {
            return $this->result(true, 'subscription_platform', 'SaaS platform licensing is managed through subscriptions.', null, null, 'info', [], $log);
        }

        $subscription = $this->activeSubscription($school);

        return $this->result(
            (bool) $subscription,
            $subscription ? 'subscription_valid' : 'subscription_missing',
            $subscription ? 'The school has an active SaaS subscription.' : 'The school does not have an active SaaS subscription.',
            null,
            $school,
            $subscription ? 'info' : 'warning',
            ['school_id' => $school->id],
            $log
        );
    }

    private function activeSubscription(School $school): ?SchoolSubscription
    {
        return $school->subscriptions()
            ->whereIn('status', ['active', 'trial', 'grace'])
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now())
                    ->orWhere('grace_ends_at', '>=', now())
                    ->orWhere('trial_ends_at', '>=', now());
            })
            ->latest()
            ->first();
    }

    private function licenseTypeMatchesMode(License $license, string $licenseMode): bool
    {
        return $license->license_type === $licenseMode;
    }

    private function licenseExpired(License $license): bool
    {
        if ($license->license_type === DeploymentModeService::LICENSE_LIFETIME) {
            return false;
        }

        return (bool) ($license->expires_at && $license->expires_at->isPast());
    }

    private function domainMismatch(License $license): bool
    {
        if (! (bool) config('licensing.require_domain_match', true)) {
            return false;
        }

        $host = request()?->getHost();

        if (! filled($host)) {
            return false;
        }

        $domains = collect($license->allowed_domains ?: [])
            ->push($license->domain)
            ->filter()
            ->map(fn (string $domain): string => strtolower(trim($domain)))
            ->unique()
            ->values();

        if ($domains->isEmpty()) {
            return false;
        }

        $host = strtolower($host);

        return ! $domains->contains(fn (string $domain): bool => $this->domainMatches($host, $domain));
    }

    private function domainMatches(string $host, string $domain): bool
    {
        if ($host === $domain) {
            return true;
        }

        if (str_starts_with($domain, '*.')) {
            return str_ends_with($host, substr($domain, 1));
        }

        return false;
    }

    private function result(
        bool $valid,
        string $status,
        string $message,
        ?License $license,
        ?School $school,
        string $severity,
        array $context,
        bool $log,
    ): LicenseValidationResult {
        $result = new LicenseValidationResult($valid, $status, $message, $license, $severity, $context);

        if ($log) {
            $this->audit->log(
                $this->eventForStatus($status, $valid),
                $message,
                $license,
                $school,
                $severity,
                $context + ['status' => $status]
            );
        }

        return $result;
    }

    private function eventForStatus(string $status, bool $valid): string
    {
        return match ($status) {
            'expired' => 'license.expired',
            'suspended' => 'license.suspended',
            'offline_grace' => 'license.grace_started',
            'domain_mismatch' => 'license.domain_mismatch',
            default => $valid ? 'license.validated' : 'license.validation_failed',
        };
    }
}
