<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentPromotionBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentPromotionController extends Controller
{
    private const ACTIONS = ['promote', 'repeat', 'graduate', 'transfer', 'withdraw', 'skip'];

    public function index()
    {
        $school = $this->currentSchoolOrFail();

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

        return view('school.student-promotions.create', $this->formData($school));
    }

    public function preview(Request $request)
    {
        $school = $this->currentSchoolOrFail();
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
        $counts = array_fill_keys(self::ACTIONS, 0);

        DB::transaction(function () use ($school, $context, $selectedRows, $eligibleStudents, $classes, &$counts) {
            $batch = StudentPromotionBatch::create([
                'school_id' => $school->id,
                'from_academic_session_id' => $context['from_academic_session_id'],
                'to_academic_session_id' => $context['to_academic_session_id'],
                'from_school_class_id' => $context['from_school_class_id'],
                'to_school_class_id' => $context['to_school_class_id'] ?? null,
                'promotion_type' => $context['promotion_type'],
                'status' => 'completed',
                'created_by' => auth()->id(),
                'notes' => $context['notes'] ?? null,
                'metadata' => [
                    'processed_at' => now()->toDateTimeString(),
                ],
            ]);

            foreach ($selectedRows as $studentId => $row) {
                $student = $eligibleStudents->get((int) $studentId);
                $action = data_get($row, 'action', $this->defaultAction($context['promotion_type']));

                if (! in_array($action, self::ACTIONS, true)) {
                    $action = 'skip';
                }

                $targetClassId = $this->targetClassId($action, $row, $context);

                if (in_array($action, ['promote', 'repeat'], true)) {
                    if (! $targetClassId || ! $classes->has((int) $targetClassId)) {
                        throw ValidationException::withMessages([
                            'to_school_class_id' => 'A valid target class is required for promoted or repeated students.',
                        ]);
                    }

                    if (
                        $action === 'promote'
                        && (int) $context['from_academic_session_id'] === (int) $context['to_academic_session_id']
                        && (int) $context['from_school_class_id'] === (int) $targetClassId
                    ) {
                        throw ValidationException::withMessages([
                            'to_school_class_id' => 'Use repeat when keeping a student in the same class context.',
                        ]);
                    }
                }

                if ($action === 'promote' || $action === 'repeat') {
                    $fromEnrollment = $this->sourceEnrollment($school, $student, $context);

                    StudentClassEnrollment::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'academic_session_id' => $context['to_academic_session_id'],
                        ],
                        [
                            'school_id' => $school->id,
                            'school_class_id' => $targetClassId,
                            'status' => $action === 'repeat' ? 'repeating' : 'active',
                            'enrolled_at' => now(),
                            'promoted_from_enrollment_id' => $fromEnrollment?->id,
                            'metadata' => [
                                'promotion_action' => $action,
                                'promotion_batch_id' => $batch->id,
                            ],
                        ]
                    );

                    $student->update([
                        'school_class_id' => $targetClassId,
                        'status' => 'active',
                    ]);
                }

                if ($action === 'graduate') {
                    $student->update(['status' => 'graduated']);
                }

                if ($action === 'transfer') {
                    $student->update(['status' => 'transferred']);
                }

                if ($action === 'withdraw') {
                    $student->update(['status' => 'withdrawn']);
                }

                $batch->items()->create([
                    'school_id' => $school->id,
                    'student_id' => $student->id,
                    'from_school_class_id' => $context['from_school_class_id'],
                    'to_school_class_id' => in_array($action, ['promote', 'repeat'], true) ? $targetClassId : null,
                    'from_academic_session_id' => $context['from_academic_session_id'],
                    'to_academic_session_id' => $context['to_academic_session_id'],
                    'action' => $action,
                    'status' => $action === 'skip' ? 'skipped' : 'completed',
                    'notes' => data_get($row, 'notes'),
                ]);

                $counts[$action]++;
            }

            $batch->update(['metadata' => array_merge($batch->metadata ?? [], ['counts' => $counts])]);
        });

        return redirect()
            ->route('school.student-promotions.index')
            ->with('success', 'Student promotion processed successfully.');
    }

    public function history()
    {
        $school = $this->currentSchoolOrFail();

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

        if (in_array($data['promotion_type'], ['promote_selected', 'promote_class', 'repeat_selected'], true) && blank($data['to_school_class_id'] ?? null)) {
            throw ValidationException::withMessages([
                'to_school_class_id' => 'A target class is required for promotion or repeat actions.',
            ]);
        }

        return $data;
    }

    private function studentsForContext(School $school, array $context)
    {
        $usesEnrollmentData = StudentClassEnrollment::where('school_id', $school->id)
            ->where('academic_session_id', $context['from_academic_session_id'])
            ->exists();

        return Student::where('school_id', $school->id)
            ->whereNotIn('status', ['graduated', 'transferred', 'withdrawn'])
            ->when($usesEnrollmentData, function ($query) use ($context) {
                $query->whereHas('classEnrollments', function ($query) use ($context) {
                    $query->where('academic_session_id', $context['from_academic_session_id'])
                        ->where('school_class_id', $context['from_school_class_id']);
                });
            }, function ($query) use ($context) {
                $query->where('school_class_id', $context['from_school_class_id']);
            });
    }

    private function sourceEnrollment(School $school, Student $student, array $context): ?StudentClassEnrollment
    {
        return StudentClassEnrollment::firstOrCreate(
            [
                'student_id' => $student->id,
                'academic_session_id' => $context['from_academic_session_id'],
            ],
            [
                'school_id' => $school->id,
                'school_class_id' => $context['from_school_class_id'],
                'status' => 'active',
                'enrolled_at' => null,
                'metadata' => [
                    'source' => 'backfilled_during_promotion',
                ],
            ]
        );
    }

    private function targetClassId(string $action, array $row, array $context): ?int
    {
        if ($action === 'repeat') {
            return (int) (data_get($row, 'to_school_class_id') ?: $context['to_school_class_id'] ?: $context['from_school_class_id']);
        }

        if ($action === 'promote') {
            return (int) (data_get($row, 'to_school_class_id') ?: $context['to_school_class_id']);
        }

        return null;
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
            'graduate_selected' => 'Graduate selected students',
            'transfer_withdraw_selected' => 'Transfer/withdraw selected students',
        ];
    }

    private function defaultAction(string $promotionType): string
    {
        return match ($promotionType) {
            'repeat_selected' => 'repeat',
            'graduate_selected' => 'graduate',
            'transfer_withdraw_selected' => 'transfer',
            default => 'promote',
        };
    }

    private function currentSchoolOrFail(): School
    {
        $school = auth()->user()->school;

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }
}
