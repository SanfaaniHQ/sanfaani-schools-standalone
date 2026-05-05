<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentElectiveSubject;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentElectiveSubjectController extends Controller
{
    public function store(Request $request, Student $student, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeStudent($student, $school);

        $termRule = Rule::exists('terms', 'id')->where('school_id', $school->id);

        if ($request->filled('academic_session_id')) {
            $termRule->where('academic_session_id', $request->input('academic_session_id'));
        }

        $data = $request->validate([
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $school->id)],
            'school_class_id' => ['nullable', Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'academic_session_id' => ['nullable', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => ['nullable', $termRule],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $elective = StudentElectiveSubject::updateOrCreate([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'subject_id' => $data['subject_id'],
            'academic_session_id' => $data['academic_session_id'] ?? null,
            'term_id' => $data['term_id'] ?? null,
        ], $data + [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $data['school_class_id'] ?? $student->school_class_id,
        ]);

        $auditLog->log('student_elective_subject_saved', $elective, $school, metadata: [
            'student_id' => $student->id,
            'subject_id' => $data['subject_id'],
        ], request: $request);

        return back()->with('success', 'Elective subject saved successfully.');
    }

    public function destroy(Request $request, Student $student, StudentElectiveSubject $electiveSubject, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeStudent($student, $school);

        if ((int) $electiveSubject->student_id !== (int) $student->id || (int) $electiveSubject->school_id !== (int) $school->id) {
            abort(403, 'You cannot remove this elective subject.');
        }

        $electiveSubject->delete();

        $auditLog->log('student_elective_subject_removed', $student, $school, metadata: [
            'student_id' => $student->id,
            'subject_id' => $electiveSubject->subject_id,
        ], request: $request);

        return back()->with('success', 'Elective subject removed from this student.');
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(\App\Services\CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizeStudent(Student $student, School $school): void
    {
        if ((int) $student->school_id !== (int) $school->id) {
            abort(403, 'You cannot access this student.');
        }
    }
}
