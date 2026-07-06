<?php

namespace Tests\Feature\Admissions;

use App\Models\Admissions\AdmissionApiKey;
use App\Services\Admissions\AdmissionWebsiteIntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdmissionSecurityTest extends TestCase
{
    use InteractsWithAdmissions;
    use RefreshDatabase;

    public function test_security_middleware_hashing_and_private_routes_are_present(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        $created = app(AdmissionWebsiteIntegrationService::class)
            ->createApiKey($school, 'Hashed key');

        $stored = AdmissionApiKey::firstOrFail();
        $this->assertNotSame($created['plain_key'], $stored->getRawOriginal('key_hash'));
        $this->assertSame(hash('sha256', $created['plain_key']), $stored->getRawOriginal('key_hash'));

        $submissionMiddleware = Route::getRoutes()->getByName('admissions.store')->gatherMiddleware();
        $trackingMiddleware = Route::getRoutes()->getByName('admissions.track.submit')->gatherMiddleware();
        $adminMiddleware = Route::getRoutes()->getByName('admin.admissions.applications.index')->gatherMiddleware();
        $this->assertTrue(collect($submissionMiddleware)->contains(fn ($item) => str_contains($item, 'throttle:')));
        $this->assertTrue(collect($trackingMiddleware)->contains(fn ($item) => str_contains($item, 'throttle:')));
        $this->assertContains('auth', $adminMiddleware);
        $this->assertContains('school.context', $adminMiddleware);
    }

    public function test_required_admission_documentation_exists_and_states_architecture_and_offline_limit(): void
    {
        $files = [
            'admissions-overview.md',
            'admissions-for-schools-with-existing-website.md',
            'admissions-for-schools-without-website.md',
            'admissions-nextjs-website-integration.md',
            'admissions-offline-and-sync-notes.md',
            'admissions-security-and-privacy.md',
            'admissions-admin-workflow.md',
            'admissions-roadmap.md',
        ];

        foreach ($files as $file) {
            $this->assertFileExists(base_path('docs/standalone/'.$file));
        }

        $docs = collect($files)
            ->map(fn ($file) => file_get_contents(base_path('docs/standalone/'.$file)))
            ->implode("\n");

        $this->assertStringContainsString('source of truth', $docs);
        $this->assertStringContainsString('existing website', $docs);
        $this->assertStringContainsString('without a website', $docs);
        $this->assertStringContainsString('Next.js', $docs);
        $this->assertStringContainsString('LAN', $docs);
        $this->assertStringContainsString('Admission Bridge', $docs);
    }
}
