<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\Student;
use App\Models\Term;
use App\Services\AuditLogService;
use App\Services\AdmissionNumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $students = $school->students()
            ->when($request->boolean('include_archived'), fn ($query) => $query->withTrashed())
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
            'includeArchived' => $request->boolean('include_archived'),
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

    public function show(Request $request, Student $student)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeStudent($student, $school);

        $student->load('schoolClass');

        $academicSessions = $school->academicSessions()
            ->where('status', 'active')
            ->latest()
            ->get();

        $selectedSession = $this->selectedAcademicSession($request, $school, $academicSessions);

        $terms = $school->terms()
            ->with('academicSession')
            ->where('status', 'active')
            ->when($selectedSession, fn ($query) => $query->where('academic_session_id', $selectedSession->id))
            ->latest()
            ->get();

        $selectedTerm = $this->selectedTerm($request, $school, $terms, $selectedSession);

        $resultsQuery = $student->results()
            ->where('school_id', $school->id)
            ->with(['subject', 'academicSession', 'term']);

        if ($selectedSession) {
            $resultsQuery->where('academic_session_id', $selectedSession->id);
        }

        if ($selectedTerm) {
            $resultsQuery->where('term_id', $selectedTerm->id);
        }

        $results = $resultsQuery
            ->get()
            ->sortBy(fn ($result) => $result->subject->name ?? '')
            ->values();

        return view('school.students.show', [
            'school' => $school,
            'student' => $student,
            'academicSessions' => $academicSessions,
            'terms' => $terms,
            'selectedSession' => $selectedSession,
            'selectedTerm' => $selectedTerm,
            'subjects' => $school->subjects()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'results' => $results,
            'totalResults' => $student->results()->where('school_id', $school->id)->count(),
            'publishedResults' => $student->results()->where('school_id', $school->id)->where('status', 'published')->count(),
            'reviewedResults' => $student->results()->where('school_id', $school->id)->where('status', 'reviewed')->count(),
            'draftResults' => $student->results()->where('school_id', $school->id)->where('status', 'draft')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        if ($request->boolean('auto_generate_admission_number')) {
            $request->merge(['admission_number' => null]);
        }

        $data = $request->validate([
            'school_class_id' => [
                'nullable',
                Rule::exists('school_classes', 'id')
                    ->where('school_id', $school->id),
            ],
            'admission_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('students', 'admission_number')
                    ->where('school_id', $school->id),
            ],
            'auto_generate_admission_number' => ['nullable', 'boolean'],
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

        $autoGenerate = $request->boolean('auto_generate_admission_number') || blank($data['admission_number'] ?? null);
        unset($data['auto_generate_admission_number']);

        DB::transaction(function () use ($school, &$data, $autoGenerate) {
            $data['school_id'] = $school->id;

            if ($autoGenerate) {
                $data['admission_number'] = app(AdmissionNumberGeneratorService::class)
                    ->generateForSchool($school);
            }

            Student::create($data);
        });

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

    public function destroy(Request $request, Student $student)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeStudent($student, $school);

        $student->update(['status' => 'inactive']);
        $student->delete();

        app(AuditLogService::class)->log('student_archived', $student, $school, metadata: [
            'admission_number' => $student->admission_number,
        ], request: $request);

        return redirect()
            ->route('school.students.index')
            ->with('success', 'Student archived safely. Results were preserved.');
    }

    public function restore(Request $request, int $student)
    {
        $school = $this->currentSchoolOrFail();

        $student = Student::onlyTrashed()
            ->where('school_id', $school->id)
            ->findOrFail($student);

        $student->restore();
        $student->update(['status' => 'active']);

        app(AuditLogService::class)->log('student_restored', $student, $school, metadata: [
            'admission_number' => $student->admission_number,
        ], request: $request);

        return redirect()
            ->route('school.students.index', ['include_archived' => 1])
            ->with('success', 'Student restored successfully.');
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

    private function selectedAcademicSession(Request $request, School $school, $academicSessions): ?AcademicSession
    {
        if ($request->filled('academic_session_id')) {
            return AcademicSession::where('school_id', $school->id)
                ->findOrFail((int) $request->input('academic_session_id'));
        }

        return $school->academicSessions()
            ->where('is_active', true)
            ->first()
            ?? $academicSessions->first();
    }

    private function selectedTerm(Request $request, School $school, $terms, ?AcademicSession $selectedSession): ?Term
    {
        if ($request->filled('term_id')) {
            return Term::where('school_id', $school->id)
                ->when($selectedSession, fn ($query) => $query->where('academic_session_id', $selectedSession->id))
                ->findOrFail((int) $request->input('term_id'));
        }

        return $school->terms()
            ->where('is_active', true)
            ->when($selectedSession, fn ($query) => $query->where('academic_session_id', $selectedSession->id))
            ->first()
            ?? $terms->first();
    }
}
