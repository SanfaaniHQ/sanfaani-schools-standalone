<?php

namespace App\Http\Controllers\School;

use App\Events\StudentTransactionalEmailRequested;
use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentPromotionItem;
use App\Services\CurrentSchoolService;
use App\Services\SchoolAuthorizationService;
use App\Services\StudentAcademicLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentPromotionController extends Controller
{
    private const ACTIONS = ['promote', 'repeat', 'demote', 'graduate', 'transfer', 'withdraw', 'skip'];

    public function index()
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizePromotionAccess($school);

        return view('school.student-promotions.index', [
            'school' => $school,
            'batches' => $school->studentPromotionBatches()
                ->with(['fromSession', 'toSession', 'fromClass', 'toClass', 'createdBy'])
                ->withCount('items')
                ->latest()
                ->paginate(10),
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizePromotionAccess($school);

        return view('school.student-promotions.create', $this->formData($school));
    }

    public function preview(Request $request)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizePromotionAccess($school);
        $context = $this->validateContext($request, $school);
        $students = $this->studentsForContext($school, $context)
            ->with('schoolClass')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        if ($students->isEmpty()) {
            return back()
                ->withInput()
                ->with('promotion_error', 'No students were found for the selected session and class.');
        }

        return view('school.student-promotions.preview', array_merge($this->formData($school), [
            'context' => $context,
            'students' => $students,
            'defaultAction' => $this->defaultAction($context['promotion_type']),
            'selectAll' => $context['promotion_type'] === 'promote_class',
        ]));
    }

    public function store(Request $request)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizePromotionAccess($school);
        $context = $this->validateContext($request, $school);
        $eligibleStudents = $this->studentsForContext($school, $context)
            ->with('schoolClass')
            ->get()
            ->keyBy('id');

        $selectedRows = collect($request->input('students', []))
            ->filter(fn ($row) => (bool) data_get($row, 'selected'))
            ->filter(fn ($row, $studentId) => $eligibleStudents->has((int) $studentId));

        if ($selectedRows->isEmpty()) {
            throw ValidationException::withMessages([
                'students' => 'Select at least one eligible student before confirming promotion.',
            ]);
        }

        $classes = SchoolClass::where('school_id', $school->id)->get()->keyBy('id');
        $sessions = AcademicSession::where('school_id', $school->id)
            ->whereIn('id', [$context['from_academic_session_id'], $context['to_academic_session_id']])
            ->get()
            ->keyBy('id');
        $result = app(StudentAcademicLifecycleService::class)->processBatch(
            $school,
            $request->user(),
            $context,
            $selectedRows,
            $eligibleStudents,
            $classes,
            $sessions,
            $request
        );

        $this->dispatchPromotionEmails($result['promotion_item_ids']);

        return redirect()
            ->route('school.student-promotions.index')
            ->with('success', 'Student lifecycle changes processed successfully.');
    }

    public function history()
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizePromotionAccess($school);

        return view('school.student-promotions.history', [
            'school' => $school,
            'items' => $school->studentPromotionBatches()
                ->with(['fromSession', 'toSession', 'fromClass', 'toClass', 'items.student', 'createdBy'])
                ->latest()
                ->paginate(8),
        ]);
    }

    private function validateContext(Request $request, School $school): array
    {
        $data = $request->validate([
            'from_academic_session_id' => ['required', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'to_academic_session_id' => ['required', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'from_school_class_id' => ['required', Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'to_school_class_id' => ['nullable', Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'promotion_type' => ['required', Rule::in(array_keys($this->promotionTypes()))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (in_array($data['promotion_type'], ['promote_selected', 'promote_class', 'repeat_selected', 'demote_selected'], true) && blank($data['to_school_class_id'] ?? null)) {
            throw ValidationException::withMessages([
                'to_school_class_id' => 'A target class is required for promotion, demotion, or repeat actions.',
            ]);
        }

        return $data;
    }

    private function studentsForContext(School $school, array $context)
    {
        $usesEnrollmentData = StudentClassEnrollment::where('school_id', $school->id)
            ->where('academic_session_id', $context['from_academic_session_id'])
            ->where('school_class_id', $context['from_school_class_id'])
            ->whereIn('status', StudentClassEnrollment::CURRENT_STATUSES)
            ->whereNull('end_term_id')
            ->exists();

        return Student::where('school_id', $school->id)
            ->whereNotIn('status', ['graduated', 'transferred', 'withdrawn'])
            ->when($usesEnrollmentData, function ($query) use ($context) {
                $query->whereHas('classEnrollments', function ($query) use ($context) {
                    $query->where('academic_session_id', $context['from_academic_session_id'])
                        ->where('school_class_id', $context['from_school_class_id'])
                        ->whereIn('status', StudentClassEnrollment::CURRENT_STATUSES)
                        ->whereNull('end_term_id');
                });
            }, function ($query) use ($context) {
                $query->where('school_class_id', $context['from_school_class_id']);
            });
    }

    private function formData(School $school): array
    {
        return [
            'school' => $school,
            'academicSessions' => AcademicSession::where('school_id', $school->id)->latest()->get(),
            'classes' => SchoolClass::where('school_id', $school->id)->where('status', 'active')->orderBy('name')->orderBy('section')->get(),
            'promotionTypes' => $this->promotionTypes(),
            'actions' => self::ACTIONS,
        ];
    }

    private function promotionTypes(): array
    {
        return [
            'promote_selected' => 'Promote selected students',
            'promote_class' => 'Promote entire class',
            'repeat_selected' => 'Repeat selected students',
            'demote_selected' => 'Demote selected students',
            'graduate_selected' => 'Graduate selected students',
            'transfer_withdraw_selected' => 'Transfer/withdraw selected students',
        ];
    }

    private function defaultAction(string $promotionType): string
    {
        return match ($promotionType) {
            'repeat_selected' => 'repeat',
            'demote_selected' => 'demote',
            'graduate_selected' => 'graduate',
            'transfer_withdraw_selected' => 'transfer',
            default => 'promote',
        };
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizePromotionAccess(School $school): void
    {
        app(SchoolAuthorizationService::class)->authorizeAny(
            auth()->user(),
            $school,
            ['student.promote', 'student.transfer']
        );
    }

    private function authorizePromotionAction(School $school, string $action): void
    {
        $featureKey = match ($action) {
            'promote', 'repeat', 'demote' => 'student.promote',
            'graduate', 'transfer', 'withdraw' => 'student.transfer',
            default => null,
        };

        if ($featureKey) {
            app(SchoolAuthorizationService::class)->authorize(auth()->user(), $school, $featureKey);
        }
    }

    private function dispatchPromotionEmails(array $promotionItemIds): void
    {
        if (empty($promotionItemIds)) {
            return;
        }

        StudentPromotionItem::whereIn('id', $promotionItemIds)
            ->with(['student.school', 'fromClass', 'toClass', 'fromSession', 'toSession'])
            ->chunkById(100, function ($items) {
                foreach ($items as $item) {
                    StudentTransactionalEmailRequested::dispatch(StudentTransactionalEmailRequested::studentPromoted($item));
                }
            });
    }
}
