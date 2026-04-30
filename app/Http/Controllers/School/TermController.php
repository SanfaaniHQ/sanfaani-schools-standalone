<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TermController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

        $terms = $school->terms()
            ->with('academicSession')
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

    private function currentSchoolOrFail(): School
    {
        $school = auth()->user()->school;

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
