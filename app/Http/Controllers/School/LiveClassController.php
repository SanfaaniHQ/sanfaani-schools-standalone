<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\LiveClass;
use App\Models\LiveClassParticipant;
use App\Models\LmsMaterial;
use App\Models\School;
use App\Models\User;
use App\Services\CurrentSchoolService;
use App\Services\LiveClasses\LiveClassAccessService;
use App\Services\LiveClasses\LiveClassProviderRegistry;
use App\Services\LiveClasses\LiveClassService;
use App\Services\SchoolAuthorizationService;
use App\Services\TeacherAssignmentAccessService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LiveClassController extends Controller
{
    public function index(Request $request, LiveClassAccessService $access, LiveClassService $liveClasses, LiveClassProviderRegistry $providers)
    {
        $school = $this->currentSchoolOrFail();
        abort_unless($access->canView($request->user(), $school), 403);

        $filters = $request->only(['status', 'date_from', 'date_to']);

        return view('school.live-classes.index', [
            'school' => $school,
            'liveClasses' => $liveClasses->sessionsForUser($school, $request->user(), $filters)->paginate(15)->withQueryString(),
            'stats' => $liveClasses->summaryForUser($school, $request->user()),
            'statuses' => LiveClass::STATUSES,
            'filters' => $filters,
            'providerLabels' => $providers->labels(),
            'activeProvider' => $providers->detailsFor(),
            'futureProviders' => $providers->futureProviderSummaries(),
            'canManage' => $access->canView($request->user(), $school)
                && app(SchoolAuthorizationService::class)->canAny($request->user(), $school, ['live_classes.create', 'live_classes.manage']),
            'studentPortalSafe' => $access->studentPortalIsSafe(),
            'studentPortalBoundary' => $access->studentPortalBoundaryNote(),
            'parentPortalSafe' => $access->parentPortalIsSafe(),
            'parentPortalBoundary' => $access->parentPortalBoundaryNote(),
        ]);
    }

    public function create(Request $request, LiveClassAccessService $access, LiveClassProviderRegistry $providers)
    {
        $school = $this->currentSchoolOrFail();
        abort_unless($access->canView($request->user(), $school), 403);
        abort_unless(app(SchoolAuthorizationService::class)->canAny($request->user(), $school, ['live_classes.create', 'live_classes.manage']), 403);

        return view('school.live-classes.form', array_merge([
            'school' => $school,
            'liveClass' => new LiveClass([
                'provider' => LiveClass::PROVIDER_MANUAL,
                'status' => LiveClass::STATUS_SCHEDULED,
                'starts_at' => now()->addDay()->startOfHour(),
                'timezone' => config('app.timezone'),
            ]),
            'action' => route('school.live-classes.store'),
            'method' => 'POST',
        ], $this->formOptions($school, $request->user(), $access, $providers)));
    }

    public function store(Request $request, LiveClassService $liveClasses)
    {
        $school = $this->currentSchoolOrFail();
        $liveClass = $liveClasses->create($school, $request->user(), $this->validatedLiveClass($request, $school));

        return redirect()
            ->route('school.live-classes.show', $liveClass)
            ->with('success', 'Live class scheduled.');
    }

    public function show(Request $request, LiveClass $liveClass, LiveClassAccessService $access, LiveClassProviderRegistry $providers)
    {
        $school = $this->currentSchoolOrFail();
        $liveClass->load(['schoolClass', 'subject', 'academicSession', 'term', 'lmsClassroom', 'lmsMaterial', 'teacher', 'creator', 'participants.user']);
        abort_unless($access->canViewLiveClass($request->user(), $school, $liveClass), 403);

        return view('school.live-classes.show', [
            'school' => $school,
            'liveClass' => $liveClass,
            'provider' => $providers->detailsFor($liveClass->provider),
            'canManage' => $access->canManageLiveClass($request->user(), $school, $liveClass),
            'studentPortalBoundary' => $access->studentPortalBoundaryNote(),
            'parentPortalBoundary' => $access->parentPortalBoundaryNote(),
        ]);
    }

    public function edit(Request $request, LiveClass $liveClass, LiveClassAccessService $access, LiveClassProviderRegistry $providers)
    {
        $school = $this->currentSchoolOrFail();
        abort_unless($access->canManageLiveClass($request->user(), $school, $liveClass), 403);

        $liveClass->load(['schoolClass', 'subject', 'academicSession', 'term', 'lmsClassroom', 'lmsMaterial', 'teacher', 'participants.user']);

        return view('school.live-classes.form', array_merge([
            'school' => $school,
            'liveClass' => $liveClass,
            'action' => route('school.live-classes.update', $liveClass),
            'method' => 'PATCH',
        ], $this->formOptions($school, $request->user(), $access, $providers)));
    }

    public function update(Request $request, LiveClass $liveClass, LiveClassService $liveClasses)
    {
        $school = $this->currentSchoolOrFail();
        $liveClasses->update($school, $request->user(), $liveClass, $this->validatedLiveClass($request, $school));

        return redirect()
            ->route('school.live-classes.show', $liveClass)
            ->with('success', 'Live class updated.');
    }

    public function start(Request $request, LiveClass $liveClass, LiveClassService $liveClasses)
    {
        $liveClasses->start($this->currentSchoolOrFail(), $request->user(), $liveClass);

        return back()->with('success', 'Live class marked live.');
    }

    public function complete(Request $request, LiveClass $liveClass, LiveClassService $liveClasses)
    {
        $liveClasses->complete($this->currentSchoolOrFail(), $request->user(), $liveClass);

        return back()->with('success', 'Live class marked completed.');
    }

    public function cancel(Request $request, LiveClass $liveClass, LiveClassService $liveClasses)
    {
        $liveClasses->cancel($this->currentSchoolOrFail(), $request->user(), $liveClass);

        return back()->with('success', 'Live class cancelled.');
    }

    private function validatedLiveClass(Request $request, School $school): array
    {
        return $request->validate([
            'school_class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')->where('school_id', $school->id)],
            'academic_session_id' => ['nullable', 'integer', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => ['nullable', 'integer', Rule::exists('terms', 'id')->where('school_id', $school->id)],
            'lms_classroom_id' => ['nullable', 'integer', Rule::exists('lms_classrooms', 'id')->where('school_id', $school->id)],
            'lms_material_id' => ['nullable', 'integer', Rule::exists('lms_materials', 'id')->where('school_id', $school->id)],
            'teacher_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('school_id', $school->id)],
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:3000'],
            'provider' => ['nullable', 'string', 'max:50'],
            'meeting_url' => ['required', 'url', 'max:2048'],
            'meeting_password' => ['nullable', 'string', 'max:191'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'timezone' => ['nullable', 'timezone'],
            'recording_url' => ['nullable', 'url', 'max:2048'],
            'reminder_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'audience_type' => ['nullable', 'string', Rule::in(LiveClassParticipant::AUDIENCE_TYPES)],
            'selected_user_ids' => ['nullable', 'array', 'max:250'],
            'selected_user_ids.*' => ['integer', Rule::exists('users', 'id')],
        ]);
    }

    private function formOptions(School $school, User $user, LiveClassAccessService $access, LiveClassProviderRegistry $providers): array
    {
        $teacherAssignments = app(TeacherAssignmentAccessService::class);

        if ($access->canManageSchool($user, $school)) {
            $classes = $school->schoolClasses()->where('status', 'active')->orderBy('name')->orderBy('section')->get();
            $subjects = $school->subjects()->where('status', 'active')->orderBy('name')->get();
            $lmsClassrooms = $school->lmsClassrooms()->active()->with(['schoolClass', 'subject'])->orderBy('title')->get();
            $lmsMaterials = $school->lmsMaterials()->with('classroom')->orderBy('title')->get();
            $teachers = User::query()
                ->where('school_id', $school->id)
                ->whereHas('roles', fn ($query) => $query->where('name', 'teacher'))
                ->orderBy('name')
                ->get();
        } else {
            $classes = $teacherAssignments->classesForTeacher($school, $user);
            $classIds = $classes->pluck('id')->all();
            $subjects = $classes
                ->flatMap(fn ($class) => $teacherAssignments->subjectsForTeacher($school, $user, $class->id))
                ->unique('id')
                ->values();
            $lmsClassrooms = $school->lmsClassrooms()
                ->active()
                ->whereIn('school_class_id', $classIds ?: [0])
                ->with(['schoolClass', 'subject'])
                ->orderBy('title')
                ->get()
                ->filter(fn ($classroom) => $access->canManageClassSubject(
                    $user,
                    $school,
                    (int) $classroom->school_class_id,
                    (int) $classroom->subject_id,
                    $classroom->academic_session_id ? (int) $classroom->academic_session_id : null,
                    $classroom->term_id ? (int) $classroom->term_id : null
                ))
                ->values();
            $lmsMaterials = LmsMaterial::query()
                ->where('school_id', $school->id)
                ->whereIn('lms_classroom_id', $lmsClassrooms->pluck('id')->all() ?: [0])
                ->with('classroom')
                ->orderBy('title')
                ->get();
            $teachers = collect([$user]);
        }

        return [
            'schoolClasses' => $classes,
            'subjects' => $subjects,
            'academicSessions' => $school->academicSessions()->where('status', 'active')->latest()->get(),
            'terms' => $school->terms()->where('status', 'active')->with('academicSession')->latest()->get(),
            'lmsClassrooms' => $lmsClassrooms,
            'lmsMaterials' => $lmsMaterials,
            'teachers' => $teachers,
            'audienceTypes' => app(LiveClassService::class)->audienceTypeOptions(),
            'eligibleUsers' => app(LiveClassService::class)->eligibleAudienceUsers($school),
            'providerOptions' => $providers->selectableOptions(),
            'futureProviders' => $providers->futureProviderSummaries(),
            'activeProvider' => $providers->detailsFor(),
        ];
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
