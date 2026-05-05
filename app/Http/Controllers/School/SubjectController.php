<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectAssignment;
use App\Models\LanguagePreference;
use App\Models\School;
use App\Models\StudentElectiveSubject;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $subjects = $school->subjects()
            ->when($request->boolean('include_archived'), fn ($query) => $query->withTrashed())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('assignment_type'), fn ($query) => $query->where('assignment_type', $request->input('assignment_type')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('school.subjects.index', [
            'school' => $school,
            'subjects' => $subjects,
            'filters' => $request->only(['search', 'status', 'assignment_type', 'include_archived']),
            'types' => ClassSubjectAssignment::TYPES,
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.subjects.create', [
            'school' => $school,
        ]);
    }

    public function store(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('subjects', 'name')
                    ->where('school_id', $school->id),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')
                    ->where('school_id', $school->id),
            ],
            'assignment_type' => ['required', Rule::in(ClassSubjectAssignment::TYPES)],
            'is_elective' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['school_id'] = $school->id;
        $data['code'] = filled($data['code'] ?? null) ? strtoupper(trim($data['code'])) : null;
        $data['is_elective'] = (bool) ($data['is_elective'] ?? false);

        Subject::create($data);

        return redirect()
            ->route('school.subjects.index')
            ->with('success', 'Subject created successfully.')
            ->with('next_actions', [
                ['label' => 'Add another subject', 'href' => route('school.subjects.create')],
                ['label' => 'Assign subject to class', 'href' => route('school.subject-assignments.create')],
                ['label' => 'Upload subjects', 'href' => route('school.subjects.upload.index')],
                ['label' => 'Back to dashboard', 'href' => route('school.dashboard')],
                ['label' => 'Go to Result System', 'href' => route('school.result-system.index')],
            ]);
    }

    public function edit(Subject $subject)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeSubject($subject, $school);

        return view('school.subjects.edit', [
            'school' => $school,
            'subject' => $subject,
            'languagePreference' => LanguagePreference::where('school_id', $school->id)
                ->where('scope_type', 'subject')
                ->where('scope_id', $subject->id)
                ->first(),
        ]);
    }

    public function update(Request $request, Subject $subject)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeSubject($subject, $school);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('subjects', 'name')
                    ->where('school_id', $school->id)
                    ->ignore($subject->id),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')
                    ->where('school_id', $school->id)
                    ->ignore($subject->id),
            ],
            'assignment_type' => ['required', Rule::in(ClassSubjectAssignment::TYPES)],
            'is_elective' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'language_code' => ['nullable', Rule::in(['en', 'fr', 'ar'])],
        ]);

        $languageCode = $data['language_code'] ?? null;
        unset($data['language_code']);

        $data['code'] = filled($data['code'] ?? null) ? strtoupper(trim($data['code'])) : null;
        $data['is_elective'] = (bool) ($data['is_elective'] ?? false);

        $subject->update($data);
        $this->saveLanguagePreference($school, $subject->id, $languageCode);

        return redirect()
            ->route('school.subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Request $request, Subject $subject, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSubject($subject, $school);

        if ($this->hasLinkedData($subject)) {
            $subject->update(['status' => 'inactive']);
            $subject->delete();

            $auditLog->log('subject_archived', $subject, $school, metadata: [
                'name' => $subject->name,
                'reason' => 'linked_data',
            ], request: $request);

            return back()->with('success', 'This record is linked to existing data. It has been archived instead of permanently deleted.');
        }

        $auditLog->log('subject_deleted', $subject, $school, metadata: [
            'name' => $subject->name,
        ], request: $request);

        $subject->forceDelete();

        return back()->with('success', 'Subject deleted permanently because no linked data was found.');
    }

    public function restore(Request $request, int $subject, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $subject = Subject::onlyTrashed()
            ->where('school_id', $school->id)
            ->findOrFail($subject);

        $subject->restore();
        $subject->update(['status' => 'active']);

        $auditLog->log('subject_restored', $subject, $school, metadata: [
            'name' => $subject->name,
        ], request: $request);

        return back()->with('success', 'Subject restored successfully.');
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(\App\Services\CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizeSubject(Subject $subject, School $school): void
    {
        if ($subject->school_id !== $school->id) {
            abort(403, 'You cannot access this subject.');
        }
    }

    private function hasLinkedData(Subject $subject): bool
    {
        return StudentResult::withTrashed()->where('subject_id', $subject->id)->exists()
            || ClassSubjectAssignment::withTrashed()->where('subject_id', $subject->id)->exists()
            || StudentElectiveSubject::where('subject_id', $subject->id)->exists();
    }

    private function saveLanguagePreference(School $school, int $subjectId, ?string $languageCode): void
    {
        if (! filled($languageCode)) {
            return;
        }

        LanguagePreference::updateOrCreate([
            'school_id' => $school->id,
            'scope_type' => 'subject',
            'scope_id' => $subjectId,
        ], [
            'language_code' => $languageCode,
            'is_default' => false,
            'status' => 'active',
        ]);
    }
}
