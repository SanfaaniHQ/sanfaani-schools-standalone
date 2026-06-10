<?php

namespace Tests\Feature\Admissions;

use App\Models\Admissions\AdmissionDocument;
use App\Models\Admissions\AdmissionPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionAdminWorkflowTest extends TestCase
{
    use InteractsWithAdmissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAdmissionPermissions();
    }

    public function test_admin_can_view_update_log_note_and_review_documents(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        $admin = $this->createAdmin($school);
        $application = $this->submitApplication($school)['application'];
        $document = AdmissionDocument::create([
            'admission_application_id' => $application->id,
            'document_type' => 'birth_certificate',
            'original_name' => 'birth.pdf',
            'storage_path' => 'admissions/test/birth.pdf',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'status' => 'pending',
        ]);
        $this->actAsAdmin($admin, $school);

        $this->get('/admin/admissions/applications')->assertOk()->assertSee($application->application_number);
        $this->post("/admin/admissions/applications/{$application->id}/status", [
            'status' => 'under_review',
            'note' => 'Review started.',
        ])->assertRedirect();
        $this->assertDatabaseHas('admission_status_logs', [
            'admission_application_id' => $application->id,
            'from_status' => 'submitted',
            'to_status' => 'under_review',
        ]);

        $this->post("/admin/admissions/applications/{$application->id}/notes", [
            'note' => 'Internal screening note.',
            'visibility' => 'internal',
        ])->assertRedirect();
        $this->assertDatabaseHas('admission_notes', ['note' => 'Internal screening note.']);

        $this->post("/admin/admissions/applications/{$application->id}/documents/{$document->id}/review", [
            'status' => 'approved',
        ])->assertRedirect();
        $this->assertDatabaseHas('admission_documents', [
            'id' => $document->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_admin_can_schedule_and_confirm_manual_payment(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        $admin = $this->createAdmin($school);
        $application = $this->submitApplication($school)['application'];
        $this->actAsAdmin($admin, $school);

        $this->post("/admin/admissions/applications/{$application->id}/interviews", [
            'type' => 'entrance_exam',
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'status' => 'scheduled',
        ])->assertRedirect();
        $this->assertDatabaseHas('admission_interviews', [
            'admission_application_id' => $application->id,
            'type' => 'entrance_exam',
        ]);

        $this->post("/admin/admissions/applications/{$application->id}/payments", [
            'amount' => 5000,
            'currency' => 'NGN',
            'reference' => 'RCPT-001',
        ])->assertRedirect();
        $payment = AdmissionPayment::firstOrFail();

        $this->post("/admin/admissions/applications/{$application->id}/payments/{$payment->id}/confirm")
            ->assertRedirect();
        $this->assertDatabaseHas('admission_payments', [
            'id' => $payment->id,
            'status' => 'confirmed',
            'confirmed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('admission_applications', [
            'id' => $application->id,
            'payment_status' => 'confirmed',
        ]);
    }
}
