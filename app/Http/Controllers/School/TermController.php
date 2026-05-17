<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Term;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TermController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

        $terms = $school->terms()
            ->with('academicSession')
            ->withCount([
                'studentResults',
                'resultPublications',
                'reportCardSnapshots',
                'teacherResultSubmissions',
            ])
            ->orderByDesc('is_active')
            ->latest()
            ->paginate(10);

        return view('school.terms.index', [
            'school' => $school,
            'terms' => $terms,
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.terms.create', [
            'school' => $school,
            'academicSessions' => $this->academicSessionsForSchool($school),
        ]);
    }

    public function store(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $data = $request->validate([
            'academic_session_id' => [
                'required',
                Rule::exists('academic_sessions', 'id')
                    ->where('school_id', $school->id),
            ],
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('terms', 'name')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $request->input('academic_session_id')),
            ],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['school_id'] = $school->id;
        $data['is_active'] = $request->boolean('is_active');

        if ($data['is_active']) {
            $school->terms()
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        Term::create($data);

        return redirect()
            ->route('school.terms.index')
            ->with('success', 'Term created successfully.');
    }

    public function edit(Term $term)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeTerm($term, $school);

        return view('school.terms.edit', [
            'school' => $school,
            'term' => $term,
            'academicSessions' => $this->academicSessionsForSchool($school),
        ]);
    }

    public function update(Request $request, Term $term)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeTerm($term, $school);

        $data = $request->validate([
            'academic_session_id' => [
                'required',
                Rule::exists('academic_sessions', 'id')
                    ->where('school_id', $school->id),
            ],
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('terms', 'name')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $request->input('academic_session_id'))
                    ->ignore($term->id),
            ],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if ($data['is_active']) {
            $school->terms()
                ->where('id', '!=', $term->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $term->update($data);

        return redirect()
            ->route('school.terms.index')
            ->with('success', 'Term updated successfully.');
    }

    public function activate(Request $request, Term $term, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeTerm($term, $school);

        if ($term->academicSession?->status === 'archived') {
            return back()->with('error', 'Archived session terms cannot be activated.');
        }

        $oldValues = $term->only(['status', 'is_active']);

        $school->terms()
            ->whereKeyNot($term->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $term->update([
            'status' => 'active',
            'is_active' => true,
        ]);

        $auditLog->log('term_activated', $term, $school, $oldValues, $term->only([
            'status',
            'is_active',
        ]), request: $request);

        return back()->with('success', 'Term activated successfully.');
    }

    public function archive(Request $request, Term $term, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeTerm($term, $school);

        $oldValues = $term->only(['status', 'is_active']);

        $term->update([
            'status' => 'archived',
            'is_active' => false,
        ]);

        $auditLog->log('term_archived', $term, $school, $oldValues, $term->only([
            'status',
            'is_active',
        ]), request: $request);

        return back()->with('success', 'Term archived. Historical results and report cards remain intact.');
    }

    public function restore(Request $request, Term $term, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeTerm($term, $school);

        $oldValues = $term->only(['status', 'is_active']);

        $term->update([
            'status' => 'inactive',
            'is_active' => false,
        ]);

        $auditLog->log('term_restored', $term, $school, $oldValues, $term->only([
            'status',
            'is_active',
        ]), request: $request);

        return back()->with('success', 'Term restored as inactive.');
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function academicSessionsForSchool(School $school)
    {
        return $school->academicSessions()
            ->where('status', 'active')
            ->latest()
            ->get();
    }

    private function authorizeTerm(Term $term, School $school): void
    {
        if ($term->school_id !== $school->id) {
            abort(403, 'You cannot access this term.');
        }
    }
}
