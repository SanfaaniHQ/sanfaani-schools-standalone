<?php

namespace App\Services\Admissions;

use App\Models\Admissions\AdmissionApiKey;
use App\Models\Admissions\AdmissionChannel;
use App\Models\Admissions\AdmissionCycle;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdmissionWebsiteIntegrationService
{
    public function publicEnabled(): bool
    {
        return (bool) config('admissions.enabled') && (bool) config('admissions.public_enabled');
    }

    public function resolvePortalSchool(): ?School
    {
        return School::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->first();
    }

    public function currentCycle(School $school): ?AdmissionCycle
    {
        return $school->admissionCycles()
            ->acceptingApplications()
            ->latest('starts_at')
            ->latest('id')
            ->first();
    }

    public function sourceChannel(School $school, ?string $requested, string $fallback = 'portal'): string
    {
        $requested = trim((string) $requested);

        if ($requested === '') {
            return $fallback;
        }

        $channel = $school->admissionChannels()
            ->where('is_active', true)
            ->where('name', $requested)
            ->first();

        return $channel?->name ?: $fallback;
    }

    public function publicConfig(School $school, ?AdmissionCycle $cycle): array
    {
        return [
            'school' => [
                'name' => $school->name,
                'admissions_url' => route('admissions.apply'),
                'tracking_url' => route('admissions.track'),
            ],
            'accepting_applications' => (bool) $cycle,
            'cycle' => $cycle ? [
                'name' => $cycle->name,
                'starts_at' => $cycle->starts_at?->toIso8601String(),
                'ends_at' => $cycle->ends_at?->toIso8601String(),
                'requirements' => data_get($cycle->settings, 'requirements', []),
            ] : null,
            'documents' => [
                'enabled' => (bool) config('admissions.allow_document_uploads'),
                'max_upload_mb' => (int) config('admissions.max_upload_mb', 5),
                'allowed_types' => config('admissions.allowed_document_mimes', []),
            ],
            'payments' => [
                'online_enabled' => false,
                'manual_enabled' => (bool) config('admissions.manual_payment_enabled'),
            ],
        ];
    }

    public function authenticateApiRequest(Request $request): AdmissionApiKey
    {
        $plainKey = trim((string) $request->header('X-Sanfaani-Admission-Key'));

        abort_if($plainKey === '', 401, 'Admission API key is required.');

        $apiKey = AdmissionApiKey::query()
            ->with(['school', 'channel'])
            ->where('key_hash', hash('sha256', $plainKey))
            ->where('is_active', true)
            ->first();

        abort_unless($apiKey && $apiKey->school?->status === 'active', 401, 'Admission API key is invalid.');

        $allowedDomain = $apiKey->allowed_domain ?: $apiKey->channel?->allowed_domain;
        if ($allowedDomain) {
            abort_unless(
                $this->domainAllowed($request, $allowedDomain),
                403,
                'This source domain is not allowed.'
            );
        }

        $apiKey->forceFill(['last_used_at' => now()])->save();

        return $apiKey;
    }

    public function createApiKey(
        School $school,
        string $name,
        ?AdmissionChannel $channel = null,
        ?string $allowedDomain = null
    ): array {
        $plainKey = 'sad_'.Str::random(48);
        $key = AdmissionApiKey::create([
            'school_id' => $school->id,
            'channel_id' => $channel?->id,
            'name' => $name,
            'key_hash' => hash('sha256', $plainKey),
            'allowed_domain' => $allowedDomain,
            'is_active' => true,
        ]);

        return ['model' => $key, 'plain_key' => $plainKey];
    }

    private function domainAllowed(Request $request, string $allowedDomain): bool
    {
        $source = $request->headers->get('Origin') ?: $request->headers->get('Referer');
        if (! $source) {
            return false;
        }

        $host = Str::lower((string) parse_url($source, PHP_URL_HOST));
        $allowed = Str::lower(trim($allowedDomain));
        $allowed = preg_replace('#^https?://#', '', $allowed);
        $allowed = trim((string) $allowed, " /\t\n\r\0\x0B");

        if (Str::startsWith($allowed, '*.')) {
            $base = Str::after($allowed, '*.');

            return $host !== $base && Str::endsWith($host, '.'.$base);
        }

        return hash_equals($allowed, $host);
    }
}
