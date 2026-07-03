<?php

namespace App\Services\Demo;

use App\Models\DemoRequest;
use App\Models\DemoSession;
use App\Models\License;
use App\Models\School;
use App\Models\User;
use App\Services\Licensing\LicenseValidationService;
use App\Services\Standalone\StandaloneEditionService;
use App\Services\System\DeploymentModeService;
use App\Services\System\FeatureAccessService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class DemoEnvironmentService
{
    public function __construct(
        private DeploymentModeService $deployment,
        private FeatureAccessService $features,
        private LicenseValidationService $licenses,
        private StandaloneEditionService $standalone,
        private DemoCredentialService $credentials,
        private DemoActivityService $activity,
    ) {}

    public function canAccessDemo(?School $school = null, ?User $user = null): bool
    {
        try {
            if ($this->standalone->hidesDemoSurfaces()) {
                return false;
            }

            if (! (bool) config('demo.enabled', true)) {
                return false;
            }

            if (! in_array($this->deployment->mode(), config('demo.deployment_modes', []), true)) {
                return false;
            }

            $licenseMode = $this->deployment->licenseMode();

            if ($this->licenses->requiresValidation()
                && ! in_array($licenseMode, config('demo.license_modes', []), true)) {
                return false;
            }

            if ($this->licenses->requiresValidation()
                && $licenseMode === DeploymentModeService::LICENSE_SUBSCRIPTION
                && ! $this->deployment->isSaas()) {
                return false;
            }

            if (! $this->features->enabled('demo_system', $school, $user)) {
                return false;
            }

            if (! $this->licenses->requiresValidation()) {
                return true;
            }

            $license = $this->licenses->current($school);

            return ! $license || $this->licenses->isValid($school);
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function createEnvironment(?DemoRequest $request = null, ?User $creator = null): DemoSession
    {
        if (! $this->canAccessDemo(user: $creator)) {
            throw new RuntimeException('Demo automation is not available for the current deployment context.');
        }

        if (DemoSession::active()->count() >= (int) config('demo.max_active_sessions', 25)) {
            throw new RuntimeException('The maximum number of active demo sessions has been reached.');
        }

        return DB::transaction(function () use ($request, $creator): DemoSession {
            $school = $this->createDemoSchool($request);
            $license = $this->licenseFor($school);
            $expiresAt = now()->addDays((int) config('demo.default_duration_days', 7));

            $session = DemoSession::create([
                'demo_request_id' => $request?->id,
                'school_id' => $school->id,
                'license_id' => $license?->id,
                'status' => DemoSession::STATUS_ACTIVE,
                'starts_at' => now(),
                'expires_at' => $expiresAt,
                'last_activity_at' => now(),
                'created_by' => $creator?->id,
                'metadata' => [
                    'demo_school' => true,
                    'deployment_mode' => $this->deployment->mode(),
                    'license_mode' => $this->deployment->licenseMode(),
                    'duration_days' => (int) config('demo.default_duration_days', 7),
                ],
            ]);

            $this->activity->log($session, 'demo.environment_created', 'Demo environment created.', $creator, [
                'school_id' => $school->id,
                'demo_request_id' => $request?->id,
            ]);

            $generated = $this->credentials->generateForSession($session);

            if ($request) {
                $request->forceFill([
                    'status' => DemoRequest::STATUS_ENVIRONMENT_CREATED,
                    'metadata' => array_merge($request->metadata ?? [], [
                        'demo_session_id' => $session->id,
                        'demo_school_id' => $school->id,
                    ]),
                ])->save();

                $this->credentials->emailCredentials($session->fresh(['credentials.user', 'school', 'demoRequest']), $request->email);
            }

            $this->activity->log($session, 'demo.credentials_generated', 'Role-based demo credentials generated.', context: [
                'credential_count' => $generated->count(),
            ]);

            return $session->fresh(['demoRequest', 'school', 'license', 'credentials.user', 'activities']);
        });
    }

    private function createDemoSchool(?DemoRequest $request): School
    {
        $baseName = $request?->school_name ?: 'Sanfaani Demo School';
        $name = str($baseName)->startsWith('[DEMO]') ? $baseName : '[DEMO] '.$baseName;
        $slug = $this->uniqueSchoolSlug($baseName);

        return School::create([
            'name' => $name,
            'slug' => $slug,
            'school_code' => 'DEMO-'.strtoupper(Str::random(8)),
            'email' => $request?->email,
            'phone' => $request?->phone,
            'status' => 'active',
            'subscription_status' => 'demo',
            'default_language' => config('sanfaani.default_language', 'en'),
            'supports_rtl' => false,
        ]);
    }

    private function uniqueSchoolSlug(string $name): string
    {
        $prefix = trim((string) config('demo.school_slug_prefix', 'demo-school'), '-');
        $base = $prefix.'-'.(Str::slug($name) ?: 'environment');
        $slug = $base;
        $counter = 2;

        while (School::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function licenseFor(School $school): ?License
    {
        if (! $this->licenses->requiresValidation()) {
            return null;
        }

        $license = $this->licenses->current($school);

        if ($license && in_array($this->deployment->licenseMode(), ['demo', 'trial'], true) && ! $this->licenses->isValid($school)) {
            throw new RuntimeException('Demo license is not valid for this demo environment.');
        }

        return $license;
    }
}
