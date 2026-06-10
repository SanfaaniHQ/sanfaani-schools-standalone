<?php

namespace Tests\Feature\Admissions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionTrackingTest extends TestCase
{
    use InteractsWithAdmissions;
    use RefreshDatabase;

    public function test_tracking_requires_number_plus_token_or_guardian_detail(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        $result = $this->submitApplication($school);

        $this->post('/admissions/track', [
            'application_number' => $result['application']->application_number,
        ])->assertSessionHasErrors(['tracking_token', 'guardian_phone']);

        $this->post('/admissions/track', [
            'application_number' => $result['application']->application_number,
            'tracking_token' => 'wrong-token',
        ])->assertSessionHasErrors('application_number');

        $this->post('/admissions/track', [
            'application_number' => $result['application']->application_number,
            'tracking_token' => $result['tracking_token'],
        ])->assertOk()->assertSee('Verified application')->assertDontSee('08030000000');

        $this->post('/admissions/track', [
            'application_number' => $result['application']->application_number,
            'guardian_phone' => '0803 000 0000',
        ])->assertOk()->assertSee('Current status');
    }

    public function test_applicant_list_is_not_public(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        $this->submitApplication($school);

        $this->get('/admissions/applications')->assertNotFound();
        $this->get('/admin/admissions/applications')->assertRedirect('/login');
    }
}
