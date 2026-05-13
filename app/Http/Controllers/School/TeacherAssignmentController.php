<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\TeacherResultSubmission;
use App\Models\TeacherClassAssignment;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SchoolAuthorizationService;
use App\Services\TeacherAssignmentAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeacherAssignmentController extends Controller
{
    public function myAssignments()
    {
        $school = $this->currentSchoolOrFail();
        app(SchoolAuthorizationService::class)->authorize(auth()->user(), $school, 'teacher.assignments.view');
        $assignmentAccess = app(TeacherAssignmentAccessService::class);

        $classAssignments = $assignmentAccess->classAssignmentsQuery($school, auth()->user())
            ->with(['schoolClass', 'academicSession', 'term'])
            ->latest()
            ->paginate(12, ['*'], 'class_page');

        $subjectAssignments = $assignmentAccess->subjectAssignmentsQuery($school, auth()->user())
            ->with(['subject', 'schoolClass', 'academicSession', 'term'])
            ->latest()
            ->paginate(12, ['*'], 'subject_page');

        return view('school.teacher-assignments.my', [
            'school' => $school,
            'classAssignments' => $classAssignments,
            'subjectAssignments' => $subjectAssignments,
            'canCreateResults' => auth()->user()->can('create', [TeacherResultSubmission::class, $school]),
        ]);
    }

    public function index(Request $request)
    {
        $school = $this->currentSchoolOrFail();
        Gate::authorize('viewAny', [TeacherClassAssignment::class, $school]);

        $classAssignments = $school->teacherClassAssignments()
            ->when($request->boolean('include_archived'), fn ($query) => $query->withTrashed())
            ->with(['teacher', 'schoolClass', 'academicSession', 'term'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($query) use ($search) {
                    $query->whereHas('teacher', fn ($query) => $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('schoolClass', fn ($query) => $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate(10, ['*'], 'class_page')
            ->withQueryString();

        $subjectAssignments = $school->teacherSubjectAssignments()
            ->when($request->boolean('include_archived'), fn ($query) => $query->withTrashed())
            ->with(['teacher', 'subject', 'schoolClass', 'academicSession', 'term'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($query) use ($search) {
                    $query->whereHas('teacher', fn ($query) => $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('subject', fn ($query) => $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('schoolClass', fn ($query) => $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate(10, ['*'], 'subject_page')
            ->withQueryString();

        return view('school.teacher-assignments.index', [
            'school' => $school,
            'classAssignments' => $classAssignments,
            'subjectAssignments' => $subjectAssignments,
            'filters' => $request->only(['search', 'status', 'include_archived']),
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();
        Gate::authorize('create', [TeacherClassAssignment::class, $school]);

        return view('school.teacher-assignments.create', $this->formData($school));
    }

    public function store(Request $request, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        Gate::authorize('create', [TeacherClassAssignment::class, $school]);
        $data = $this->validated($request, $school);
        $type = $data['assignment_scope'];
        unset($data['assignment_scope']);

        $data['school_id'] = $school->id;
        $data['assigned_by'] = auth()->id();

        if ($this->duplicateExists($school, $type, $data)) {
            return back()
                ->withInput()
                ->withErrors(['teacher_user_id' => 'This teacher already has an active matching assignment.']);
        }

        DB::transaction(function () use ($type, $data, $school, $auditLog, $request) {
            $assignment = $type === 'class'
                ? TeacherClassAssignment::create($data)
                : TeacherSubjectAssignment::create($data);

            $auditLog->log($type === 'class' ? 'teacher_class_assigned' : 'teacher_subject_assigned', $assignment, $school, metadata: [
                'assignment_scope' => $type,
                'teacher_user_id' => $data['teacher_user_id'],
                'school_class_id' => $data['school_class_id'] ?? null,
                'subject_id' => $data['subject_id'] ?? null,
                'role_type' => $data['role_type'] ?? null,
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
            ], request: $request);
        });

        return redirect()
            ->route('school.teacher-assignments.index')
            ->with('success', 'Teacher assignment saved successfully.');
    }

    public function edit(Request $request, int $assignment)
    {
        $school = $this->currentSchoolOrFail();
        [$record, $type] = $this->findAssignment($school, $assignment, $request->query('type'));
        Gate::authorize('view', $record);

        return view('school.teacher-assignments.edit', $this->formData($school) + [
            'assignment' => $record,
            'assignmentType' => $type,
        ]);
    }

    public function update(Request $request, int $assignment, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        [$record, $type] = $this->findAssignment($school, $assignment, $request->input('type'));
        Gate::authorize('update', $record);
        $data = $this->validated($request, $school, $type);
        unset($data['assignment_scope']);

        if ($this->duplicateExists($school, $type, $data, $record->id)) {
            return back()
                ->withInput()
                ->withErrors(['teacher_user_id' => 'This teacher already has an active matching assignment.']);
        }

        DB::transaction(function () use ($record, $data, $school, $auditLog, $request) {
            $oldValues = $record->only($this->auditedColumns());
            $record->update($data);

            $auditLog->log('teacher_assignment_updated', $record, $school, $oldValues, $record->only($this->auditedColumns()), request: $request);
        });

        return redirect()
            ->route('school.teacher-assignments.index')
            ->with('success', 'Teacher assignment updated successfully.');
    }

    public function archive(Request $request, int $assignment, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        [$record, $type] = $this->findAssignment($school, $assignment, $request->input('type'));
        Gate::authorize('delete', $record);

        DB::transaction(function () use ($record, $type, $school, $auditLog, $request) {
            $oldValues = $record->only($this->auditedColumns());
            $updates = ['status' => 'archived'];

            if (! $record->ends_at) {
                $updates['ends_at'] = today();
            }

            $record->update($updates);
            $record->delete();

            $auditLog->log('teacher_assignment_archived', $record, $school, $oldValues, $record->only($this->auditedColumns()), metadata: [
                'assignment_scope' => $type,
                'teacher_user_id' => $record->teacher_user_id,
            ], request: $request);
        });

        return back()->with('success', 'Teacher assignment archived safely.');
    }

    public function restore(Request $request, int $assignment, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $type = $request->input('type', 'subject');
        $record = $type === 'class'
            ? TeacherClassAssignment::onlyTrashed()->where('school_id', $school->id)->findOrFail($assignment)
            : TeacherSubjectAssignment::onlyTrashed()->where('school_id', $school->id)->findOrFail($assignment);
        Gate::authorize('restore', $record);

        $data = $record->only(['teacher_user_id', 'school_class_id', 'subject_id', 'academic_session_id', 'term_id', 'role_type']);
        $data['status'] = 'active';

        if ($this->duplicateExists($school, $type, $data, $record->id)) {
            return back()->withErrors(['teacher_user_id' => 'Restoring this assignment would overlap with an active assignment.']);
        }

        DB::transaction(function () use ($record, $type, $school, $auditLog, $request) {
            $oldValues = $record->only($this->auditedColumns());
            $record->restore();
            $record->update(['status' => 'active', 'ends_at' => null]);

            $auditLog->log('teacher_assignment_restored', $record, $school, $oldValues, $record->only($this->auditedColumns()), metadata: [
                'assignment_scope' => $type,
                'teacher_user_id' => $record->teacher_user_id,
            ], request: $request);
        });

        return back()->with('success', 'Teacher assignment restored successfully.');
    }

    private function validated(Request $request, School $school, ?string $forcedType = null): array
    {
        $type = $forcedType ?: $request->input('assignment_scope', 'subject');
        $roleTypes = $type === 'class'
            ? ['class_teacher', 'assistant_teacher', 'co_teacher']
            : ['subject_teacher', 'assistant_teacher', 'co_teacher'];

        $data = $request->validate([
            'assignment_scope' => ['nullable', Rule::in(['class', 'subject'])],
            'teacher_user_id' => ['required', Rule::exists('users', 'id')],
            'school_class_id' => [$type === 'class' ? 'required' : 'nullable', Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'subject_id' => [$type === 'subject' ? 'required' : 'nullable', Rule::exists('subjects', 'id')->where('school_id', $school->id)],
            'academic_session_id' => ['nullable', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $school->id)],
            'role_type' => ['nullable', Rule::in($roleTypes)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::in(['active', 'inactive', 'archived'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! $this->teacherBelongsToSchool($school, (int) $data['teacher_user_id'])) {
            abort(422, 'The selected teacher is not assigned to this school.');
        }

        if (filled($data['term_id'] ?? null)) {
            $term = $school->terms()->withTrashed()->find((int) $data['term_id']);

            if (filled($data['academic_session_id'] ?? null) && (int) $term->academic_session_id !== (int) $data['academic_session_id']) {
                throw ValidationException::withMessages([
                    'term_id' => 'The selected term does not belong to the selected academic session.',
                ]);
            }

            $data['academic_session_id'] ??= $term->academic_session_id;
        }

        $data['assignment_scope'] = $type;
        $data['role_type'] = $data['role_type'] ?? ($type === 'class' ? 'class_teacher' : 'subject_teacher');
        $data['metadata'] = filled($data['notes'] ?? null) ? ['notes' => $data['notes']] : null;

        unset($data['notes']);

        if ($type === 'class') {
            unset($data['subject_id']);
        }

        return $data;
    }

    private function duplicateExists(School $school, string $type, array $data, ?int $exceptId = null): bool
    {
        if (($data['status'] ?? 'active') !== 'active') {
            return false;
        }

        $query = $type === 'class'
            ? TeacherClassAssignment::query()
            : TeacherSubjectAssignment::query();

        $query->where('school_id', $school->id)
            ->where('teacher_user_id', $data['teacher_user_id'])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId));

        $this->whereOverlappingNullable($query, 'academic_session_id', $data['academic_session_id'] ?? null);
        $this->whereOverlappingNullable($query, 'term_id', $data['term_id'] ?? null);

        if ($type === 'subject') {
            $query->where('subject_id', $data['subject_id']);
            $this->whereOverlappingNullable($query, 'school_class_id', $data['school_class_id'] ?? null);
        } else {
            $query->where('school_class_id', $data['school_class_id']);
        }

        return $query->exists();
    }

    private function whereOverlappingNullable($query, string $column, mixed $value): void
    {
        if (filled($value)) {
            $query->where(function ($query) use ($column, $value) {
                $query->whereNull($column)
                    ->orWhere($column, $value);
            });
        }
    }

    private function findAssignment(School $school, int $id, ?string $type = null): array
    {
        if ($type === 'class') {
            return [TeacherClassAssignment::withTrashed()->where('school_id', $school->id)->findOrFail($id), 'class'];
        }

        if ($type === 'subject') {
            return [TeacherSubjectAssignment::withTrashed()->where('school_id', $school->id)->findOrFail($id), 'subject'];
        }

        $assignment = TeacherSubjectAssignment::withTrashed()->where('school_id', $school->id)->find($id);

        return $assignment
            ? [$assignment, 'subject']
            : [TeacherClassAssignment::withTrashed()->where('school_id', $school->id)->findOrFail($id), 'class'];
    }

    private function teacherBelongsToSchool(School $school, int $userId): bool
    {
        return User::whereKey($userId)
            ->where(function ($query) use ($school) {
                $query->where('school_id', $school->id)
                    ->orWhereHas('activeSchoolRoles', fn ($query) => $query
                        ->where('school_id', $school->id)
                        ->where('role_name', 'teacher'));
            })
            ->where(function ($query) {
                $query->whereHas('roles', fn ($query) => $query->where('name', 'teacher'))
                    ->orWhereHas('activeSchoolRoles', fn ($query) => $query->where('role_name', 'teacher'));
            })
            ->exists();
    }

    private function formData(School $school): array
    {
        return [
            'school' => $school,
            'teachers' => User::query()
                ->where(function ($query) use ($school) {
                    $query->where('school_id', $school->id)
                        ->orWhereHas('activeSchoolRoles', fn ($query) => $query
                            ->where('school_id', $school->id)
                            ->where('role_name', 'teacher'));
                })
                ->where(function ($query) {
                    $query->whereHas('roles', fn ($query) => $query->where('name', 'teacher'))
                        ->orWhereHas('activeSchoolRoles', fn ($query) => $query->where('role_name', 'teacher'));
                })
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'classes' => $school->schoolClasses()->where('status', 'active')->orderBy('name')->get(),
            'subjects' => $school->subjects()->where('status', 'active')->orderBy('name')->get(),
            'academicSessions' => $school->academicSessions()->where('status', 'active')->latest()->get(),
            'terms' => $school->terms()->where('status', 'active')->with('academicSession')->latest()->get(),
        ];
    }

    private function auditedColumns(): array
    {
        return [
            'teacher_user_id',
            'school_class_id',
            'subject_id',
            'academic_session_id',
            'term_id',
            'role_type',
            'starts_at',
            'ends_at',
            'status',
        ];
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
