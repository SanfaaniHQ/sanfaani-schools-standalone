<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use App\Services\CurrentSchoolService;
use App\Services\SchoolAuthorizationService;
use App\Services\StudentResultWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentResultWorkspaceController extends Controller
{
    public function __invoke(
        Request $request,
        Student $student,
        StudentResultWorkspaceService $workspace
    ) {
        $school = $this->currentSchoolOrFail();

        $this->authorizeStudent($student, $school);

        $validated = $request->validate([
            'academic_session_id' => [
                'nullable',
                'integer',
                Rule::exists('academic_sessions', 'id')->where('school_id', $school->id),
            ],
            'term_id' => [
                'nullable',
                'integer',
                Rule::exists('terms', 'id')->where('school_id', $school->id),
            ],
            'result_type' => [
                'nullable',
                'string',
                Rule::in(array_keys(StudentResultWorkspaceService::RESULT_TYPES)),
            ],
            'class_enrollment_id' => [
                'nullable',
                'integer',
                Rule::exists('student_class_enrollments', 'id')
                    ->where('school_id', $school->id)
                    ->where('student_id', $student->id),
            ],
        ]);

        $filters = [
            'academic_session_id' => $validated['academic_session_id'] ?? null,
            'term_id' => $validated['term_id'] ?? null,
            'result_type' => $validated['result_type'] ?? 'term_result',
            'class_enrollment_id' => $validated['class_enrollment_id'] ?? null,
            '_has_academic_session_filter' => $request->query->has('academic_session_id'),
            '_has_term_filter' => $request->query->has('term_id'),
            '_has_class_enrollment_filter' => $request->query->has('class_enrollment_id'),
        ];

        return view('school.students.results.workspace', [
            'school' => $school,
            'student' => $student,
            'workspace' => $workspace->build($school, $student, $filters),
        ]);
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizeStudent(Student $student, School $school): void
    {
        $user = auth()->user();

        if (! $user || ! app(SchoolAuthorizationService::class)->canViewStudent($user, $school, $student)) {
            abort(403, 'You cannot access this student.');
        }
    }
}
