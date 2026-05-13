<?php

namespace App\Http\Controllers\School;

use App\Events\StudentTransactionalEmailRequested;
use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\ClassSubjectAssignment;
use App\Models\School;
use App\Models\Student;
use App\Models\CommunicationLog;
use App\Models\Term;
use App\Services\AuditLogService;
use App\Services\AdmissionNumberGeneratorService;
use App\Services\StudentClassEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $school = $this->currentSchoolOrFail();
        $selectedAcademicSessionId = $request->filled('academic_session_id') ? $request->integer('academic_session_id') : null;
        $selectedClassId = $request->filled('school_class_id') ? $request->integer('school_class_id') : null;

        $students = $school->students()
            ->when($request->boolean('include_archived'), fn ($query) => $query->withTrashed())
            ->with(['schoolClass', 'currentEnrollment.schoolClass', 'currentEnrollment.academicSession'])
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
            ->when($selectedAcademicSessionId, function ($query) use ($selectedAcademicSessionId, $selectedClassId) {
                $query->whereHas('classEnrollments', function ($query) use ($selectedAcademicSessionId, $selectedClassId) {
                    $query->where('academic_session_id', $selectedAcademicSessionId)
                        ->when($selectedClassId, fn ($query) => $query->where('school_class_id', $selectedClassId));
                });
            })
            ->when(! $selectedAcademicSessionId && $selectedClassId, fn ($query) => $query->where('school_class_id', $selectedClassId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('school.students.index', [
            'school' => $school,
            'students' => $students,
            'search' => $request->input('search'),
            'includeArchived' => $request->boolean('include_archived'),
            'academicSessions' => $school->academicSessions()->latest()->get(),
            'classes' => $this->classesForSchool($school),
            'selectedAcademicSessionId' => $selectedAcademicSessionId,
            'selectedClassId' => $selectedClassId,
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

        $student->load([
            'schoolClass',
            'currentEnrollment.schoolClass',
            'currentEnrollment.academicSession',
            'currentEnrollment.startTerm',
            'currentEnrollment.endTerm',
            'classEnrollments.academicSession',
            'classEnrollments.schoolClass',
            'classEnrollments.startTerm',
            'classEnrollments.endTerm',
            'classEnrollments.createdBy',
            'classEnrollments.promotedFrom.schoolClass',
        ]);

        $academicSessions = $school->academicSessions()
            ->where('status', 'active')
            ->latest()
            ->get();

        $activeSession = $school->academicSessions()
            ->where('is_active', true)
            ->first();

        $selectedSession = $this->selectedAcademicSession($request, $school, $academicSessions, $activeSession);

        $terms = $school->terms()
            ->with('academicSession')
            ->where('status', 'active')
            ->when($selectedSession, fn ($query) => $query->where('academic_session_id', $selectedSession->id))
            ->latest()
            ->get();

        $activeTerm = $school->terms()
            ->where('is_active', true)
            ->when($selectedSession, fn ($query) => $query->where('academic_session_id', $selectedSession->id))
            ->first();

        $selectedTerm = $this->selectedTerm($request, $school, $terms, $selectedSession, $activeTerm);

        $resultsQuery = $this->studentResultsForContext($school, $student, $selectedSession, $selectedTerm)
            ->with(['subject', 'academicSession', 'term']);

        $results = $resultsQuery
            ->get()
            ->sortBy(fn ($result) => $result->subject->name ?? '')
            ->values();

        $subjects = $school->subjects()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $electiveSubjects = $student->electiveSubjects()
            ->with(['subject', 'academicSession', 'term'])
            ->latest()
            ->get();

        // Scratch card usage data
        $scratchCardUsages = $student->scratchCardUsages()
            ->with(['scratchCard', 'academicSession', 'term'])
            ->latest()
            ->limit(10)
            ->get();

        // Recent activity timeline (audit logs) - increased to 50 for comprehensive view
        $recentActivities = \App\Models\AuditLog::where('auditable_type', Student::class)
            ->where('auditable_id', $student->id)
            ->where('school_id', $school->id)
            ->with('user')
            ->latest()
            ->limit(50)
            ->get();

        $recentCommunications = collect();
        if (Schema::hasTable('communication_logs')) {
            $recentCommunications = CommunicationLog::where('school_id', $school->id)
                ->where(function ($query) use ($student) {
                    $query->where('recipient', $student->guardian_email)
                        ->orWhere('metadata->student_id', $student->id);
                })
                ->latest()
                ->limit(8)
                ->get();
        }

        // Calculate student age if date of birth exists
        $age = null;
        if ($student->date_of_birth) {
            $age = \Carbon\Carbon::parse($student->date_of_birth)->age;
        }

        // Get promotion history
        $promotionHistory = $student->promotionItems()
            ->with(['fromClass', 'toClass', 'fromSession', 'toSession', 'batch.createdBy'])
            ->latest()
            ->get();

        $summaryMetrics = $this->studentSummaryMetrics(
            $school,
            $student,
            $results,
            $subjects,
            $electiveSubjects,
            $promotionHistory,
            $selectedSession,
            $selectedTerm
        );

        return view('school.students.show', [
            'school' => $school,
            'student' => $student,
            'age' => $age,
            'academicSessions' => $academicSessions,
            'terms' => $terms,
            'selectedSession' => $selectedSession,
            'selectedTerm' => $selectedTerm,
            'activeSession' => $activeSession,
            'activeTerm' => $activeTerm,
            'subjects' => $subjects,
            'electiveSubjects' => $electiveSubjects,
            'results' => $results,
            'classEnrollments' => $student->classEnrollments
                ->sortByDesc(fn ($enrollment) => $enrollment->academicSession?->starts_at?->timestamp ?? $enrollment->id)
                ->values(),
            'promotionHistory' => $promotionHistory,
            'totalResults' => $summaryMetrics['result_stats']['total'],
            'publishedResults' => $summaryMetrics['result_stats']['published'],
            'reviewedResults' => $summaryMetrics['result_stats']['reviewed'],
            'draftResults' => $summaryMetrics['result_stats']['draft'],
            'unpublishedResults' => $summaryMetrics['result_stats']['unpublished'],
            'summaryMetrics' => $summaryMetrics,
            'scratchCardUsages' => $scratchCardUsages,
            'recentActivities' => $recentActivities,
            'recentCommunications' => $recentCommunications,
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
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'transferred', 'withdrawn'])],
        ]);

        $autoGenerate = $request->boolean('auto_generate_admission_number') || blank($data['admission_number'] ?? null);
        unset($data['auto_generate_admission_number']);

        $student = DB::transaction(function () use ($school, &$data, $autoGenerate) {
            $data['school_id'] = $school->id;

            if ($autoGenerate) {
                $data['admission_number'] = app(AdmissionNumberGeneratorService::class)
                    ->generateForSchool($school);
            }

            $student = Student::create($data);

            app(StudentClassEnrollmentService::class)->recordPlacement(
                $school,
                $student,
                $data['school_class_id'] ?? null,
                createdBy: auth()->id(),
                source: 'student_created'
            );

            return $student;
        });

        StudentTransactionalEmailRequested::dispatch(StudentTransactionalEmailRequested::studentCreated($student->loadMissing('school')));

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
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'transferred', 'withdrawn'])],
        ]);

        $classId = $data['school_class_id'] ?? null;
        unset($data['school_class_id']);

        DB::transaction(function () use ($school, $student, $data, $classId) {
            $student->update($data);

            $enrollments = app(StudentClassEnrollmentService::class);

            if (in_array($student->status, ['graduated', 'transferred', 'withdrawn'], true)) {
                $enrollments->closeOpenEnrollments(
                    $school,
                    $student,
                    $enrollments->activeTerm($school),
                    $student->status
                );

                return;
            }

            if ((int) $student->school_class_id !== (int) $classId || ($classId && ! $student->currentEnrollment)) {
                $enrollments->recordPlacement(
                    $school,
                    $student,
                    $classId,
                    createdBy: auth()->id(),
                    source: 'student_updated'
                );
            }
        });

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

        StudentTransactionalEmailRequested::dispatch(StudentTransactionalEmailRequested::studentArchived($student->loadMissing('school')));

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

        if ($student->school_class_id) {
            app(StudentClassEnrollmentService::class)->recordPlacement(
                $school,
                $student,
                $student->school_class_id,
                createdBy: auth()->id(),
                source: 'student_restored'
            );
        }

        app(AuditLogService::class)->log('student_restored', $student, $school, metadata: [
            'admission_number' => $student->admission_number,
        ], request: $request);

        return redirect()
            ->route('school.students.index', ['include_archived' => 1])
            ->with('success', 'Student restored successfully.');
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(\App\Services\CurrentSchoolService::class)->get();

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

    private function studentSummaryMetrics(
        School $school,
        Student $student,
        Collection $results,
        Collection $subjects,
        Collection $electiveSubjects,
        Collection $promotionHistory,
        ?AcademicSession $selectedSession,
        ?Term $selectedTerm
    ): array {
        $resultStats = $this->studentResultsForContext($school, $student, $selectedSession, $selectedTerm)
            ->selectRaw('COUNT(*) as total_results')
            ->selectRaw("SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_results")
            ->selectRaw("SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed_results")
            ->selectRaw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_results")
            ->selectRaw("SUM(CASE WHEN status <> 'published' THEN 1 ELSE 0 END) as unpublished_results")
            ->first();

        $latestResult = $this->studentResultsForContext($school, $student, $selectedSession, $selectedTerm)
            ->with(['subject:id,name', 'academicSession:id,name', 'term:id,name'])
            ->latest('updated_at')
            ->first();

        $contextElectives = $electiveSubjects
            ->filter(fn ($elective) => $elective->status === 'active')
            ->when($selectedSession, fn ($collection) => $collection->filter(
                fn ($elective) => (int) $elective->academic_session_id === (int) $selectedSession->id
                    || blank($elective->academic_session_id)
            ))
            ->when($selectedTerm, fn ($collection) => $collection->filter(
                fn ($elective) => (int) $elective->term_id === (int) $selectedTerm->id
                    || blank($elective->term_id)
            ))
            ->values();

        $currentClassId = $student->currentEnrollment?->school_class_id ?: $student->school_class_id;
        $classSubjectIds = collect();
        if ($currentClassId) {
            $classSubjectIds = ClassSubjectAssignment::query()
                ->where('school_id', $school->id)
                ->where(function ($query) use ($currentClassId) {
                    $query->whereNull('school_class_id')
                        ->orWhere('school_class_id', $currentClassId);
                })
                ->where('status', 'active')
                ->when($selectedSession, function ($query) use ($selectedSession) {
                    $query->where(function ($query) use ($selectedSession) {
                        $query->whereNull('academic_session_id')
                            ->orWhere('academic_session_id', $selectedSession->id);
                    });
                })
                ->when($selectedTerm, function ($query) use ($selectedTerm) {
                    $query->where(function ($query) use ($selectedTerm) {
                        $query->whereNull('term_id')
                            ->orWhere('term_id', $selectedTerm->id);
                    });
                })
                ->pluck('subject_id')
                ->filter()
                ->unique()
                ->values();
        }

        if ($classSubjectIds->isEmpty()) {
            $classSubjectIds = $subjects->pluck('id')->filter()->unique()->values();
        }

        $expectedSubjectIds = $classSubjectIds
            ->merge($contextElectives->pluck('subject_id'))
            ->filter()
            ->unique()
            ->values();

        $completedSubjectIds = $results->pluck('subject_id')
            ->filter()
            ->unique()
            ->values();

        $expectedSubjects = $expectedSubjectIds->count();
        $completedSubjects = $completedSubjectIds->count();
        $completionPercentage = $expectedSubjects > 0
            ? min(100, (int) round(($completedSubjects / $expectedSubjects) * 100))
            : 0;

        $latestPromotion = $promotionHistory->first();
        $promotionFromClass = $latestPromotion
            ? trim(($latestPromotion->fromClass?->name ?? '').' '.($latestPromotion->fromClass?->section ?? ''))
            : null;
        $promotionToClass = $latestPromotion
            ? trim(($latestPromotion->toClass?->name ?? '').' '.($latestPromotion->toClass?->section ?? ''))
            : null;
        $guardianChannels = collect([$student->guardian_phone, $student->guardian_email])
            ->filter(fn ($value) => filled($value))
            ->count();

        return [
            'result_stats' => [
                'total' => (int) ($resultStats->total_results ?? 0),
                'published' => (int) ($resultStats->published_results ?? 0),
                'reviewed' => (int) ($resultStats->reviewed_results ?? 0),
                'draft' => (int) ($resultStats->draft_results ?? 0),
                'unpublished' => (int) ($resultStats->unpublished_results ?? 0),
            ],
            'subjects' => [
                'total' => $expectedSubjects,
                'core' => $classSubjectIds->count(),
                'elective' => $contextElectives->pluck('subject_id')->filter()->unique()->count(),
            ],
            'completion' => [
                'completed' => $completedSubjects,
                'expected' => $expectedSubjects,
                'percentage' => $completionPercentage,
            ],
            'promotion' => [
                'label' => $latestPromotion ? str($latestPromotion->action)->replace('_', ' ')->title()->toString() : 'No promotion yet',
                'status' => $latestPromotion?->status,
                'detail' => $latestPromotion
                    ? trim(($promotionFromClass ?: 'Previous class').' to '.($promotionToClass ?: 'current class'))
                    : 'No class movement recorded',
                'percentage' => $latestPromotion ? 100 : 0,
            ],
            'guardian' => [
                'label' => $guardianChannels === 2 ? 'Complete' : ($guardianChannels === 1 ? 'Partial' : 'Missing'),
                'detail' => $student->guardian_name ?: 'No guardian name',
                'channels' => $guardianChannels,
                'percentage' => (int) round(($guardianChannels / 2) * 100),
            ],
            'last_result_update' => [
                'label' => $latestResult?->updated_at?->format('d M Y') ?? 'No result yet',
                'detail' => $latestResult
                    ? trim(($latestResult->subject?->name ?? 'Result').' - '.ucfirst($latestResult->status))
                    : 'No saved result records',
                'time' => $latestResult?->updated_at?->format('h:i A'),
                'percentage' => $latestResult ? 100 : 0,
            ],
        ];
    }

    private function studentResultsForContext(
        School $school,
        Student $student,
        ?AcademicSession $selectedSession,
        ?Term $selectedTerm
    ) {
        return $student->results()
            ->where('school_id', $school->id)
            ->when($selectedSession, fn ($query) => $query->where('academic_session_id', $selectedSession->id))
            ->when($selectedTerm, fn ($query) => $query->where('term_id', $selectedTerm->id));
    }

    private function authorizeStudent(Student $student, School $school): void
    {
        if ((int) $student->school_id !== (int) $school->id) {
            abort(403, 'You cannot access this student.');
        }
    }

    private function selectedAcademicSession(
        Request $request,
        School $school,
        $academicSessions,
        ?AcademicSession $activeSession
    ): ?AcademicSession
    {
        if ($request->filled('academic_session_id')) {
            return AcademicSession::where('school_id', $school->id)
                ->findOrFail((int) $request->input('academic_session_id'));
        }

        return $activeSession ?? $academicSessions->first();
    }

    private function selectedTerm(
        Request $request,
        School $school,
        $terms,
        ?AcademicSession $selectedSession,
        ?Term $activeTerm
    ): ?Term
    {
        if ($request->filled('term_id')) {
            return Term::where('school_id', $school->id)
                ->when($selectedSession, fn ($query) => $query->where('academic_session_id', $selectedSession->id))
                ->findOrFail((int) $request->input('term_id'));
        }

        return $activeTerm ?? $terms->first();
    }
}
