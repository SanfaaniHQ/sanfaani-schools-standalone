<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\StudentAttendanceRecord;
use App\Models\StudentFeeInvoice;
use App\Models\StudentFeePayment;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use App\Services\ImportExport\SchoolImportExportService;
use App\Services\SchoolAuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ImportExportController extends Controller
{
    public function __construct(
        private CurrentSchoolService $currentSchool,
        private SchoolAuthorizationService $authorization,
        private SchoolImportExportService $importExport,
        private AuditLogService $auditLog,
    ) {}

    public function index(Request $request)
    {
        $school = $this->schoolOrFail();
        $role = $this->role($request);
        abort_unless(in_array($role, ['school_admin', 'accountant', 'super_admin'], true), 403);

        $canManageStudentImports = $this->canUseStudentTools($request, $school);
        $canExportAttendance = $this->canUseAttendanceTools($request, $school);
        $canExportFinance = $this->canUseFinanceTools($request, $school);

        return view('school.import-export.index', [
            'school' => $school,
            'classes' => $school->schoolClasses()->where('status', 'active')->orderBy('name')->orderBy('section')->get(),
            'academicSessions' => $school->academicSessions()->where('status', 'active')->latest()->get(),
            'terms' => $school->terms()->where('status', 'active')->with('academicSession')->latest()->get(),
            'studentStatuses' => ['active', 'inactive', 'graduated', 'transferred', 'withdrawn'],
            'attendanceStatuses' => StudentAttendanceRecord::STATUSES,
            'invoiceStatuses' => StudentFeeInvoice::STATUSES,
            'paymentMethods' => StudentFeePayment::METHODS,
            'studentImportHeaders' => SchoolImportExportService::STUDENT_IMPORT_HEADERS,
            'studentImportPreview' => session('student_import_preview_display'),
            'pendingStudentImport' => session('student_import_preview'),
            'canManageStudentImports' => $canManageStudentImports,
            'canExportAttendance' => $canExportAttendance,
            'canExportFinance' => $canExportFinance,
        ]);
    }

    public function exportStudents(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorizeStudentTools($request, $school);
        $filters = $this->validatedStudentExportFilters($request, $school);

        return $this->importExport->exportStudents($school, $filters, $request->user(), $request);
    }

    public function studentTemplate(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorizeStudentTools($request, $school);

        return $this->importExport->studentTemplate($school, $request->user(), $request);
    }

    public function previewStudents(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorizeStudentTools($request, $school);

        $validator = Validator::make($request->all(), [
            'student_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        if ($validator->fails()) {
            $this->auditLog->log('import_export_students_import_validation_failed', null, $school, metadata: [
                'tool' => 'import_export',
                'module' => 'students',
                'stage' => 'file_validation',
                'error_count' => $validator->errors()->count(),
                'actor_id' => $request->user()->id,
            ], request: $request);

            return back()->withErrors($validator)->withInput();
        }

        $preview = $this->importExport->previewStudentImport(
            $school,
            $request->file('student_file'),
            $request->user(),
            $request
        );

        session()->forget('student_import_preview');

        if ($preview['error_count'] === 0 && $preview['valid_count'] > 0) {
            session([
                'student_import_preview' => [
                    'token' => $preview['token'],
                    'rows' => $preview['rows'],
                    'valid_count' => $preview['valid_count'],
                ],
            ]);
        }

        return redirect()
            ->route('school.import-export.index')
            ->with('student_import_preview_display', $preview)
            ->with($preview['error_count'] > 0 ? 'warning' : 'success', $preview['error_count'] > 0
                ? 'Student import preview found validation issues. No records were written.'
                : 'Student import preview passed. Review the rows and confirm to import.');
    }

    public function commitStudents(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorizeStudentTools($request, $school);

        $data = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $pending = session('student_import_preview');

        if (! $pending || ! hash_equals((string) $pending['token'], (string) $data['token'])) {
            return redirect()
                ->route('school.import-export.index')
                ->with('warning', 'Student import preview expired. Upload the CSV again before confirming.');
        }

        $result = $this->importExport->commitStudentImport($school, $pending['rows'], $request->user(), $request);

        if ($result['errors'] !== []) {
            session()->forget('student_import_preview');

            return redirect()
                ->route('school.import-export.index')
                ->with('student_import_preview_display', [
                    'token' => null,
                    'rows' => [],
                    'errors' => $result['errors'],
                    'row_count' => count($pending['rows']),
                    'valid_count' => 0,
                    'error_count' => count($result['errors']),
                    'max_rows' => 200,
                ])
                ->with('warning', 'Student import could not be completed. No records were written.');
        }

        session()->forget('student_import_preview');

        return redirect()
            ->route('school.import-export.index')
            ->with('success', "{$result['created']} student(s) imported successfully.");
    }

    public function exportAttendance(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorizeAttendanceTools($request, $school);
        $filters = $this->validatedAttendanceExportFilters($request, $school);

        return $this->importExport->exportAttendance($school, $filters, $request->user(), $request);
    }

    public function exportFinance(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorization->authorize($request->user(), $school, 'finance.view');
        $filters = $this->validatedFinanceExportFilters($request, $school);

        return $this->importExport->exportFinance($school, $filters, $request->user(), $request);
    }

    private function validatedStudentExportFilters(Request $request, School $school): array
    {
        return collect($request->validate([
            'school_class_id' => ['nullable', Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'graduated', 'transferred', 'withdrawn'])],
            'search' => ['nullable', 'string', 'max:100'],
        ]))->filter(fn ($value): bool => filled($value))->all();
    }

    private function validatedAttendanceExportFilters(Request $request, School $school): array
    {
        return collect($request->validate([
            'date' => ['nullable', 'date'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'school_class_id' => ['nullable', Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'status' => ['nullable', Rule::in(StudentAttendanceRecord::STATUSES)],
            'academic_session_id' => ['nullable', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $school->id)],
        ]))->filter(fn ($value): bool => filled($value))->all();
    }

    private function validatedFinanceExportFilters(Request $request, School $school): array
    {
        return collect($request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'school_class_id' => ['nullable', Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'academic_session_id' => ['nullable', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $school->id)],
            'invoice_status' => ['nullable', Rule::in(StudentFeeInvoice::STATUSES)],
            'payment_method' => ['nullable', Rule::in(StudentFeePayment::METHODS)],
        ]))->filter(fn ($value): bool => filled($value))->all();
    }

    private function authorizeStudentTools(Request $request, School $school): void
    {
        abort_unless($this->canUseStudentTools($request, $school), 403);
    }

    private function authorizeAttendanceTools(Request $request, School $school): void
    {
        abort_unless($this->canUseAttendanceTools($request, $school), 403);
    }

    private function canUseStudentTools(Request $request, School $school): bool
    {
        return in_array($this->role($request), ['school_admin', 'super_admin'], true)
            && $this->authorization->can($request->user(), $school, 'students.view');
    }

    private function canUseAttendanceTools(Request $request, School $school): bool
    {
        return in_array($this->role($request), ['school_admin', 'super_admin'], true)
            && $this->authorization->can($request->user(), $school, 'attendance.view');
    }

    private function canUseFinanceTools(Request $request, School $school): bool
    {
        return $this->authorization->can($request->user(), $school, 'finance.view');
    }

    private function schoolOrFail(): School
    {
        $school = $this->currentSchool->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function role(Request $request): ?string
    {
        return $this->currentSchool->roleContext($request->user());
    }
}
