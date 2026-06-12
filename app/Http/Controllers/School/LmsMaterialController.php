<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\LmsClassroom;
use App\Models\LmsMaterial;
use App\Models\School;
use App\Services\CurrentSchoolService;
use App\Services\Lms\LmsAccessService;
use App\Services\Lms\LmsCbtIntegrationService;
use App\Services\Lms\LmsMaterialService;
use App\Services\Lms\LmsResourceStorageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LmsMaterialController extends Controller
{
    public function create(Request $request, LmsClassroom $classroom, LmsAccessService $access, LmsResourceStorageService $resources)
    {
        $school = $this->currentSchoolOrFail();
        abort_unless((int) $classroom->school_id === (int) $school->id, 403);
        abort_unless($access->canManageClassroom($request->user(), $school, $classroom), 403);

        $classroom->load(['schoolClass', 'subject', 'topics' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')]);

        return view('school.lms.material-form', [
            'school' => $school,
            'classroom' => $classroom,
            'material' => new LmsMaterial([
                'type' => LmsMaterial::TYPE_LESSON,
                'status' => LmsMaterial::STATUS_DRAFT,
            ]),
            'types' => LmsMaterial::TYPES,
            'allowedExtensions' => $resources->allowedExtensions(),
            'maxUploadMb' => $resources->maxUploadMb(),
        ]);
    }

    public function store(Request $request, LmsClassroom $classroom, LmsMaterialService $materials)
    {
        $school = $this->currentSchoolOrFail();
        $material = $materials->create($school, $request->user(), $classroom, $this->validatedMaterial($request, $school, $classroom));

        return redirect()
            ->route('school.lms.materials.show', $material)
            ->with('success', 'LMS material saved as draft.');
    }

    public function show(
        Request $request,
        LmsMaterial $material,
        LmsAccessService $access,
        LmsResourceStorageService $resources,
        LmsCbtIntegrationService $cbtIntegration
    ) {
        $school = $this->currentSchoolOrFail();
        $material->load(['classroom.schoolClass', 'classroom.subject', 'classroom.academicSession', 'classroom.term', 'topic', 'teacher', 'resources']);
        abort_unless($access->canViewMaterial($request->user(), $school, $material), 403);
        $cbtActivities = $cbtIntegration->materialActivities($school, $material);

        return view('school.lms.material-show', [
            'school' => $school,
            'material' => $material,
            'canManage' => $access->canManageMaterial($request->user(), $school, $material),
            'allowedExtensions' => $resources->allowedExtensions(),
            'maxUploadMb' => $resources->maxUploadMb(),
            'cbtActivities' => $cbtActivities,
            'cbtActivityManagement' => $cbtActivities->mapWithKeys(fn ($activity) => [
                $activity->id => $cbtIntegration->canManageActivityLink($request->user(), $school, $activity),
            ]),
            'eligibleCbtExams' => $cbtIntegration->eligibleExamsForMaterial($school, $request->user(), $material),
            'canManageCbtLinks' => $cbtIntegration->canManageMaterialLinks($request->user(), $school, $material),
            'cbtAttachAction' => route('school.lms.materials.cbt.store', $material),
        ]);
    }

    public function update(Request $request, LmsMaterial $material, LmsMaterialService $materials)
    {
        $school = $this->currentSchoolOrFail();
        $material->load('classroom');

        $materials->update($school, $request->user(), $material, $this->validatedMaterial($request, $school, $material->classroom));

        return back()->with('success', 'LMS material updated.');
    }

    public function publish(Request $request, LmsMaterial $material, LmsMaterialService $materials)
    {
        $materials->publish($this->currentSchoolOrFail(), $request->user(), $material);

        return back()->with('success', 'LMS material published.');
    }

    public function unpublish(Request $request, LmsMaterial $material, LmsMaterialService $materials)
    {
        $materials->unpublish($this->currentSchoolOrFail(), $request->user(), $material);

        return back()->with('success', 'LMS material unpublished.');
    }

    public function archive(Request $request, LmsMaterial $material, LmsMaterialService $materials)
    {
        $materials->archive($this->currentSchoolOrFail(), $request->user(), $material);

        return redirect()
            ->route('school.lms.classrooms.show', $material->lms_classroom_id)
            ->with('success', 'LMS material archived.');
    }

    private function validatedMaterial(Request $request, School $school, LmsClassroom $classroom): array
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 403);

        return $request->validate([
            'lms_topic_id' => [
                'nullable',
                'integer',
                Rule::exists('lms_topics', 'id')
                    ->where('school_id', $school->id)
                    ->where('lms_classroom_id', $classroom->id),
            ],
            'title' => ['required', 'string', 'max:191'],
            'body' => ['nullable', 'string', 'max:20000'],
            'type' => ['required', Rule::in(LmsMaterial::TYPES)],
            'visible_from' => ['nullable', 'date'],
            'visible_until' => ['nullable', 'date', 'after_or_equal:visible_from'],
            'due_at' => ['nullable', 'date'],
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
