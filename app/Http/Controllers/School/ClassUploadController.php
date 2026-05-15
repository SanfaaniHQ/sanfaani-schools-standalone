<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolClass;
use App\Services\AuditLogService;
use App\Services\BulkCsvImportService;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassUploadController extends Controller
{
    public function index()
    {
        return view('school.classes.upload.index', [
            'school' => $this->currentSchoolOrFail(),
        ]);
    }

    public function store(Request $request, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $request->validate([
            'class_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $csv = new BulkCsvImportService;

        if (! $csv->read($request->file('class_file')->getRealPath(), ['name'])) {
            return back()->withInput()->with('import_errors', $csv->errors);
        }

        $errors = $this->validateRows($school, $csv->rows);

        if ($errors !== []) {
            return back()->withInput()->with('import_errors', $errors);
        }

        DB::transaction(function () use ($school, $csv) {
            foreach ($csv->rows as $row) {
                $data = $row['data'];

                SchoolClass::create([
                    'school_id' => $school->id,
                    'name' => $data['name'],
                    'code' => $data['code'] ?? null,
                    'status' => $this->normalizeStatus($data['status'] ?? null),
                ]);
            }
        });

        $auditLog->log('classes_csv_uploaded', null, $school, metadata: [
            'created' => count($csv->rows),
        ], request: $request);

        return redirect()
            ->route('school.classes.index')
            ->with('success', count($csv->rows).' classes uploaded successfully.');
    }

    public function downloadTemplate()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'code', 'status']);
            fputcsv($handle, ['JSS 1', 'JSS1', 'active']);
            fclose($handle);
        }, 'classes-upload-template.csv', ['Content-Type' => 'text/csv']);
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
            $rawStatus = strtolower(trim((string) ($data['status'] ?? '')));
            $status = $this->normalizeStatus($rawStatus);

            if ($name === '') {
                $errors[] = "Row {$rowNumber}: name is required.";

                continue;
            }

            if (mb_strlen($name) > 100) {
                $errors[] = "Row {$rowNumber}: name must not exceed 100 characters.";
            }

            if ($code !== '' && mb_strlen($code) > 50) {
                $errors[] = "Row {$rowNumber}: code must not exceed 50 characters.";
            }

            if ($rawStatus !== '' && ! in_array($rawStatus, ['active', 'inactive'], true)) {
                $errors[] = "Row {$rowNumber}: status must be active or inactive.";
            }

            $nameKey = mb_strtolower($name);

            if (isset($names[$nameKey])) {
                $errors[] = "Row {$rowNumber}: duplicate class name in this file.";
            }

            $names[$nameKey] = true;

            if ($code !== '') {
                $codeKey = mb_strtolower($code);

                if (isset($codes[$codeKey])) {
                    $errors[] = "Row {$rowNumber}: duplicate class code in this file.";
                }

                $codes[$codeKey] = true;
            }

            if ($school->schoolClasses()->where('name', $name)->exists()) {
                $errors[] = "Row {$rowNumber}: class name already exists for this school.";
            }

            if ($code !== '' && $school->schoolClasses()->where('code', $code)->exists()) {
                $errors[] = "Row {$rowNumber}: class code already exists for this school.";
            }
        }

        return $errors;
    }

    private function normalizeStatus(?string $status): string
    {
        $status = strtolower(trim((string) $status));

        return in_array($status, ['active', 'inactive'], true) ? $status : 'active';
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }
}
