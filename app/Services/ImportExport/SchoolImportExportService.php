<?php

namespace App\Services\ImportExport;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendanceRecord;
use App\Models\StudentFeeInvoice;
use App\Models\StudentFeePayment;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\StudentClassEnrollmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SchoolImportExportService
{
    public const STUDENT_IMPORT_HEADERS = [
        'admission_number',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'class',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'status',
    ];

    public const STUDENT_IMPORT_REQUIRED_HEADERS = [
        'admission_number',
        'first_name',
        'last_name',
        'gender',
        'class',
    ];

    private const MAX_IMPORT_ROWS = 200;

    private const STUDENT_STATUSES = [
        'active',
        'inactive',
        'graduated',
        'transferred',
        'withdrawn',
    ];

    public function __construct(
        private AuditLogService $auditLog,
        private StudentClassEnrollmentService $enrollments
    ) {}

    public function studentTemplate(School $school, User $actor, Request $request): StreamedResponse
    {
        $fileName = $this->fileName($school, 'student-import-template');

        $this->auditLog->log('import_export_student_template_downloaded', null, $school, metadata: [
            'tool' => 'import_export',
            'module' => 'students',
            'filename' => $fileName,
            'actor_id' => $actor->id,
        ], request: $request);

        return $this->csvResponse($fileName, function ($handle): void {
            fputcsv($handle, self::STUDENT_IMPORT_HEADERS);
            fputcsv($handle, [
                'SCH/2026/001',
                'Aisha',
                'Fatimah',
                'Bello',
                'female',
                '2015-09-12',
                'JSS 1',
                'Mr Bello',
                '08012345678',
                'guardian@example.com',
                'active',
            ]);
        });
    }

    public function exportStudents(School $school, array $filters, User $actor, Request $request): StreamedResponse
    {
        $query = Student::query()
            ->where('school_id', $school->id)
            ->with('schoolClass')
            ->when(filled($filters['school_class_id'] ?? null), fn (Builder $query) => $query->where('school_class_id', (int) $filters['school_class_id']))
            ->when(filled($filters['status'] ?? null), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(filled($filters['search'] ?? null), function (Builder $query) use ($filters): void {
                $search = trim((string) $filters['search']);

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('admission_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name');

        $rowCount = (clone $query)->count();
        $fileName = $this->fileName($school, 'students-export');

        $this->auditLog->log('import_export_students_exported', null, $school, metadata: [
            'tool' => 'import_export',
            'module' => 'students',
            'filters' => $this->safeFilters($filters),
            'row_count' => $rowCount,
            'filename' => $fileName,
            'actor_id' => $actor->id,
        ], request: $request);

        return $this->csvResponse($fileName, function ($handle) use ($query): void {
            fputcsv($handle, [
                'admission_number',
                'first_name',
                'middle_name',
                'last_name',
                'gender',
                'date_of_birth',
                'class',
                'guardian_name',
                'guardian_phone',
                'guardian_email',
                'status',
            ]);

            $query->chunk(500, function ($students) use ($handle): void {
                foreach ($students as $student) {
                    fputcsv($handle, [
                        $student->admission_number,
                        $student->first_name,
                        $student->middle_name,
                        $student->last_name,
                        $student->gender,
                        $student->date_of_birth?->toDateString(),
                        $this->classLabel($student->schoolClass),
                        $student->guardian_name,
                        $student->guardian_phone,
                        $student->guardian_email,
                        $student->status,
                    ]);
                }
            });
        });
    }

    public function exportAttendance(School $school, array $filters, User $actor, Request $request): StreamedResponse
    {
        $query = StudentAttendanceRecord::query()
            ->where('school_id', $school->id)
            ->with('schoolClass')
            ->when(filled($filters['date'] ?? null), fn (Builder $query) => $query->whereDate('attendance_date', $filters['date']))
            ->when(filled($filters['date_from'] ?? null), fn (Builder $query) => $query->whereDate('attendance_date', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn (Builder $query) => $query->whereDate('attendance_date', '<=', $filters['date_to']))
            ->when(filled($filters['school_class_id'] ?? null), fn (Builder $query) => $query->where('school_class_id', (int) $filters['school_class_id']))
            ->when(filled($filters['status'] ?? null), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(filled($filters['academic_session_id'] ?? null), fn (Builder $query) => $query->where('academic_session_id', (int) $filters['academic_session_id']))
            ->when(filled($filters['term_id'] ?? null), fn (Builder $query) => $query->where('term_id', (int) $filters['term_id']));

        if (blank($filters['date'] ?? null) && blank($filters['date_from'] ?? null) && blank($filters['date_to'] ?? null)) {
            $query->whereDate('attendance_date', today()->toDateString());
            $filters['date'] = today()->toDateString();
        }

        $records = $query
            ->orderBy('attendance_date')
            ->orderBy('school_class_id')
            ->get();
        $summaryRows = $this->attendanceSummaryRows($records);
        $fileName = $this->fileName($school, 'attendance-summary-export');

        $this->auditLog->log('import_export_attendance_exported', null, $school, metadata: [
            'tool' => 'import_export',
            'module' => 'attendance',
            'filters' => $this->safeFilters($filters),
            'row_count' => count($summaryRows),
            'filename' => $fileName,
            'actor_id' => $actor->id,
        ], request: $request);

        return $this->csvResponse($fileName, function ($handle) use ($summaryRows): void {
            fputcsv($handle, [
                'attendance_date',
                'class',
                'present',
                'absent',
                'late',
                'excused',
                'total',
            ]);

            foreach ($summaryRows as $row) {
                fputcsv($handle, $row);
            }
        });
    }

    public function exportFinance(School $school, array $filters, User $actor, Request $request): StreamedResponse
    {
        $invoiceQuery = StudentFeeInvoice::query()
            ->where('school_id', $school->id)
            ->with(['student', 'schoolClass', 'academicSession', 'term'])
            ->when(filled($filters['date_from'] ?? null), fn (Builder $query) => $query->whereDate('issued_at', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn (Builder $query) => $query->whereDate('issued_at', '<=', $filters['date_to']))
            ->when(filled($filters['school_class_id'] ?? null), fn (Builder $query) => $query->where('school_class_id', (int) $filters['school_class_id']))
            ->when(filled($filters['academic_session_id'] ?? null), fn (Builder $query) => $query->where('academic_session_id', (int) $filters['academic_session_id']))
            ->when(filled($filters['term_id'] ?? null), fn (Builder $query) => $query->where('term_id', (int) $filters['term_id']))
            ->when(filled($filters['invoice_status'] ?? null), fn (Builder $query) => $query->where('status', $filters['invoice_status']))
            ->when(filled($filters['payment_method'] ?? null), fn (Builder $query) => $query->whereHas('payments', fn (Builder $paymentQuery) => $paymentQuery->where('method', $filters['payment_method'])));

        $paymentQuery = StudentFeePayment::query()
            ->where('school_id', $school->id)
            ->with(['student', 'invoice.schoolClass', 'invoice.academicSession', 'invoice.term'])
            ->when(filled($filters['date_from'] ?? null), fn (Builder $query) => $query->whereDate('payment_date', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn (Builder $query) => $query->whereDate('payment_date', '<=', $filters['date_to']))
            ->when(filled($filters['payment_method'] ?? null), fn (Builder $query) => $query->where('method', $filters['payment_method']));

        if ($this->hasFinanceInvoiceFilters($filters)) {
            $paymentQuery->whereHas('invoice', function (Builder $query) use ($filters): void {
                $query
                    ->when(filled($filters['school_class_id'] ?? null), fn (Builder $query) => $query->where('school_class_id', (int) $filters['school_class_id']))
                    ->when(filled($filters['academic_session_id'] ?? null), fn (Builder $query) => $query->where('academic_session_id', (int) $filters['academic_session_id']))
                    ->when(filled($filters['term_id'] ?? null), fn (Builder $query) => $query->where('term_id', (int) $filters['term_id']))
                    ->when(filled($filters['invoice_status'] ?? null), fn (Builder $query) => $query->where('status', $filters['invoice_status']));
            });
        }

        $rowCount = (clone $invoiceQuery)->count() + (clone $paymentQuery)->count();
        $fileName = $this->fileName($school, 'finance-summary-export');

        $this->auditLog->log('import_export_finance_exported', null, $school, metadata: [
            'tool' => 'import_export',
            'module' => 'finance',
            'filters' => $this->safeFilters($filters),
            'row_count' => $rowCount,
            'filename' => $fileName,
            'actor_id' => $actor->id,
        ], request: $request);

        return $this->csvResponse($fileName, function ($handle) use ($invoiceQuery, $paymentQuery): void {
            fputcsv($handle, [
                'record_type',
                'invoice_number',
                'student_admission_number',
                'student_name',
                'class',
                'session',
                'term',
                'status',
                'total_amount',
                'discount_amount',
                'paid_amount',
                'balance_amount',
                'due_date',
                'issued_at',
                'payment_date',
                'payment_method',
                'payment_amount',
            ]);

            $invoiceQuery->orderBy('id')->chunk(500, function ($invoices) use ($handle): void {
                foreach ($invoices as $invoice) {
                    fputcsv($handle, $this->invoiceCsvRow($invoice));
                }
            });

            $paymentQuery->orderBy('id')->chunk(500, function ($payments) use ($handle): void {
                foreach ($payments as $payment) {
                    fputcsv($handle, $this->paymentCsvRow($payment));
                }
            });
        });
    }

    public function previewStudentImport(School $school, UploadedFile $file, User $actor, Request $request): array
    {
        $result = $this->parseStudentCsv($school, $file->getRealPath());
        $action = $result['error_count'] > 0
            ? 'import_export_students_import_validation_failed'
            : 'import_export_students_import_previewed';

        $this->auditLog->log($action, null, $school, metadata: [
            'tool' => 'import_export',
            'module' => 'students',
            'filename' => $this->sanitizeOriginalFilename($file->getClientOriginalName()),
            'row_count' => $result['row_count'],
            'valid_count' => $result['valid_count'],
            'error_count' => $result['error_count'],
            'actor_id' => $actor->id,
        ], request: $request);

        return $result;
    }

    public function commitStudentImport(School $school, array $rows, User $actor, Request $request): array
    {
        $rows = collect($rows)->values();
        $errors = $this->commitValidationErrors($school, $rows);

        if ($errors !== []) {
            $this->auditLog->log('import_export_students_import_validation_failed', null, $school, metadata: [
                'tool' => 'import_export',
                'module' => 'students',
                'row_count' => $rows->count(),
                'error_count' => count($errors),
                'actor_id' => $actor->id,
            ], request: $request);

            return [
                'created' => 0,
                'errors' => $errors,
            ];
        }

        $created = DB::transaction(function () use ($school, $rows, $actor): int {
            $count = 0;

            foreach ($rows as $row) {
                $student = Student::create([
                    'school_id' => $school->id,
                    'school_class_id' => (int) $row['school_class_id'],
                    'admission_number' => $row['admission_number'],
                    'first_name' => $row['first_name'],
                    'middle_name' => $row['middle_name'] ?: null,
                    'last_name' => $row['last_name'],
                    'gender' => $row['gender'],
                    'date_of_birth' => $row['date_of_birth'] ?: null,
                    'guardian_name' => $row['guardian_name'] ?: null,
                    'guardian_phone' => $row['guardian_phone'] ?: null,
                    'guardian_email' => $row['guardian_email'] ?: null,
                    'status' => $row['status'],
                ]);

                $this->enrollments->recordPlacement(
                    $school,
                    $student,
                    (int) $row['school_class_id'],
                    createdBy: $actor->id,
                    source: 'student_import_export_committed'
                );

                $count++;
            }

            return $count;
        });

        $this->auditLog->log('import_export_students_import_committed', null, $school, metadata: [
            'tool' => 'import_export',
            'module' => 'students',
            'row_count' => $rows->count(),
            'created' => $created,
            'actor_id' => $actor->id,
        ], request: $request);

        return [
            'created' => $created,
            'errors' => [],
        ];
    }

    private function parseStudentCsv(School $school, string $path): array
    {
        $handle = fopen($path, 'r');

        if (! $handle) {
            return $this->previewResult([], ['Could not open the uploaded CSV file.']);
        }

        $headers = fgetcsv($handle);

        if (! $headers) {
            fclose($handle);

            return $this->previewResult([], ['The CSV file is empty or missing a header row.']);
        }

        $headers = $this->normalizeHeaders($headers);
        $errors = $this->missingHeaderErrors($headers);
        $rows = [];
        $rowNumber = 1;
        $dataRowCount = 0;
        $admissionNumbers = [];
        $classes = $this->classLookup($school);

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $dataRowCount++;

            if ($dataRowCount > self::MAX_IMPORT_ROWS) {
                $errors[] = 'The CSV file exceeds the '.self::MAX_IMPORT_ROWS.' row import limit.';
                break;
            }

            $values = array_slice(array_pad($row, count($headers), null), 0, count($headers));
            $data = array_map(fn ($value): string => trim((string) $value), array_combine($headers, $values));
            $validated = $this->validateStudentImportRow($school, $classes, $data, $rowNumber, $admissionNumbers);
            $errors = [...$errors, ...$validated['errors']];

            if ($validated['row']) {
                $rows[] = $validated['row'];
                $admissionNumbers[mb_strtolower($validated['row']['admission_number'])] = $rowNumber;
            }
        }

        fclose($handle);

        return $this->previewResult($rows, $errors, $dataRowCount);
    }

    private function validateStudentImportRow(
        School $school,
        Collection $classes,
        array $data,
        int $rowNumber,
        array $seenAdmissionNumbers
    ): array {
        $errors = [];

        foreach (self::STUDENT_IMPORT_REQUIRED_HEADERS as $field) {
            if (blank($data[$field] ?? null)) {
                $errors[] = "Row {$rowNumber}: {$field} is required.";
            }
        }

        $admissionNumber = $this->clean($data['admission_number'] ?? '');
        $firstName = $this->clean($data['first_name'] ?? '');
        $middleName = $this->clean($data['middle_name'] ?? '');
        $lastName = $this->clean($data['last_name'] ?? '');
        $gender = mb_strtolower($this->clean($data['gender'] ?? ''));
        $dateOfBirth = $this->clean($data['date_of_birth'] ?? '');
        $classValue = $this->clean($data['class'] ?? '');
        $guardianName = $this->clean($data['guardian_name'] ?? '');
        $guardianPhone = $this->clean($data['guardian_phone'] ?? '');
        $guardianEmail = $this->clean($data['guardian_email'] ?? '');
        $status = mb_strtolower($this->clean($data['status'] ?? 'active') ?: 'active');
        $class = $classes->get($this->normalizeKey($classValue));

        if ($admissionNumber !== '' && mb_strlen($admissionNumber) > 100) {
            $errors[] = "Row {$rowNumber}: admission_number must not exceed 100 characters.";
        }

        if ($firstName !== '' && mb_strlen($firstName) > 100) {
            $errors[] = "Row {$rowNumber}: first_name must not exceed 100 characters.";
        }

        if ($middleName !== '' && mb_strlen($middleName) > 100) {
            $errors[] = "Row {$rowNumber}: middle_name must not exceed 100 characters.";
        }

        if ($lastName !== '' && mb_strlen($lastName) > 100) {
            $errors[] = "Row {$rowNumber}: last_name must not exceed 100 characters.";
        }

        if ($gender !== '' && ! in_array($gender, ['male', 'female'], true)) {
            $errors[] = "Row {$rowNumber}: gender must be male or female.";
        }

        if ($status !== '' && ! in_array($status, self::STUDENT_STATUSES, true)) {
            $errors[] = "Row {$rowNumber}: status is invalid.";
        }

        if ($dateOfBirth !== '' && ! $this->validDate($dateOfBirth)) {
            $errors[] = "Row {$rowNumber}: date_of_birth must be in YYYY-MM-DD format.";
        }

        if ($guardianEmail !== '' && ! filter_var($guardianEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Row {$rowNumber}: guardian_email is not valid.";
        }

        if ($classValue !== '' && ! $class) {
            $errors[] = "Row {$rowNumber}: class was not found in this school.";
        }

        $admissionKey = mb_strtolower($admissionNumber);

        if ($admissionNumber !== '' && isset($seenAdmissionNumbers[$admissionKey])) {
            $errors[] = "Row {$rowNumber}: duplicate admission_number in this file.";
        }

        if ($admissionNumber !== '' && Student::query()
            ->where('school_id', $school->id)
            ->where('admission_number', $admissionNumber)
            ->exists()) {
            $errors[] = "Row {$rowNumber}: admission_number already exists in this school.";
        }

        if ($errors !== []) {
            return [
                'row' => null,
                'errors' => $errors,
            ];
        }

        return [
            'row' => [
                'row_number' => $rowNumber,
                'school_class_id' => $class->id,
                'class' => $this->classLabel($class),
                'admission_number' => $admissionNumber,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'gender' => $gender,
                'date_of_birth' => $dateOfBirth,
                'guardian_name' => $guardianName,
                'guardian_phone' => $guardianPhone,
                'guardian_email' => $guardianEmail,
                'status' => $status,
            ],
            'errors' => [],
        ];
    }

    private function commitValidationErrors(School $school, Collection $rows): array
    {
        $errors = [];
        $seenAdmissionNumbers = [];

        foreach ($rows as $row) {
            $admissionKey = mb_strtolower((string) $row['admission_number']);

            if (isset($seenAdmissionNumbers[$admissionKey])) {
                $errors[] = "Row {$row['row_number']}: duplicate admission_number in this import.";
            }

            $seenAdmissionNumbers[$admissionKey] = true;

            if (Student::query()
                ->where('school_id', $school->id)
                ->where('admission_number', $row['admission_number'])
                ->exists()) {
                $errors[] = "Row {$row['row_number']}: admission_number already exists in this school.";
            }

            if (! SchoolClass::query()
                ->where('school_id', $school->id)
                ->whereKey((int) $row['school_class_id'])
                ->exists()) {
                $errors[] = "Row {$row['row_number']}: class was not found in this school.";
            }
        }

        return $errors;
    }

    private function attendanceSummaryRows(Collection $records): array
    {
        return $records
            ->groupBy(fn (StudentAttendanceRecord $record): string => $record->attendance_date?->toDateString().'|'.$record->school_class_id)
            ->map(function (Collection $group): array {
                $first = $group->first();
                $counts = collect(StudentAttendanceRecord::STATUSES)
                    ->mapWithKeys(fn (string $status): array => [$status => $group->where('status', $status)->count()])
                    ->all();

                return [
                    $first->attendance_date?->toDateString(),
                    $this->classLabel($first->schoolClass),
                    $counts[StudentAttendanceRecord::STATUS_PRESENT] ?? 0,
                    $counts[StudentAttendanceRecord::STATUS_ABSENT] ?? 0,
                    $counts[StudentAttendanceRecord::STATUS_LATE] ?? 0,
                    $counts[StudentAttendanceRecord::STATUS_EXCUSED] ?? 0,
                    $group->count(),
                ];
            })
            ->values()
            ->all();
    }

    private function classLookup(School $school): Collection
    {
        return $school->schoolClasses()
            ->where('status', 'active')
            ->get()
            ->flatMap(function (SchoolClass $class): array {
                $values = [
                    $class->name,
                    $class->code,
                    $this->classLabel($class),
                ];

                return collect($values)
                    ->filter(fn ($value): bool => filled($value))
                    ->mapWithKeys(fn ($value): array => [$this->normalizeKey($value) => $class])
                    ->all();
            });
    }

    private function missingHeaderErrors(array $headers): array
    {
        $errors = [];

        foreach (self::STUDENT_IMPORT_REQUIRED_HEADERS as $header) {
            if (! in_array($header, $headers, true)) {
                $errors[] = "Missing required column: {$header}.";
            }
        }

        return $errors;
    }

    private function previewResult(array $rows, array $errors, int $rowCount = 0): array
    {
        return [
            'token' => Str::random(40),
            'rows' => $rows,
            'errors' => $errors,
            'row_count' => $rowCount ?: count($rows),
            'valid_count' => count($rows),
            'error_count' => count($errors),
            'max_rows' => self::MAX_IMPORT_ROWS,
        ];
    }

    private function invoiceCsvRow(StudentFeeInvoice $invoice): array
    {
        return [
            'invoice',
            $invoice->invoice_number,
            $invoice->student?->admission_number,
            $invoice->student?->fullName(),
            $this->classLabel($invoice->schoolClass),
            $invoice->academicSession?->name,
            $invoice->term?->name,
            $invoice->status,
            $invoice->total_amount,
            $invoice->discount_amount,
            $invoice->paid_amount,
            $invoice->balance_amount,
            $invoice->due_date?->toDateString(),
            $invoice->issued_at?->toDateString(),
            null,
            null,
            null,
        ];
    }

    private function paymentCsvRow(StudentFeePayment $payment): array
    {
        $invoice = $payment->invoice;

        return [
            'payment',
            $invoice?->invoice_number,
            $payment->student?->admission_number,
            $payment->student?->fullName(),
            $this->classLabel($invoice?->schoolClass),
            $invoice?->academicSession?->name,
            $invoice?->term?->name,
            $invoice?->status,
            null,
            null,
            null,
            null,
            null,
            null,
            $payment->payment_date?->toDateString(),
            $payment->method,
            $payment->amount,
        ];
    }

    private function csvResponse(string $fileName, callable $callback): StreamedResponse
    {
        return response()->streamDownload(function () use ($callback): void {
            $handle = fopen('php://output', 'w');
            $callback($handle);
            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    private function fileName(School $school, string $name): string
    {
        return Str::slug($school->name ?: 'school').'-'.$name.'-'.now()->format('Ymd-His').'.csv';
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header): string {
            $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);
            $header = mb_strtolower(trim($header));

            return preg_replace('/\s+/', '_', $header);
        }, $headers);
    }

    private function safeFilters(array $filters): array
    {
        $safe = collect($filters)
            ->filter(fn ($value): bool => filled($value))
            ->only([
                'date',
                'date_from',
                'date_to',
                'school_class_id',
                'status',
                'academic_session_id',
                'term_id',
                'invoice_status',
                'payment_method',
            ])
            ->all();

        if (filled($filters['search'] ?? null)) {
            $safe['has_search'] = true;
        }

        return $safe;
    }

    private function sanitizeOriginalFilename(?string $fileName): ?string
    {
        if (! $fileName) {
            return null;
        }

        return Str::limit(basename($fileName), 120, '');
    }

    private function clean(mixed $value): string
    {
        return trim((string) $value);
    }

    private function validDate(string $date): bool
    {
        $parsed = date_create_from_format('Y-m-d', $date);

        return $parsed && $parsed->format('Y-m-d') === $date;
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

    private function normalizeKey(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }

    private function classLabel(?SchoolClass $class): string
    {
        if (! $class) {
            return '';
        }

        return trim($class->name.' '.$class->section);
    }

    private function hasFinanceInvoiceFilters(array $filters): bool
    {
        foreach (['school_class_id', 'academic_session_id', 'term_id', 'invoice_status'] as $key) {
            if (filled($filters[$key] ?? null)) {
                return true;
            }
        }

        return false;
    }
}
