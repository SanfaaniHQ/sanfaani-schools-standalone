<?php

namespace App\Services\Licensing;

use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LicenseActivationService
{
    public function __construct(
        private LicenseKeyHasher $hasher,
        private LicenseAuditService $audit,
    ) {}

    public function activate(array $data, ?School $school = null, ?Request $request = null): License
    {
        if (! in_array($data['license_type'], config('licensing.types', []), true)) {
            throw new RuntimeException('Unsupported license type.');
        }

        return DB::transaction(function () use ($data, $school, $request): License {
            $license = License::updateOrCreate(
                ['license_key_hash' => $this->hasher->hash($data['license_key'])],
                [
                    'school_id' => $school?->id ?? Arr::get($data, 'school_id'),
                    'license_type' => $data['license_type'],
                    'status' => $data['status'] ?? 'active',
                    'issued_to_name' => $data['issued_to_name'] ?? null,
                    'issued_to_email' => $data['issued_to_email'] ?? null,
                    'domain' => $data['domain'] ?? $request?->getHost(),
                    'allowed_domains' => $data['allowed_domains'] ?? [],
                    'features' => $data['features'] ?? [],
                    'entitlements' => $data['entitlements'] ?? [],
                    'starts_at' => $data['starts_at'] ?? null,
                    'expires_at' => $data['expires_at'] ?? null,
                    'last_validated_at' => now(),
                    'offline_grace_until' => now()->addDays((int) config('licensing.offline_grace_days', 7)),
                    'suspended_at' => ($data['status'] ?? 'active') === 'suspended' ? now() : null,
                    'metadata' => array_merge($data['metadata'] ?? [], [
                        'activated_by' => $request?->user()?->id,
                    ]),
                ]
            );

            LicenseActivation::updateOrCreate(
                [
                    'license_id' => $license->id,
                    'activation_fingerprint' => $this->fingerprint($request),
                ],
                [
                    'school_id' => $license->school_id,
                    'domain' => $request?->getHost(),
                    'ip_address' => $request?->ip(),
                    'user_agent' => $request?->userAgent(),
                    'activated_at' => now(),
                    'last_seen_at' => now(),
                    'status' => 'active',
                    'metadata' => [
                        'deployment_mode' => config('sanfaani.deployment.mode'),
                        'license_mode' => config('sanfaani.deployment.license_mode'),
                    ],
                ]
            );

            $this->audit->log('license.created', 'License record was created or updated.', $license, $school);
            $this->audit->log('license.activated', 'License was activated for this installation.', $license, $school);

            return $license->fresh() ?? $license;
        });
    }

    public function fingerprint(?Request $request = null): string
    {
        return hash('sha256', implode('|', [
            config('app.url'),
            $request?->getHost(),
            base_path(),
        ]));
    }
}
