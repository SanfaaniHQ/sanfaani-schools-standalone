<?php

namespace Tests\Feature\Admissions;

use App\Models\Admissions\AdmissionApplication;
use App\Models\Student;
use App\Services\Admissions\AdmissionConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdmissionConversionTest extends TestCase
{
    use InteractsWithAdmissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareAdmissionPermissions();
    }

    public function test_accepted_applicant_converts_once_and_preserves_application(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        $class = $this->createClass($school);
        $admin = $this->createAdmin($school);
        $application = $this->submitApplication($school, $class)['application'];
        $application = $this->acceptApplication($application, $admin);

        $student = app(AdmissionConversionService::class)->convert($application, $admin->id);
        $sameStudent = app(AdmissionConversionService::class)->convert($application->fresh(), $admin->id);

        $this->assertSame($student->id, $sameStudent->id);
        $this->assertSame(1, Student::count());
        $this->assertDatabaseHas('admission_applications', [
            'id' => $application->id,
            'converted_student_id' => $student->id,
            'status' => AdmissionApplication::STATUS_CONVERTED,
        ]);
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'school_class_id' => $class->id,
        ]);
    }

    public function test_pending_or_rejected_applicant_cannot_convert(): void
    {
        $school = $this->createSchool();
        $this->createCycle($school);
        $admin = $this->createAdmin($school);
        $application = $this->submitApplication($school)['application'];

        try {
            app(AdmissionConversionService::class)->convert($application, $admin->id);
            $this->fail('Pending application conversion should fail.');
        } catch (ValidationException) {
            $this->assertSame(0, Student::count());
        }

        app(\App\Services\Admissions\AdmissionWorkflowService::class)
            ->changeStatus($application, AdmissionApplication::STATUS_REJECTED, $admin->id, null, false);

        $this->expectException(ValidationException::class);
        app(AdmissionConversionService::class)->convert($application->fresh(), $admin->id);
    }
}
