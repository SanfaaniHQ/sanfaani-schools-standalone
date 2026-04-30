<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolClassController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

        $classes = $school->schoolClasses()
            ->latest()
            ->paginate(10);

        return view('school.classes.index', [
            'school' => $school,
            'classes' => $classes,
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.classes.create', [
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
                'max:100',
                Rule::unique('school_classes', 'name')
                    ->where('school_id', $school->id)
                    ->where('section', $request->input('section')),
            ],
            'section' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['school_id'] = $school->id;

        SchoolClass::create($data);

        return redirect()
            ->route('school.classes.index')
            ->with('success', 'Class created successfully.');
    }

    public function edit(SchoolClass $class)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeSchoolClass($class, $school);

        return view('school.classes.edit', [
            'school' => $school,
            'class' => $class,
        ]);
    }

    public function update(Request $request, SchoolClass $class)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeSchoolClass($class, $school);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('school_classes', 'name')
                    ->where('school_id', $school->id)
                    ->where('section', $request->input('section'))
                    ->ignore($class->id),
            ],
            'section' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $class->update($data);

        return redirect()
            ->route('school.classes.index')
            ->with('success', 'Class updated successfully.');
    }

    private function currentSchoolOrFail(): School
    {
        $school = auth()->user()->school;

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizeSchoolClass(SchoolClass $class, School $school): void
    {
        if ($class->school_id !== $school->id) {
            abort(403, 'You cannot access this class.');
        }
    }
}
