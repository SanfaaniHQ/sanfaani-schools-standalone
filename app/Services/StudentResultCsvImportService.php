<?php

namespace App\Services;

use App\Enums\ResultWorkflowStatus;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;

class StudentResultCsvImportService
{
    public int $createdCount = 0;

    public int $updatedCount = 0;

    public int $skippedCount = 0;

    public array $errors = [];

    public function __construct(
        private School $school,
        private SchoolClass $schoolClass,
        private AcademicSession $academicSession,
        private Term $term,
        private User $user,
        private string $resultType = 'term_result'
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
            'subject_code',
            'ca_score',
            'exam_score',
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
        $subjectCode = $this->clean($row['subject_code'] ?? null);
        $caScore = $row['ca_score'] ?? null;
        $examScore = $row['exam_score'] ?? null;
        $status = $this->clean($row['status'] ?? ResultWorkflowStatus::Draft->value) ?: ResultWorkflowStatus::Draft->value;
        $teacherRemark = $this->clean($row['teacher_remark'] ?? null);

        if (! $admissionNumber && ! $subjectCode && $this->scoreIsBlank($caScore) && $this->scoreIsBlank($examScore)) {
            $this->skippedCount++;
            return;
        }

        if (! $admissionNumber || ! $subjectCode) {
            $this->errors[] = "Row {$rowNumber}: admission_number and subject_code are required.";
            return;
        }

        if ($this->scoreIsBlank($caScore) && $this->scoreIsBlank($examScore)) {
            $this->skippedCount++;
            return;
        }

        if ($this->scoreIsBlank($caScore) || $this->scoreIsBlank($examScore)) {
            $this->errors[] = "Row {$rowNumber}: CA score and exam score must both be filled.";
            return;
        }

        if (! is_numeric($caScore) || ! is_numeric($examScore)) {
            $this->errors[] = "Row {$rowNumber}: CA score and exam score must be numbers.";
            return;
        }

        $caScore = (float) $caScore;
        $examScore = (float) $examScore;

        if ($caScore < 0 || $caScore > 40) {
            $this->errors[] = "Row {$rowNumber}: CA score must be between 0 and 40.";
            return;
        }

        if ($examScore < 0 || $examScore > 60) {
            $this->errors[] = "Row {$rowNumber}: Exam score must be between 0 and 60.";
            return;
        }

        if (! in_array($status, ResultWorkflowStatus::manualEntryValues(), true)) {
            $this->errors[] = "Row {$rowNumber}: Status must be draft or reviewed.";
            return;
        }

        if ($teacherRemark && mb_strlen($teacherRemark) > 500) {
            $this->errors[] = "Row {$rowNumber}: Teacher remark must not exceed 500 characters.";
            return;
        }

        $student = Student::where('school_id', $this->school->id)
            ->where('school_class_id', $this->schoolClass->id)
            ->where('admission_number', $admissionNumber)
            ->first();

        if (! $student) {
            $this->errors[] = "Row {$rowNumber}: Student {$admissionNumber} was not found in the selected class.";
            return;
        }

        $subject = Subject::where('school_id', $this->school->id)
            ->where(function ($query) use ($subjectCode) {
                $query->where('code', $subjectCode)
                    ->orWhere('name', $subjectCode);
            })
            ->first();

        if (! $subject) {
            $this->errors[] = "Row {$rowNumber}: Subject {$subjectCode} was not found.";
            return;
        }

        $totalScore = $caScore + $examScore;

        $grading = app(ResultGradingService::class)->calculate($this->school, $totalScore);

        $result = StudentResult::updateOrCreate(
            [
                'school_id' => $this->school->id,
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_session_id' => $this->academicSession->id,
                'term_id' => $this->term->id,
                'result_type' => $this->resultType,
            ],
            [
                'school_class_id' => $this->schoolClass->id,
                'result_type' => $this->resultType,
                'ca_score' => $caScore,
                'exam_score' => $examScore,
                'total_score' => $totalScore,
                'grade' => $grading['grade'],
                'remark' => $grading['remark'],
                'teacher_remark' => $teacherRemark,
                'status' => $status,
                'recorded_by' => $this->user->id,
            ]
        );

        if ($result->wasRecentlyCreated) {
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

    private function scoreIsBlank($value): bool
    {
        return $value === null || trim((string) $value) === '';
    }

    private function clean($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim((string) $value);
    }
}
