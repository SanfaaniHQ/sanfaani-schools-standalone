<?php

namespace App\Services\Admissions;

use App\Models\Admissions\AdmissionApplication;
use App\Models\Student;
use App\Services\AdmissionNumberGeneratorService;
use App\Services\StudentClassEnrollmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdmissionConversionService
{
    public function __construct(
        private readonly AdmissionNumberGeneratorService $studentNumberGenerator,
        private readonly StudentClassEnrollmentService $enrollments,
        private readonly AdmissionWorkflowService $workflow
    ) {}

    public function convert(AdmissionApplication $application, ?int $convertedBy = null): Student
    {
        return DB::transaction(function () use ($application, $convertedBy) {
            $locked = AdmissionApplication::query()
                ->with(['school', 'guardians', 'requestedClass', 'convertedStudent'])
                ->whereKey($application->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->converted_student_id) {
                return $locked->convertedStudent;
            }

            if (! in_array($locked->status, [
                AdmissionApplication::STATUS_ACCEPTED,
                AdmissionApplication::STATUS_ADMITTED,
            ], true)) {
                throw ValidationException::withMessages([
                    'application' => 'Only accepted or admitted applicants can be converted.',
                ]);
            }

            if (! filled($locked->first_name) || ! filled($locked->last_name) || ! $locked->school) {
                throw ValidationException::withMessages([
                    'application' => 'Required student identity fields are missing.',
                ]);
            }

            if ($locked->requestedClass && (int) $locked->requestedClass->school_id !== (int) $locked->school_id) {
                throw ValidationException::withMessages([
                    'application' => 'The requested class does not belong to this school.',
                ]);
            }

            $guardian = $locked->guardians->first();
            $student = Student::create([
                'school_id' => $locked->school_id,
                'school_class_id' => $locked->requested_class_id,
                'admission_number' => $this->studentNumberGenerator->generateForSchool($locked->school),
                'first_name' => $locked->first_name,
                'middle_name' => $locked->other_names,
                'last_name' => $locked->last_name,
                'gender' => $locked->gender,
                'date_of_birth' => $locked->date_of_birth,
                'guardian_name' => $guardian?->name,
                'guardian_phone' => $guardian?->phone,
                'guardian_email' => $guardian?->email,
                'address' => $guardian?->address,
                'status' => 'active',
            ]);

            $this->enrollments->recordPlacement(
                $locked->school,
                $student,
                $locked->requested_class_id,
                createdBy: $convertedBy,
                source: 'admission_conversion'
            );

            $locked->update(['converted_student_id' => $student->id]);
            $this->workflow->changeStatus(
                $locked,
                AdmissionApplication::STATUS_CONVERTED,
                $convertedBy,
                'Applicant converted to student record.',
                false
            );

            return $student;
        });
    }
}
