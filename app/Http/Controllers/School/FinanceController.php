<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\FinanceFeeAssignment;
use App\Models\FinanceFeeItem;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFeeInvoice;
use App\Services\CurrentSchoolService;
use App\Services\Finance\SchoolFinanceReportService;
use App\Services\Finance\SchoolFinanceService;
use App\Services\SchoolAuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinanceController extends Controller
{
    public function __construct(
        private CurrentSchoolService $currentSchool,
        private SchoolAuthorizationService $authorization,
        private SchoolFinanceService $finance,
        private SchoolFinanceReportService $reports,
    ) {}

    public function index(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorizeFinance($request, $school, 'finance.view');

        return view('school.finance.index', [
            'school' => $school,
            'summary' => $this->finance->summary($school),
            'recentInvoices' => $this->finance->invoicesQuery($school)->limit(5)->get(),
            'recentPayments' => $school->studentFeePayments()
                ->with(['student', 'invoice'])
                ->latest('id')
                ->limit(5)
                ->get(),
        ]);
    }

    public function feeItems(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorizeFinance($request, $school, 'finance.view');

        $items = FinanceFeeItem::query()
            ->where('school_id', $school->id)
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));

                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('school.finance.fee-items', [
            'school' => $school,
            'items' => $items,
            'filters' => $request->only(['search']),
        ]);
    }

    public function reports(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorizeFinance($request, $school, 'finance.view');
        $filters = $this->validatedReportFilters($request, $school);

        return view('school.finance.reports', [
            'school' => $school,
            'filters' => $filters,
            'report' => $this->reports->report($school, $filters),
            'statuses' => $this->finance->invoiceStatuses(),
            'paymentMethods' => $this->finance->paymentMethods(),
            ...$this->financeOptions($school),
        ]);
    }

    public function audit(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorizeFinance($request, $school, 'finance.view');
        $filters = $this->validatedAuditFilters($request, $school);
        $logs = $this->reports->auditLogQuery($school, $filters)
            ->with('user')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $logs->getCollection()->transform(function ($log) {
            $log->safe_finance_metadata = $this->reports->safeAuditMetadata($log);

            return $log;
        });

        return view('school.finance.audit', [
            'school' => $school,
            'filters' => $filters,
            'logs' => $logs,
            'actions' => $this->reports->financeAuditActions(),
            ...$this->financeOptions($school),
        ]);
    }

    public function storeFeeItem(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorizeFinance($request, $school, 'finance.manage');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('finance_fee_items', 'code')->where('school_id', $school->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'default_amount' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $this->finance->createFeeItem($school, $request->user(), $data);

        return redirect()
            ->route('school.finance.fee-items.index')
            ->with('success', 'Fee item created successfully.');
    }

    public function assignments(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorizeFinance($request, $school, 'finance.view');

        $assignments = FinanceFeeAssignment::query()
            ->where('school_id', $school->id)
            ->with(['feeItem', 'academicSession', 'term', 'schoolClass', 'student'])
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('school.finance.assignments', [
            'school' => $school,
            'assignments' => $assignments,
            ...$this->financeOptions($school),
        ]);
    }

    public function storeAssignment(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorizeFinance($request, $school, 'finance.manage');

        $data = $request->validate([
            'fee_item_id' => [
                'required',
                'integer',
                Rule::exists('finance_fee_items', 'id')->where('school_id', $school->id),
            ],
            'academic_session_id' => [
                'nullable',
                'integer',
                Rule::exists('academic_sessions', 'id')->where('school_id', $school->id),
            ],
            'term_id' => [
                'nullable',
                'integer',
                Rule::exists('terms', 'id')->where('school_id', $school->id),
            ],
            'school_class_id' => [
                'nullable',
                'integer',
                Rule::exists('school_classes', 'id')->where('school_id', $school->id),
            ],
            'student_id' => [
                'nullable',
                'integer',
                Rule::exists('students', 'id')->where('school_id', $school->id),
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'due_date' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $this->finance->createFeeAssignment($school, $request->user(), $data);

        return redirect()
            ->route('school.finance.assignments.index')
            ->with('success', 'Fee assignment created successfully.');
    }

    public function invoices(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorizeFinance($request, $school, 'finance.view');

        $filters = $request->validate([
            'status' => ['nullable', Rule::in($this->finance->invoiceStatuses())],
            'school_class_id' => ['nullable', 'integer'],
            'academic_session_id' => ['nullable', 'integer'],
            'term_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $invoices = $this->finance->invoicesQuery($school)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['school_class_id'] ?? null, fn ($query, $id) => $query->where('school_class_id', $id))
            ->when($filters['academic_session_id'] ?? null, fn ($query, $id) => $query->where('academic_session_id', $id))
            ->when($filters['term_id'] ?? null, fn ($query, $id) => $query->where('term_id', $id))
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('student', function ($query) use ($search): void {
                            $query->where('admission_number', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->paginate(10)
            ->withQueryString();

        return view('school.finance.invoices.index', [
            'school' => $school,
            'invoices' => $invoices,
            'statuses' => $this->finance->invoiceStatuses(),
            'filters' => $filters,
            ...$this->financeOptions($school),
        ]);
    }

    public function generateInvoices(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorizeFinance($request, $school, 'finance.manage');

        $data = $request->validate([
            'student_id' => [
                'nullable',
                'required_without:school_class_id',
                'integer',
                Rule::exists('students', 'id')->where('school_id', $school->id),
            ],
            'school_class_id' => [
                'nullable',
                'required_without:student_id',
                'integer',
                Rule::exists('school_classes', 'id')->where('school_id', $school->id),
            ],
            'academic_session_id' => [
                'nullable',
                'integer',
                Rule::exists('academic_sessions', 'id')->where('school_id', $school->id),
            ],
            'term_id' => [
                'nullable',
                'integer',
                Rule::exists('terms', 'id')->where('school_id', $school->id),
            ],
            'due_date' => ['nullable', 'date'],
        ]);

        if (filled($data['student_id'] ?? null)) {
            $student = Student::query()->where('school_id', $school->id)->findOrFail($data['student_id']);
            $result = $this->finance->generateStudentInvoice($school, $request->user(), $student, $data);

            return redirect()
                ->route('school.finance.invoices.show', $result['invoice'])
                ->with('success', $result['created'] ? 'Student invoice generated successfully.' : 'Matching invoice already existed; opened existing invoice.');
        }

        $class = SchoolClass::query()->where('school_id', $school->id)->findOrFail($data['school_class_id']);
        $result = $this->finance->generateClassInvoices($school, $request->user(), $class, $data);

        return redirect()
            ->route('school.finance.invoices.index', ['school_class_id' => $class->id])
            ->with('success', "{$result['created']} class invoice(s) generated; {$result['existing']} existing invoice(s) preserved.");
    }

    public function showInvoice(Request $request, StudentFeeInvoice $invoice)
    {
        $school = $this->schoolOrFail();
        $this->authorizeFinance($request, $school, 'finance.view');
        $this->authorizeInvoice($invoice, $school);
        $this->finance->recalculateInvoice($invoice);

        return view('school.finance.invoices.show', [
            'school' => $school,
            'invoice' => $invoice->load(['student', 'schoolClass', 'academicSession', 'term', 'items.feeItem', 'payments.receiver']),
            'paymentMethods' => $this->finance->paymentMethods(),
        ]);
    }

    public function recordPayment(Request $request, StudentFeeInvoice $invoice)
    {
        $school = $this->schoolOrFail();
        $this->authorizeFinance($request, $school, 'finance.manage');
        $this->authorizeInvoice($invoice, $school);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'payment_date' => ['required', 'date'],
            'method' => ['required', Rule::in($this->finance->paymentMethods())],
            'reference' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->finance->recordPayment($school, $request->user(), $invoice, $data);

        return redirect()
            ->route('school.finance.invoices.show', $invoice)
            ->with('success', 'Payment recorded successfully.');
    }

    public function student(Request $request, Student $student)
    {
        $school = $this->schoolOrFail();
        $this->authorizeFinance($request, $school, 'finance.view');

        $history = $this->finance->studentHistory($school, $student);

        return view('school.finance.students.show', [
            'school' => $school,
            'student' => $student,
            ...$history,
        ]);
    }

    private function schoolOrFail(): School
    {
        $school = $this->currentSchool->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizeFinance(Request $request, School $school, string $feature): void
    {
        $this->authorization->authorize($request->user(), $school, $feature);
    }

    private function authorizeInvoice(StudentFeeInvoice $invoice, School $school): void
    {
        if ((int) $invoice->school_id !== (int) $school->id) {
            abort(403, 'You cannot access this invoice.');
        }
    }

    private function validatedReportFilters(Request $request, School $school): array
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'academic_session_id' => [
                'nullable',
                'integer',
                Rule::exists('academic_sessions', 'id')->where('school_id', $school->id),
            ],
            'term_id' => [
                'nullable',
                'integer',
                Rule::exists('terms', 'id')->where('school_id', $school->id),
            ],
            'school_class_id' => [
                'nullable',
                'integer',
                Rule::exists('school_classes', 'id')->where('school_id', $school->id),
            ],
            'invoice_status' => ['nullable', Rule::in($this->finance->invoiceStatuses())],
            'payment_method' => ['nullable', Rule::in($this->finance->paymentMethods())],
            'student_id' => [
                'nullable',
                'integer',
                Rule::exists('students', 'id')->where('school_id', $school->id),
            ],
        ]);

        return collect($filters)->filter(fn ($value): bool => filled($value))->all();
    }

    private function validatedAuditFilters(Request $request, School $school): array
    {
        $filters = $request->validate([
            'action' => ['nullable', Rule::in($this->reports->financeAuditActions())],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'school_class_id' => [
                'nullable',
                'integer',
                Rule::exists('school_classes', 'id')->where('school_id', $school->id),
            ],
            'student_id' => [
                'nullable',
                'integer',
                Rule::exists('students', 'id')->where('school_id', $school->id),
            ],
        ]);

        return collect($filters)->filter(fn ($value): bool => filled($value))->all();
    }

    private function financeOptions(School $school): array
    {
        return [
            'feeItems' => $school->financeFeeItems()->where('is_active', true)->orderBy('name')->get(),
            'classes' => $school->schoolClasses()->where('status', 'active')->orderBy('name')->orderBy('section')->get(),
            'students' => $school->students()->where('status', 'active')->orderBy('last_name')->orderBy('first_name')->get(),
            'academicSessions' => $school->academicSessions()->where('status', 'active')->latest()->get(),
            'terms' => $school->terms()->where('status', 'active')->with('academicSession')->latest()->get(),
        ];
    }
}
