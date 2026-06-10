<?php

namespace Tests\Feature\Admissions;

use App\Models\Admissions\AdmissionNote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionTrackingTest extends TestCase
{
    use InteractsWithAdmissions;
    use RefreshDatabase;

    public function test_tracking_requires_number_plus_tracking_token_by_default(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        $result = $this->submitApplication($school);

        $this->post('/admissions/track', [
            'application_number' => $result['application']->application_number,
        ])->assertSessionHasErrors('tracking_token');

        $this->post('/admissions/track', [
            'application_number' => $result['application']->application_number,
            'tracking_token' => 'wrong-token',
        ])->assertSessionHasErrors('application_number');

        $this->post('/admissions/track', [
            'application_number' => $result['application']->application_number,
            'guardian_phone' => '0803 000 0000',
            'date_of_birth' => '2014-05-10',
        ])->assertSessionHasErrors('tracking_token');

        $this->post('/admissions/track', [
            'application_number' => $result['application']->application_number,
            'tracking_token' => $result['tracking_token'],
        ])->assertOk()->assertSee('Verified application')->assertDontSee('08030000000');
    }

    public function test_guardian_tracking_fallback_requires_date_of_birth_when_enabled(): void
    {
        config()->set('admissions.guardian_tracking_fallback_enabled', true);
        config()->set('admissions.guardian_tracking_requires_date_of_birth', true);

        $school = $this->createSchool();
        $this->createCycle($school);
        $result = $this->submitApplication($school);

        $this->post('/admissions/track', [
            'application_number' => $result['application']->application_number,
            'guardian_phone' => '0803 000 0000',
        ])->assertSessionHasErrors('date_of_birth');

        $this->post('/admissions/track', [
            'application_number' => $result['application']->application_number,
            'guardian_phone' => '0803 000 0000',
            'date_of_birth' => '2014-05-10',
        ])->assertOk()->assertSee('Current status');
    }

    public function test_public_tracking_does_not_expose_internal_admin_notes(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        $result = $this->submitApplication($school);
        $application = $result['application'];

        AdmissionNote::create([
            'admission_application_id' => $application->id,
            'note' => 'Internal scholarship review note.',
            'visibility' => 'internal',
        ]);
        AdmissionNote::create([
            'admission_application_id' => $application->id,
            'note' => 'Please bring original documents.',
            'visibility' => 'public',
        ]);

        $this->post('/admissions/track', [
            'application_number' => $application->application_number,
            'tracking_token' => $result['tracking_token'],
        ])->assertOk()
            ->assertSee('Please bring original documents.')
            ->assertDontSee('Internal scholarship review note.');
    }

    public function test_applicant_list_is_not_public(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        $this->submitApplication($school);

        $this->get('/admissions/applications')->assertNotFound();
        $this->getJson('/api/public/admissions/applications')->assertNotFound();
        $this->get('/admin/admissions/applications')->assertRedirect('/login');
    }
}
