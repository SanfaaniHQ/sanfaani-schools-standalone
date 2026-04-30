<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $students = $school->students()
            ->with('schoolClass')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($query) use ($search) {
                    $query->where('admission_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('guardian_phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('school.students.index', [
            'school' => $school,
            'students' => $students,
            'search' => $request->input('search'),
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.students.create', [
            'school' => $school,
            'classes' => $this->classesForSchool($school),
        ]);
    }

    public function store(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $data = $request->validate([
            'school_class_id' => [
                'nullable',
                Rule::exists('school_classes', 'id')
                    ->where('school_id', $school->id),
            ],
            'admission_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('students', 'admission_number')
                    ->where('school_id', $school->id),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'date_of_birth' => ['nullable', 'date'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_phone' => ['nullable', 'string', 'max:50'],
            'guardian_email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'withdrawn'])],
        ]);

        $data['school_id'] = $school->id;

        Student::create($data);

        return redirect()
            ->route('school.students.index')
            ->with('success', 'Student created successfully.');
    }

    public function edit(Student $student)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeStudent($student, $school);

        return view('school.students.edit', [
            'school' => $school,
            'student' => $student,
            'classes' => $this->classesForSchool($school),
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeStudent($student, $school);

        $data = $request->validate([
            'school_class_id' => [
                'nullable',
                Rule::exists('school_classes', 'id')
                    ->where('school_id', $school->id),
            ],
            'admission_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('students', 'admission_number')
                    ->where('school_id', $school->id)
                    ->ignore($student->id),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'date_of_birth' => ['nullable', 'date'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_phone' => ['nullable', 'string', 'max:50'],
            'guardian_email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'withdrawn'])],
        ]);

        $student->update($data);

        return redirect()
            ->route('school.students.index')
            ->with('success', 'Student updated successfully.');
    }

    private function currentSchoolOrFail(): School
    {
        $school = auth()->user()->school;

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function classesForSchool(School $school)
    {
        return $school->schoolClasses()
            ->where('status', 'active')
            ->orderBy('name')
            ->orderBy('section')
            ->get();
    }

    private function authorizeStudent(Student $student, School $school): void
    {
        if ($student->school_id !== $school->id) {
            abort(403, 'You cannot access this student.');
        }
    }
}
