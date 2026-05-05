<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectAssignment;
use App\Models\School;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SubjectAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $assignments = $school->classSubjectAssignments()
            ->when($request->boolean('include_archived'), fn ($query) => $query->withTrashed())
            ->with(['subject', 'schoolClass', 'academicSession', 'term'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($query) use ($search) {
                    $query->whereHas('subject', fn ($query) => $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('schoolClass', fn ($query) => $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('assignment_type'), fn ($query) => $query->where('assignment_type', $request->input('assignment_type')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('school.subject-assignments.index', [
            'school' => $school,
            'assignments' => $assignments,
            'filters' => $request->only(['search', 'status', 'assignment_type', 'include_archived']),
            'types' => ClassSubjectAssignment::TYPES,
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.subject-assignments.create', $this->formData($school));
    }

    public function store(Request $request, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $data = $this->validated($request, $school);
        $classIds = $this->selectedClassIds($request, $school);
        $created = 0;

        if ($this->hasDuplicateActiveAssignment($school, $data, $classIds)) {
            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' => 'An active assignment already exists for this subject, class, session, and term combination.',
                ]);
        }

        DB::transaction(function () use ($school, $data, $classIds, &$created) {
            foreach ($classIds as $classId) {
                ClassSubjectAssignment::create($data + [
                    'school_id' => $school->id,
                    'school_class_id' => $classId,
                ]);

                $created++;
            }
        });

        $auditLog->log('subject_assignment_created', null, $school, metadata: [
            'subject_id' => $data['subject_id'],
            'created' => $created,
            'scope' => $request->boolean('assign_to_all') ? 'all_classes' : ($classIds === [null] ? 'general' : 'selected_classes'),
        ], request: $request);

        return redirect()
            ->route('school.subject-assignments.index')
            ->with('success', "{$created} subject assignment record(s) saved successfully.");
    }

    public function edit(ClassSubjectAssignment $assignment)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeAssignment($assignment, $school);

        return view('school.subject-assignments.edit', $this->formData($school) + [
            'assignment' => $assignment,
        ]);
    }

    public function update(Request $request, ClassSubjectAssignment $assignment, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeAssignment($assignment, $school);

        $data = $this->validated($request, $school);
        $data['school_class_id'] = $request->filled('school_class_id') ? $request->integer('school_class_id') : null;

        if ($this->hasDuplicateActiveAssignment($school, $data, [$data['school_class_id']], $assignment->id)) {
            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' => 'An active assignment already exists for this subject, class, session, and term combination.',
                ]);
        }

        $oldValues = $assignment->only(['school_class_id', 'subject_id', 'assignment_type', 'is_elective', 'is_required', 'status']);
        $assignment->update($data);

        $auditLog->log('subject_assignment_updated', $assignment, $school, $oldValues, $assignment->only([
            'school_class_id',
            'subject_id',
            'assignment_type',
            'is_elective',
            'is_required',
            'status',
        ]), request: $request);

        return redirect()
            ->route('school.subject-assignments.index')
            ->with('success', 'Subject assignment updated successfully.');
    }

    public function archive(Request $request, ClassSubjectAssignment $assignment, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeAssignment($assignment, $school);

        $assignment->update(['status' => 'archived']);
        $assignment->delete();

        $auditLog->log('subject_assignment_archived', $assignment, $school, metadata: [
            'subject_id' => $assignment->subject_id,
            'school_class_id' => $assignment->school_class_id,
        ], request: $request);

        return back()->with('success', 'Subject assignment archived safely.');
    }

    public function restore(Request $request, int $assignment, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $assignment = ClassSubjectAssignment::onlyTrashed()
            ->where('school_id', $school->id)
            ->findOrFail($assignment);

        $assignment->restore();
        $assignment->update(['status' => 'active']);

        $auditLog->log('subject_assignment_restored', $assignment, $school, metadata: [
            'subject_id' => $assignment->subject_id,
            'school_class_id' => $assignment->school_class_id,
        ], request: $request);

        return back()->with('success', 'Subject assignment restored successfully.');
    }

    private function validated(Request $request, School $school): array
    {
        $termRule = Rule::exists('terms', 'id')->where('school_id', $school->id);

        if ($request->filled('academic_session_id')) {
            $termRule->where('academic_session_id', $request->input('academic_session_id'));
        }

        $data = $request->validate([
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $school->id)],
            'school_class_id' => ['nullable', Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'school_class_ids' => ['nullable', 'array'],
            'school_class_ids.*' => [Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'assign_to_all' => ['nullable', 'boolean'],
            'academic_session_id' => ['nullable', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => ['nullable', $termRule],
            'assignment_type' => ['required', Rule::in(ClassSubjectAssignment::TYPES)],
            'is_elective' => ['nullable', 'boolean'],
            'is_required' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive', 'archived'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]) + [
            'is_elective' => false,
            'is_required' => true,
        ];

        $data['metadata'] = filled($data['notes'] ?? null) ? ['notes' => $data['notes']] : null;

        unset($data['notes'], $data['school_class_ids'], $data['assign_to_all'], $data['school_class_id']);

        return $data;
    }

    private function selectedClassIds(Request $request, School $school): array
    {
        if ($request->boolean('assign_to_all')) {
            $classIds = $school->schoolClasses()
                ->where('status', 'active')
                ->pluck('id')
                ->all();

            return $classIds !== [] ? $classIds : [null];
        }

        $classIds = collect($request->input('school_class_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($request->filled('school_class_id')) {
            $classIds[] = $request->integer('school_class_id');
        }

        $classIds = array_values(array_unique($classIds));

        return $classIds !== [] ? $classIds : [null];
    }

    private function formData(School $school): array
    {
        return [
            'school' => $school,
            'subjects' => $school->subjects()->where('status', 'active')->orderBy('name')->get(),
            'classes' => $school->schoolClasses()->where('status', 'active')->orderBy('name')->orderBy('section')->get(),
            'academicSessions' => $school->academicSessions()->where('status', 'active')->latest()->get(),
            'terms' => $school->terms()->where('status', 'active')->with('academicSession')->latest()->get(),
            'types' => ClassSubjectAssignment::TYPES,
        ];
    }

    private function hasDuplicateActiveAssignment(School $school, array $data, array $classIds, ?int $exceptId = null): bool
    {
        if (($data['status'] ?? 'active') !== 'active') {
            return false;
        }

        foreach ($classIds as $classId) {
            $exists = ClassSubjectAssignment::where('school_id', $school->id)
                ->where('subject_id', $data['subject_id'])
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
                ->when(
                    $classId,
                    fn ($query) => $query->where('school_class_id', $classId),
                    fn ($query) => $query->whereNull('school_class_id')
                )
                ->when(
                    $data['academic_session_id'] ?? null,
                    fn ($query, $sessionId) => $query->where('academic_session_id', $sessionId),
                    fn ($query) => $query->whereNull('academic_session_id')
                )
                ->when(
                    $data['term_id'] ?? null,
                    fn ($query, $termId) => $query->where('term_id', $termId),
                    fn ($query) => $query->whereNull('term_id')
                )
                ->exists();

            if ($exists) {
                return true;
            }
        }

        return false;
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(\App\Services\CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizeAssignment(ClassSubjectAssignment $assignment, School $school): void
    {
        if ((int) $assignment->school_id !== (int) $school->id) {
            abort(403, 'You cannot access this subject assignment.');
        }
    }
}
