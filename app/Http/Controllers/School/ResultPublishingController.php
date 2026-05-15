<?php

namespace App\Http\Controllers\School;

use App\Enums\ResultWorkflowStatus;
use App\Events\StudentTransactionalEmailRequested;
use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\ResultPublication;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\Term;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ResultPublishingController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

        $publications = $school->resultPublications()
            ->with([
                'schoolClass',
                'academicSession',
                'term',
                'subject',
                'student',
                'publishedBy',
                'unpublishedBy',
                'createdBy',
            ])
            ->latest()
            ->paginate(10);

        return view('school.results.publishing.index', [
            'school' => $school,
            'classes' => $this->classesForSchool($school),
            'academicSessions' => $this->academicSessionsForSchool($school),
            'terms' => $this->termsForSchool($school),
            'subjects' => $this->subjectsForSchool($school),
            'students' => $this->studentsForSchool($school),
            'publications' => $publications,
        ]);
    }

    public function publish(Request $request)
    {
        $school = $this->currentSchoolOrFail();
        Gate::authorize('publish', [StudentResult::class, $school]);

        $data = $this->validatePublishingRequest($request, $school);

        $query = $this->matchingResultsQuery($school, $data)
            ->whereIn('status', ResultWorkflowStatus::publishableStudentResultValues());

        $totalResults = (clone $query)->count();

        if ($totalResults === 0) {
            return back()
                ->withInput()
                ->with('publishing_error', 'No reviewed, approved, or unpublished results were found for the selected class, session, term, and scope.');
        }

        $sourceStatuses = (clone $query)
            ->pluck('status')
            ->countBy()
            ->all();

        $studentIds = (clone $query)
            ->pluck('student_id')
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($query, $school, $data) {
            $now = now();

            $query->update([
                'status' => ResultWorkflowStatus::Published->value,
                'published_at' => $now,
                'published_by' => auth()->id(),
                'unpublished_at' => null,
                'unpublished_by' => null,
                'unpublish_reason' => null,
            ]);

            ResultPublication::create([
                'school_id' => $school->id,
                'school_class_id' => $data['school_class_id'],
                'academic_session_id' => $data['academic_session_id'],
                'term_id' => $data['term_id'],
                'result_type' => $data['result_type'],
                'scope_type' => $data['scope_type'],
                'subject_id' => $data['subject_id'] ?? null,
                'student_id' => $data['student_id'] ?? null,
                'status' => 'published',
                'scheduled_publish_at' => null,
                'published_at' => $now,
                'published_by' => auth()->id(),
                'unpublished_at' => null,
                'unpublished_by' => null,
                'unpublish_reason' => null,
                'created_by' => auth()->id(),
            ]);
        });

        app(AuditLogService::class)->log('result_published', null, $school, metadata: [
            'scope_type' => $data['scope_type'],
            'school_class_id' => $data['school_class_id'],
            'academic_session_id' => $data['academic_session_id'],
            'term_id' => $data['term_id'],
            'result_type' => $data['result_type'],
            'records' => $totalResults,
            'from_statuses' => $sourceStatuses,
            'to_status' => ResultWorkflowStatus::Published->value,
        ], request: $request);

        $this->notifyGuardians($school, $data, $studentIds);

        return back()->with('success', "Results published successfully. Total records affected: {$totalResults}.");
    }

    public function unpublish(Request $request)
    {
        $school = $this->currentSchoolOrFail();
        Gate::authorize('unpublish', [StudentResult::class, $school]);

        $data = $this->validateUnpublishingRequest($request, $school);

        $query = $this->matchingResultsQuery($school, $data)
            ->where('status', ResultWorkflowStatus::Published->value);

        $totalResults = (clone $query)->count();

        if ($totalResults === 0) {
            return back()
                ->withInput()
                ->with('publishing_error', 'No published results were found for the selected class, session, term, and scope.');
        }

        DB::transaction(function () use ($query, $school, $data) {
            $now = now();

            $query->update([
                'status' => ResultWorkflowStatus::Unpublished->value,
                'unpublished_at' => $now,
                'unpublished_by' => auth()->id(),
                'unpublish_reason' => $data['unpublish_reason'],
            ]);

            ResultPublication::create([
                'school_id' => $school->id,
                'school_class_id' => $data['school_class_id'],
                'academic_session_id' => $data['academic_session_id'],
                'term_id' => $data['term_id'],
                'result_type' => $data['result_type'],
                'scope_type' => $data['scope_type'],
                'subject_id' => $data['subject_id'] ?? null,
                'student_id' => $data['student_id'] ?? null,
                'status' => 'revoked',
                'scheduled_publish_at' => null,
                'published_at' => null,
                'published_by' => null,
                'unpublished_at' => $now,
                'unpublished_by' => auth()->id(),
                'unpublish_reason' => $data['unpublish_reason'],
                'created_by' => auth()->id(),
            ]);
        });

        app(AuditLogService::class)->log('result_unpublished', null, $school, metadata: [
            'scope_type' => $data['scope_type'],
            'school_class_id' => $data['school_class_id'],
            'academic_session_id' => $data['academic_session_id'],
            'term_id' => $data['term_id'],
            'result_type' => $data['result_type'],
            'records' => $totalResults,
            'reason' => $data['unpublish_reason'],
            'from_status' => ResultWorkflowStatus::Published->value,
            'to_status' => ResultWorkflowStatus::Unpublished->value,
        ], request: $request);

        return back()->with('success', "Results unpublished successfully. Total records affected: {$totalResults}.");
    }

    private function validatePublishingRequest(Request $request, School $school): array
    {
        return $request->validate([
            'school_class_id' => [
                'required',
                Rule::exists('school_classes', 'id')->where('school_id', $school->id),
            ],
            'academic_session_id' => [
                'required',
                Rule::exists('academic_sessions', 'id')->where('school_id', $school->id),
            ],
            'term_id' => [
                'required',
                Rule::exists('terms', 'id')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $request->input('academic_session_id')),
            ],
            'result_type' => ['required', Rule::in(['term_result'])],
            'scope_type' => ['required', Rule::in(['class', 'subject', 'student'])],
            'subject_id' => [
                Rule::requiredIf(fn () => $request->input('scope_type') === 'subject'),
                'nullable',
                Rule::exists('subjects', 'id')->where('school_id', $school->id),
            ],
            'student_id' => [
                Rule::requiredIf(fn () => $request->input('scope_type') === 'student'),
                'nullable',
                Rule::exists('students', 'id')
                    ->where('school_id', $school->id)
                    ->where('school_class_id', $request->input('school_class_id')),
            ],
        ]);
    }

    private function validateUnpublishingRequest(Request $request, School $school): array
    {
        $data = $this->validatePublishingRequest($request, $school);

        $reason = $request->validate([
            'unpublish_reason' => ['required', 'string', 'max:1000'],
        ]);

        return array_merge($data, $reason);
    }

    private function matchingResultsQuery(School $school, array $data)
    {
        $query = StudentResult::where('school_id', $school->id)
            ->where('school_class_id', $data['school_class_id'])
            ->where('academic_session_id', $data['academic_session_id'])
            ->where('term_id', $data['term_id'])
            ->where('result_type', $data['result_type']);

        if ($data['scope_type'] === 'subject') {
            $query->where('subject_id', $data['subject_id']);
        }

        if ($data['scope_type'] === 'student') {
            $query->where('student_id', $data['student_id']);
        }

        return $query;
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

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
            ->with('academicSession')
            ->where('status', 'active')
            ->latest()
            ->get();
    }

    private function subjectsForSchool(School $school)
    {
        return Subject::where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function studentsForSchool(School $school)
    {
        return Student::where('school_id', $school->id)
            ->with('schoolClass')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    private function notifyGuardians(School $school, array $data, array $studentIds): void
    {
        $academicSession = AcademicSession::where('school_id', $school->id)
            ->find($data['academic_session_id']);
        $term = Term::where('school_id', $school->id)
            ->find($data['term_id']);

        if (! $academicSession || ! $term || empty($studentIds)) {
            return;
        }

        Student::where('school_id', $school->id)
            ->whereIn('id', $studentIds)
            ->whereNotNull('guardian_email')
            ->with('school')
            ->chunkById(100, function ($students) use ($academicSession, $term, $data) {
                foreach ($students as $student) {
                    StudentTransactionalEmailRequested::dispatch(
                        StudentTransactionalEmailRequested::resultPublished($student, $academicSession, $term, $data)
                    );
                }
            });
    }
}
