<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\LmsClassroom;
use App\Models\School;
use App\Services\CurrentSchoolService;
use App\Services\Lms\LmsAccessService;
use App\Services\Lms\LmsCbtIntegrationService;
use App\Services\Lms\LmsClassroomService;
use App\Services\Lms\LmsMaterialService;
use App\Services\Lms\LmsResourceStorageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LmsClassroomController extends Controller
{
    public function index(Request $request, LmsDashboardController $dashboard)
    {
        return $dashboard($request, app(LmsAccessService::class), app(LmsClassroomService::class), app(\App\Services\Lms\LmsResourceStorageService::class));
    }

    public function store(Request $request, LmsClassroomService $classrooms)
    {
        $school = $this->currentSchoolOrFail();
        $data = $this->validatedClassroom($request, $school);

        $classroom = $classrooms->create($school, $request->user(), $data);

        return redirect()
            ->route('school.lms.classrooms.show', $classroom)
            ->with('success', 'LMS classroom created.');
    }

    public function show(
        Request $request,
        LmsClassroom $classroom,
        LmsAccessService $access,
        LmsMaterialService $materials,
        LmsResourceStorageService $resources,
        LmsCbtIntegrationService $cbtIntegration,
    ) {
        $school = $this->currentSchoolOrFail();
        abort_unless((int) $classroom->school_id === (int) $school->id, 403);
        abort_unless($access->canManageClassroom($request->user(), $school, $classroom), 403);

        $classroom->load(['schoolClass', 'subject', 'academicSession', 'term', 'topics' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')]);
        $cbtActivities = $cbtIntegration->classroomActivities($school, $classroom);

        return view('school.lms.classroom-show', [
            'school' => $school,
            'classroom' => $classroom,
            'materials' => $materials->materialsForClassroom($school, $request->user(), $classroom)->paginate(15),
            'publishedMaterials' => $materials->publishedMaterials($school, $classroom)->limit(6)->get(),
            'canManageSchool' => $access->canManageSchool($request->user(), $school),
            'allowedExtensions' => $resources->allowedExtensions(),
            'maxUploadMb' => $resources->maxUploadMb(),
            'cbtActivities' => $cbtActivities,
            'cbtActivityManagement' => $cbtActivities->mapWithKeys(fn ($activity) => [
                $activity->id => $cbtIntegration->canManageActivityLink($request->user(), $school, $activity),
            ]),
            'eligibleCbtExams' => $cbtIntegration->eligibleExamsForClassroom($school, $request->user(), $classroom),
            'canManageCbtLinks' => $cbtIntegration->canManageClassroomLinks($request->user(), $school, $classroom),
            'cbtAttachAction' => route('school.lms.classrooms.cbt.store', $classroom),
        ]);
    }

    public function update(Request $request, LmsClassroom $classroom, LmsClassroomService $classrooms)
    {
        $school = $this->currentSchoolOrFail();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:3000'],
            'status' => ['nullable', Rule::in([LmsClassroom::STATUS_ACTIVE, LmsClassroom::STATUS_ARCHIVED])],
        ]);

        $classrooms->update($school, $request->user(), $classroom, $data);

        return back()->with('success', 'LMS classroom updated.');
    }

    public function storeTopic(Request $request, LmsClassroom $classroom, LmsClassroomService $classrooms)
    {
        $school = $this->currentSchoolOrFail();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $classrooms->createTopic($school, $request->user(), $classroom, $data);

        return back()->with('success', 'Topic added.');
    }

    private function validatedClassroom(Request $request, School $school): array
    {
        return $request->validate([
            'school_class_id' => [
                'required',
                'integer',
                Rule::exists('school_classes', 'id')->where('school_id', $school->id),
            ],
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')->where('school_id', $school->id),
            ],
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
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:3000'],
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
}
