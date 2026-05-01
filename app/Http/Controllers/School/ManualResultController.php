<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\Term;
use App\Services\ResultGradingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ManualResultController extends Controller
{
    public function index(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $results = $school->studentResults()
            ->with(['student', 'schoolClass', 'subject', 'academicSession', 'term'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->whereHas('student', function ($query) use ($search) {
                    $query->where('admission_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('school.results.manual.index', [
            'school' => $school,
            'results' => $results,
            'search' => $request->input('search'),
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.results.manual.create', [
            'school' => $school,
            'students' => $this->studentsForSchool($school),
            'subjects' => $this->subjectsForSchool($school),
            'academicSessions' => $this->academicSessionsForSchool($school),
            'terms' => $this->termsForSchool($school),
        ]);
    }

    public function store(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $data = $this->validateResult($request, $school);

        $student = Student::where('school_id', $school->id)
            ->where('id', $data['student_id'])
            ->firstOrFail();

        $data['school_id'] = $school->id;
        $data['school_class_id'] = $student->school_class_id;
        $data['recorded_by'] = auth()->id();

        $data = $this->calculateResult($school, $data);

        StudentResult::create($data);

        return redirect()
            ->route('school.results.manual.index')
            ->with('success', 'Result recorded successfully.');
    }

    public function edit(StudentResult $studentResult)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeResult($studentResult, $school);

        return view('school.results.manual.edit', [
            'school' => $school,
            'studentResult' => $studentResult,
            'students' => $this->studentsForSchool($school),
            'subjects' => $this->subjectsForSchool($school),
            'academicSessions' => $this->academicSessionsForSchool($school),
            'terms' => $this->termsForSchool($school),
        ]);
    }

    public function update(Request $request, StudentResult $studentResult)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeResult($studentResult, $school);

        $data = $this->validateResult($request, $school, $studentResult->id);

        $student = Student::where('school_id', $school->id)
            ->where('id', $data['student_id'])
            ->firstOrFail();

        $data['school_class_id'] = $student->school_class_id;
        $data = $this->calculateResult($school, $data);

        $studentResult->update($data);

        return redirect()
            ->route('school.results.manual.index')
            ->with('success', 'Result updated successfully.');
    }

    private function validateResult(Request $request, School $school, ?int $ignoreId = null): array
    {
        return $request->validate([
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where('school_id', $school->id),
                Rule::unique('student_results', 'student_id')
                    ->where('school_id', $school->id)
                    ->where('subject_id', $request->input('subject_id'))
                    ->where('academic_session_id', $request->input('academic_session_id'))
                    ->where('term_id', $request->input('term_id'))
                    ->ignore($ignoreId),
            ],
            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id')->where('school_id', $school->id),
            ],
            'academic_session_id' => [
                'required',
                Rule::exists('academic_sessions', 'id')->where('school_id', $school->id),
            ],
            'term_id' => [
                'required',
                Rule::exists('terms', 'id')->where('school_id', $school->id),
            ],
            'ca_score' => ['required', 'numeric', 'min:0', 'max:40'],
            'exam_score' => ['required', 'numeric', 'min:0', 'max:60'],
            'teacher_remark' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['draft', 'reviewed'])],
        ]);
    }

    private function calculateResult(School $school, array $data): array
    {
        $total = (float) $data['ca_score'] + (float) $data['exam_score'];

        $grading = app(ResultGradingService::class)->calculate($school, $total);

        $data['total_score'] = $total;
        $data['grade'] = $grading['grade'];
        $data['remark'] = $grading['remark'];

        return $data;
    }

    private function currentSchoolOrFail(): School
    {
        $school = auth()->user()->school;

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizeResult(StudentResult $studentResult, School $school): void
    {
        if ($studentResult->school_id !== $school->id) {
            abort(403, 'You cannot access this result.');
        }
    }

    private function studentsForSchool(School $school)
    {
        return $school->students()
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    private function subjectsForSchool(School $school)
    {
        return Subject::where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function academicSessionsForSchool(School $school)
    {
        return AcademicSession::where('school_id', $school->id)
            ->where('status', 'active')
            ->latest()
            ->get();
    }

    private function termsForSchool(School $school)
    {
        return Term::where('school_id', $school->id)
            ->where('status', 'active')
            ->latest()
            ->get();
    }
}