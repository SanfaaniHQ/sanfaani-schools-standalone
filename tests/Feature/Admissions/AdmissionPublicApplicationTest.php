<?php

namespace Tests\Feature\Admissions;

use App\Models\Admissions\AdmissionApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdmissionPublicApplicationTest extends TestCase
{
    use InteractsWithAdmissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAdmissionPermissions();
        config()->set('admissions.enabled', true);
        config()->set('admissions.public_enabled', true);
    }

    public function test_admissions_config_and_public_pages_respect_feature_flags(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);

        $this->assertTrue(config()->has('admissions.enabled'));
        $this->get('/admissions')->assertOk()->assertSee('Apply to');
        $this->get('/admissions/apply')->assertOk()->assertSee('Applicant details');

        config()->set('admissions.public_enabled', false);
        $this->get('/admissions')->assertNotFound();
    }

    public function test_public_application_generates_number_and_stores_guardian(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        $class = $this->createClass($school);

        $this->post('/admissions/apply', $this->admissionPayload($class))
            ->assertCreated()
            ->assertSee('Application received')
            ->assertSee('Tracking token');

        $application = AdmissionApplication::firstOrFail();
        $this->assertNotEmpty($application->application_number);
        $this->assertSame('submitted', $application->status);
        $this->assertDatabaseHas('admission_applicant_guardians', [
            'admission_application_id' => $application->id,
            'phone' => '08030000000',
        ]);
        $this->assertDatabaseHas('admission_status_logs', [
            'admission_application_id' => $application->id,
            'to_status' => 'submitted',
        ]);
    }

    public function test_document_upload_enforces_type_and_size_and_uses_private_disk(): void
    {
        Storage::fake('local');
        $school = $this->createSchool();
        $this->createCycle($school);
        config()->set('admissions.max_upload_mb', 1);

        $payload = $this->admissionPayload();
        $payload['documents'] = [UploadedFile::fake()->create('birth-certificate.pdf', 200, 'application/pdf')];

        $this->post('/admissions/apply', $payload)->assertCreated();
        $document = \App\Models\Admissions\AdmissionDocument::firstOrFail();
        Storage::disk('local')->assertExists($document->storage_path);
        $this->assertStringStartsWith('admissions/', $document->storage_path);

        $invalid = $this->admissionPayload();
        $invalid['documents'] = [UploadedFile::fake()->create('script.exe', 20, 'application/octet-stream')];
        $this->post('/admissions/apply', $invalid)->assertSessionHasErrors('documents.0');

        $oversized = $this->admissionPayload();
        $oversized['documents'] = [UploadedFile::fake()->create('large.pdf', 2048, 'application/pdf')];
        $this->post('/admissions/apply', $oversized)->assertSessionHasErrors('documents.0');
    }

    public function test_invalid_document_type_is_rejected(): void
    {
        Storage::fake('local');
        $school = $this->createSchool();
        $this->createCycle($school);

        $payload = $this->admissionPayload();
        $payload['documents'] = [UploadedFile::fake()->create('birth-certificate.pdf', 200, 'application/pdf')];
        $payload['document_types'] = ['shell_script'];

        $this->post('/admissions/apply', $payload)->assertSessionHasErrors('document_types.0');
        $this->assertSame(0, \App\Models\Admissions\AdmissionDocument::count());
    }

    public function test_honeypot_and_timing_fallback_blocks_bot_like_submissions_when_captcha_is_required(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        config()->set('admissions.require_captcha', true);
        config()->set('admissions.minimum_submission_seconds', 3);

        $botPayload = $this->admissionPayload() + [
            'admission_website' => 'https://spam.example',
            'admission_started_at' => now()->subSeconds(10)->timestamp,
        ];
        $this->post('/admissions/apply', $botPayload)->assertSessionHasErrors('admissions');

        $fastPayload = $this->admissionPayload() + [
            'admission_started_at' => now()->timestamp,
        ];
        $this->post('/admissions/apply', $fastPayload)->assertSessionHasErrors('admissions');

        $humanPayload = $this->admissionPayload() + [
            'admission_started_at' => now()->subSeconds(5)->timestamp,
        ];
        $this->post('/admissions/apply', $humanPayload)->assertCreated();

        $this->assertSame(1, AdmissionApplication::count());
    }

    public function test_documents_cannot_be_stored_on_public_disk(): void
    {
        Storage::fake('public');
        $school = $this->createSchool();
        $this->createCycle($school);
        config()->set('admissions.document_disk', 'public');

        $payload = $this->admissionPayload();
        $payload['documents'] = [UploadedFile::fake()->create('birth-certificate.pdf', 200, 'application/pdf')];

        $this->post('/admissions/apply', $payload)->assertSessionHasErrors('documents');
        $this->assertSame(0, \App\Models\Admissions\AdmissionDocument::count());
    }
}
