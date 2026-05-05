<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectAssignment;
use App\Models\School;
use App\Models\Subject;
use App\Services\AuditLogService;
use App\Services\BulkCsvImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectUploadController extends Controller
{
    public function index()
    {
        return view('school.subjects.upload.index', [
            'school' => $this->currentSchoolOrFail(),
            'types' => ClassSubjectAssignment::TYPES,
        ]);
    }

    public function store(Request $request, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $request->validate([
            'subject_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $csv = new BulkCsvImportService();

        if (! $csv->read($request->file('subject_file')->getRealPath(), ['name'])) {
            return back()->withInput()->with('import_errors', $csv->errors);
        }

        $errors = $this->validateRows($school, $csv->rows);

        if ($errors !== []) {
            return back()->withInput()->with('import_errors', $errors);
        }

        DB::transaction(function () use ($school, $csv) {
            foreach ($csv->rows as $row) {
                $data = $row['data'];

                Subject::create([
                    'school_id' => $school->id,
                    'name' => $data['name'],
                    'code' => filled($data['code'] ?? null) ? strtoupper($data['code']) : null,
                    'assignment_type' => $this->normalizeType($data['assignment_type'] ?? null),
                    'is_elective' => $this->truthy($data['is_elective'] ?? null),
                    'status' => $this->normalizeStatus($data['status'] ?? null),
                ]);
            }
        });

        $auditLog->log('subjects_csv_uploaded', null, $school, metadata: [
            'created' => count($csv->rows),
        ], request: $request);

        return redirect()
            ->route('school.subjects.index')
            ->with('success', count($csv->rows).' subjects uploaded successfully.');
    }

    public function downloadTemplate()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'code', 'assignment_type', 'is_elective', 'status']);
            fputcsv($handle, ['Mathematics', 'MTH', 'core', 'no', 'active']);
            fputcsv($handle, ['Agricultural Science', 'AGR', 'elective', 'yes', 'active']);
            fclose($handle);
        }, 'subjects-upload-template.csv', ['Content-Type' => 'text/csv']);
    }

    private function validateRows(School $school, array $rows): array
    {
        $errors = [];
        $names = [];
        $codes = [];

        foreach ($rows as $row) {
            $data = $row['data'];
            $rowNumber = $row['row_number'];
            $name = trim((string) ($data['name'] ?? ''));
            $code = trim((string) ($data['code'] ?? ''));
            $rawType = strtolower(trim((string) ($data['assignment_type'] ?? '')));
            $rawStatus = strtolower(trim((string) ($data['status'] ?? '')));
            $type = $this->normalizeType($rawType);
            $status = $this->normalizeStatus($rawStatus);

            if ($name === '') {
                $errors[] = "Row {$rowNumber}: name is required.";
                continue;
            }

            if (mb_strlen($name) > 150) {
                $errors[] = "Row {$rowNumber}: name must not exceed 150 characters.";
            }

            if ($code !== '' && mb_strlen($code) > 50) {
                $errors[] = "Row {$rowNumber}: code must not exceed 50 characters.";
            }

            if ($rawType !== '' && ! in_array($rawType, ClassSubjectAssignment::TYPES, true)) {
                $errors[] = "Row {$rowNumber}: assignment_type is not supported.";
            }

            if ($rawStatus !== '' && ! in_array($rawStatus, ['active', 'inactive'], true)) {
                $errors[] = "Row {$rowNumber}: status must be active or inactive.";
            }

            $nameKey = mb_strtolower($name);

            if (isset($names[$nameKey])) {
                $errors[] = "Row {$rowNumber}: duplicate subject name in this file.";
            }

            $names[$nameKey] = true;

            if ($code !== '') {
                $codeKey = mb_strtolower($code);

                if (isset($codes[$codeKey])) {
                    $errors[] = "Row {$rowNumber}: duplicate subject code in this file.";
                }

                $codes[$codeKey] = true;
            }

            if ($school->subjects()->where('name', $name)->exists()) {
                $errors[] = "Row {$rowNumber}: subject name already exists for this school.";
            }

            if ($code !== '' && $school->subjects()->where('code', $code)->exists()) {
                $errors[] = "Row {$rowNumber}: subject code already exists for this school.";
            }
        }

        return $errors;
    }

    private function normalizeType(?string $type): string
    {
        $type = strtolower(trim((string) $type));

        return in_array($type, ClassSubjectAssignment::TYPES, true) ? $type : 'core';
    }

    private function normalizeStatus(?string $status): string
    {
        $status = strtolower(trim((string) $status));

        return in_array($status, ['active', 'inactive'], true) ? $status : 'active';
    }

    private function truthy(?string $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'y', 'elective'], true);
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(\App\Services\CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }
}
