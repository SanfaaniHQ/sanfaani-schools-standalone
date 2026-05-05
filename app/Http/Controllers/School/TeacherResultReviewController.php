<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\TeacherResultSubmission;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use App\Services\ResultGradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeacherResultReviewController extends Controller
{
    public function index(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $submissions = $school->teacherResultSubmissions()
            ->with(['teacher', 'schoolClass', 'subject', 'academicSession', 'term'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when(! $request->filled('status'), fn ($query) => $query->whereIn('status', ['submitted', 'returned', 'approved']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('school.result-reviews.index', [
            'school' => $school,
            'submissions' => $submissions,
            'statuses' => TeacherResultSubmission::STATUSES,
            'filters' => $request->only(['status']),
        ]);
    }

    public function show(TeacherResultSubmission $submission)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSubmission($submission, $school);

        return view('school.result-reviews.show', [
            'school' => $school,
            'submission' => $submission->load(['teacher', 'schoolClass', 'subject', 'academicSession', 'term']),
            'studentsById' => $this->studentsForSubmission($submission)->keyBy('id'),
            'scores' => collect($submission->metadata['scores'] ?? [])->keyBy('student_id'),
        ]);
    }

    public function update(Request $request, TeacherResultSubmission $submission, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSubmission($submission, $school);

        if (in_array($submission->status, ['published', 'voided'], true)) {
            return back()->with('error', 'Published or voided submissions cannot be edited.');
        }

        $scores = $this->validatedScores($request, $submission);
        $submission->update([
            'metadata' => ['scores' => $scores],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $auditLog->log('teacher_result_modified_by_admin', $submission, $school, metadata: [
            'rows' => count($scores),
        ], request: $request);

        return redirect()
            ->route('school.result-reviews.show', $submission)
            ->with('success', 'Teacher result scores updated for review.');
    }

    public function return(Request $request, TeacherResultSubmission $submission, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSubmission($submission, $school);
        $data = $request->validate(['return_reason' => ['required', 'string', 'max:1000']]);

        if (in_array($submission->status, ['published', 'voided'], true)) {
            return back()->with('error', 'Published or voided submissions cannot be returned.');
        }

        $submission->update([
            'status' => 'returned',
            'returned_by' => auth()->id(),
            'returned_at' => now(),
            'return_reason' => $data['return_reason'],
        ]);

        $auditLog->log('teacher_result_returned', $submission, $school, metadata: [
            'reason' => $data['return_reason'],
        ], request: $request);

        return back()->with('success', 'Result returned to teacher for correction.');
    }

    public function approve(Request $request, TeacherResultSubmission $submission, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSubmission($submission, $school);

        if (! in_array($submission->status, ['submitted', 'returned'], true)) {
            return back()->with('error', 'Only submitted or returned results can be approved.');
        }

        $submission->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $auditLog->log('teacher_result_approved', $submission, $school, metadata: [
            'school_class_id' => $submission->school_class_id,
            'subject_id' => $submission->subject_id,
        ], request: $request);

        return back()->with('success', 'Teacher result approved.');
    }

    public function publish(Request $request, TeacherResultSubmission $submission, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSubmission($submission, $school);

        if ($submission->status !== 'approved') {
            return back()->with('error', 'Approve this teacher result before publishing.');
        }

        $scores = $submission->metadata['scores'] ?? [];

        if ($scores === [] || ! $submission->subject_id) {
            return back()->with('error', 'This submission has no publishable scores.');
        }

        DB::transaction(function () use ($submission, $school, $scores) {
            foreach ($scores as $row) {
                $student = Student::where('school_id', $school->id)
                    ->where('school_class_id', $submission->school_class_id)
                    ->find($row['student_id']);

                if (! $student) {
                    continue;
                }

                $total = (float) $row['ca_score'] + (float) $row['exam_score'];
                $grading = app(ResultGradingService::class)->calculate($school, $total);

                StudentResult::updateOrCreate([
                    'school_id' => $school->id,
                    'student_id' => $student->id,
                    'subject_id' => $submission->subject_id,
                    'academic_session_id' => $submission->academic_session_id,
                    'term_id' => $submission->term_id,
                ], [
                    'school_class_id' => $submission->school_class_id,
                    'result_type' => $submission->result_type,
                    'ca_score' => $row['ca_score'],
                    'exam_score' => $row['exam_score'],
                    'total_score' => $total,
                    'grade' => $grading['grade'],
                    'remark' => $grading['remark'],
                    'teacher_remark' => $row['teacher_remark'] ?? null,
                    'status' => 'published',
                    'published_at' => now(),
                    'published_by' => auth()->id(),
                    'unpublished_at' => null,
                    'unpublished_by' => null,
                    'unpublish_reason' => null,
                    'recorded_by' => $submission->teacher_user_id,
                    'teacher_result_submission_id' => $submission->id,
                ]);
            }

            $submission->update([
                'status' => 'published',
                'published_by' => auth()->id(),
                'published_at' => now(),
            ]);
        });

        $auditLog->log('teacher_result_published', $submission, $school, metadata: [
            'rows' => count($scores),
        ], request: $request);

        return back()->with('success', 'Approved teacher result published successfully.');
    }

    public function voidSubmission(Request $request, TeacherResultSubmission $submission, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSubmission($submission, $school);

        if ($submission->status === 'published') {
            return back()->with('error', 'Published teacher submissions cannot be voided here. Unpublish related student results first.');
        }

        $submission->update(['status' => 'voided']);

        $auditLog->log('teacher_result_voided', $submission, $school, metadata: [
            'school_class_id' => $submission->school_class_id,
            'subject_id' => $submission->subject_id,
        ], request: $request);

        return back()->with('success', 'Teacher result submission voided safely.');
    }

    private function validatedScores(Request $request, TeacherResultSubmission $submission): array
    {
        $students = $this->studentsForSubmission($submission)->keyBy('id');
        $errors = [];
        $rows = [];

        foreach ($request->input('scores', []) as $studentId => $score) {
            $studentId = (int) $studentId;

            if (! $students->has($studentId)) {
                continue;
            }

            $ca = trim((string) ($score['ca_score'] ?? ''));
            $exam = trim((string) ($score['exam_score'] ?? ''));

            if ($ca === '' && $exam === '') {
                continue;
            }

            if (! is_numeric($ca) || (float) $ca < 0 || (float) $ca > 40) {
                $errors[] = $students[$studentId]->fullName().': CA score must be between 0 and 40.';
            }

            if (! is_numeric($exam) || (float) $exam < 0 || (float) $exam > 60) {
                $errors[] = $students[$studentId]->fullName().': exam score must be between 0 and 60.';
            }

            $rows[] = [
                'student_id' => $studentId,
                'ca_score' => round((float) $ca, 2),
                'exam_score' => round((float) $exam, 2),
                'teacher_remark' => trim((string) ($score['teacher_remark'] ?? '')),
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['scores' => implode(' ', $errors)]);
        }

        if ($rows === []) {
            throw ValidationException::withMessages(['scores' => 'Enter at least one complete student score.']);
        }

        return $rows;
    }

    private function studentsForSubmission(TeacherResultSubmission $submission)
    {
        return Student::where('school_id', $submission->school_id)
            ->where('school_class_id', $submission->school_class_id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    private function authorizeSubmission(TeacherResultSubmission $submission, School $school): void
    {
        if ((int) $submission->school_id !== (int) $school->id) {
            abort(403, 'You cannot access this result submission.');
        }
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }
}
