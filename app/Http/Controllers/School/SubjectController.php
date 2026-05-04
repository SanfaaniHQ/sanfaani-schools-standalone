<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

        $subjects = $school->subjects()
            ->latest()
            ->paginate(10);

        return view('school.subjects.index', [
            'school' => $school,
            'subjects' => $subjects,
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
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['school_id'] = $school->id;

        Subject::create($data);

        return redirect()
            ->route('school.subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeSubject($subject, $school);

        return view('school.subjects.edit', [
            'school' => $school,
            'subject' => $subject,
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
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $subject->update($data);

        return redirect()
            ->route('school.subjects.index')
            ->with('success', 'Subject updated successfully.');
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
}
