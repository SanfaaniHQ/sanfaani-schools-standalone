<?php

namespace Tests\Feature\Admissions;

use App\Models\Admissions\AdmissionApplication;
use App\Models\Admissions\AdmissionChannel;
use App\Services\Admissions\AdmissionWebsiteIntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionWebsiteIntegrationTest extends TestCase
{
    use InteractsWithAdmissions;
    use RefreshDatabase;

    public function test_embed_page_renders_and_tracks_approved_source_channel(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        AdmissionChannel::create([
            'school_id' => $school->id,
            'name' => 'main-website',
            'type' => 'existing_website',
            'allowed_domain' => 'school.example',
            'is_active' => true,
        ]);

        $this->withHeaders(['Origin' => 'https://school.example'])
            ->get('/admissions/embed?channel=main-website')
            ->assertOk()
            ->assertSee('Applicant details')
            ->assertSee('main-website')
            ->assertHeader('Content-Security-Policy', "frame-ancestors 'self' https://school.example http://school.example");

        $this->withHeaders(['Origin' => 'https://evil.example'])
            ->get('/admissions/embed?channel=main-website')
            ->assertForbidden();

        $this->post('/admissions/apply', $this->admissionPayload() + ['source_channel' => 'main-website'])
            ->assertCreated();
        $application = AdmissionApplication::firstOrFail();
        $this->assertSame('main-website', $application->source_channel);

        $application->notes()->create([
            'note' => 'Internal admissions review details.',
            'visibility' => 'internal',
        ]);

        $this->withHeaders(['Origin' => 'https://school.example'])
            ->get('/admissions/embed?channel=main-website')
            ->assertOk()
            ->assertDontSee($application->application_number)
            ->assertDontSee('guardian@example.test')
            ->assertDontSee('Internal admissions review details.');
    }

    public function test_embed_can_be_disabled(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        config()->set('admissions.embed_enabled', false);

        $this->get('/admissions/embed')->assertNotFound();
    }

    public function test_public_api_is_disabled_by_default_and_requires_key_and_domain_when_enabled(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        $channel = AdmissionChannel::create([
            'school_id' => $school->id,
            'name' => 'nextjs-site',
            'type' => 'nextjs',
            'allowed_domain' => 'school.example',
            'is_active' => true,
        ]);
        $created = app(AdmissionWebsiteIntegrationService::class)
            ->createApiKey($school, 'Website key', $channel, 'school.example');

        $this->getJson('/api/public/admissions/config')->assertNotFound();

        config()->set('admissions.api_enabled', true);
        $this->getJson('/api/public/admissions/config')->assertUnauthorized();
        $this->withHeaders([
            'X-Sanfaani-Admission-Key' => 'sad_invalid',
            'Origin' => 'https://school.example',
        ])->getJson('/api/public/admissions/config')->assertUnauthorized();
        $this->withHeaders([
            'X-Sanfaani-Admission-Key' => $created['plain_key'],
            'Origin' => 'https://evil.example',
        ])->getJson('/api/public/admissions/config')->assertForbidden();

        $this->withHeaders([
            'X-Sanfaani-Admission-Key' => $created['plain_key'],
            'Origin' => 'https://school.example',
        ])->getJson('/api/public/admissions/config')
            ->assertOk()
            ->assertJsonPath('school.name', $school->name)
            ->assertJsonPath('payments.online_enabled', false)
            ->assertJsonMissingPath('applications')
            ->assertJsonMissingPath('api_keys')
            ->assertJsonMissingPath('documents.disk');

        $this->withHeaders([
            'X-Sanfaani-Admission-Key' => $created['plain_key'],
            'Origin' => 'https://school.example',
        ])->postJson('/api/public/admissions', $this->admissionPayload())
            ->assertCreated()
            ->assertJsonStructure(['application_number', 'tracking_token', 'next_step']);
    }
}
