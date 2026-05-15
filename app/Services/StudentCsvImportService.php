<?php

namespace App\Services;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class StudentCsvImportService
{
    public int $createdCount = 0;

    public int $updatedCount = 0;

    public int $skippedCount = 0;

    public array $errors = [];

    public function __construct(
        private School $school,
        private SchoolClass $schoolClass,
        private ?int $createdBy = null
    ) {}

    public function import(string $filePath): void
    {
        $handle = fopen($filePath, 'r');

        if (! $handle) {
            $this->errors[] = 'Could not open the uploaded file.';

            return;
        }

        $headers = fgetcsv($handle);

        if (! $headers) {
            $this->errors[] = 'The CSV file is empty or has no header row.';
            fclose($handle);

            return;
        }

        $headers = $this->normalizeHeaders($headers);

        $requiredHeaders = [
            'admission_number',
            'first_name',
            'last_name',
            'gender',
            'status',
        ];

        foreach ($requiredHeaders as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                $this->errors[] = "Missing required column: {$requiredHeader}.";
                fclose($handle);

                return;
            }
        }

        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($this->rowIsEmpty($row)) {
                $this->skippedCount++;

                continue;
            }

            $data = array_combine($headers, array_pad($row, count($headers), null));

            if (! $data) {
                $this->errors[] = "Row {$rowNumber}: Could not read row data.";

                continue;
            }

            $this->processRow($data, $rowNumber);
        }

        fclose($handle);
    }

    private function processRow(array $row, int $rowNumber): void
    {
        $admissionNumber = $this->clean($row['admission_number'] ?? null);
        $firstName = $this->clean($row['first_name'] ?? null);
        $middleName = $this->clean($row['middle_name'] ?? null);
        $lastName = $this->clean($row['last_name'] ?? null);
        $gender = strtolower($this->clean($row['gender'] ?? ''));
        $dateOfBirth = $this->clean($row['date_of_birth'] ?? null);
        $guardianName = $this->clean($row['guardian_name'] ?? null);
        $guardianPhone = $this->clean($row['guardian_phone'] ?? null);
        $guardianEmail = $this->clean($row['guardian_email'] ?? null);
        $address = $this->clean($row['address'] ?? null);
        $status = strtolower($this->clean($row['status'] ?? 'active') ?: 'active');

        if (! $admissionNumber && ! $firstName && ! $lastName) {
            $this->skippedCount++;

            return;
        }

        if (! $firstName) {
            $this->errors[] = "Row {$rowNumber}: first_name is required.";

            return;
        }

        if (! $lastName) {
            $this->errors[] = "Row {$rowNumber}: last_name is required.";

            return;
        }

        if (! in_array($gender, ['male', 'female'], true)) {
            $this->errors[] = "Row {$rowNumber}: gender must be male or female.";

            return;
        }

        if (! in_array($status, ['active', 'inactive'], true)) {
            $this->errors[] = "Row {$rowNumber}: status must be active or inactive.";

            return;
        }

        if ($guardianEmail && ! filter_var($guardianEmail, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Row {$rowNumber}: guardian_email is not valid.";

            return;
        }

        if ($dateOfBirth && ! $this->validDate($dateOfBirth)) {
            $this->errors[] = "Row {$rowNumber}: date_of_birth must be in YYYY-MM-DD format.";

            return;
        }

        $student = DB::transaction(function () use ($admissionNumber, $firstName, $middleName, $lastName, $gender, $dateOfBirth, $guardianName, $guardianPhone, $guardianEmail, $address, $status) {
            $admissionNumber = $admissionNumber ?: app(AdmissionNumberGeneratorService::class)
                ->generateForSchool($this->school);

            $student = Student::firstOrNew([
                'school_id' => $this->school->id,
                'admission_number' => $admissionNumber,
            ]);
            $wasRecentlyCreated = ! $student->exists;

            if ($wasRecentlyCreated) {
                $student->school_class_id = $this->schoolClass->id;
            }

            $student->fill([
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'gender' => $gender,
                'date_of_birth' => $dateOfBirth ?: null,
                'guardian_name' => $guardianName,
                'guardian_phone' => $guardianPhone,
                'guardian_email' => $guardianEmail,
                'address' => $address,
                'status' => $status,
            ]);
            $student->save();

            if ($status === 'active') {
                app(StudentClassEnrollmentService::class)->recordPlacement(
                    $this->school,
                    $student,
                    $this->schoolClass->id,
                    createdBy: $this->createdBy,
                    source: $wasRecentlyCreated ? 'student_csv_created' : 'student_csv_updated'
                );
            }

            $student->wasRecentlyCreated = $wasRecentlyCreated;

            return $student;
        });

        if ($student->wasRecentlyCreated) {
            $this->createdCount++;
        } else {
            $this->updatedCount++;
        }
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);
            $header = strtolower(trim($header));
            $header = preg_replace('/\s+/', '_', $header);

            return $header;
        }, $headers);
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function clean($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim((string) $value);
    }

    private function validDate(string $date): bool
    {
        $parsed = date_create_from_format('Y-m-d', $date);

        return $parsed && $parsed->format('Y-m-d') === $date;
    }
}
