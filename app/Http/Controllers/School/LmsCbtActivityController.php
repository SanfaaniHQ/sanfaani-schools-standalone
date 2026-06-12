<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\CbtExam;
use App\Models\LmsCbtActivity;
use App\Models\LmsClassroom;
use App\Models\LmsMaterial;
use App\Models\School;
use App\Services\CurrentSchoolService;
use App\Services\Lms\LmsCbtIntegrationService;
use Illuminate\Http\Request;

class LmsCbtActivityController extends Controller
{
    public function storeForClassroom(Request $request, LmsClassroom $classroom, LmsCbtIntegrationService $integration)
    {
        $school = $this->currentSchoolOrFail();
        $data = $this->validatedLinkData($request);
        $exam = CbtExam::findOrFail((int) $data['cbt_exam_id']);

        $integration->attachToClassroom($school, $request->user(), $classroom, $exam, $data);

        return back()->with('success', 'CBT activity linked to LMS classroom.');
    }

    public function storeForMaterial(Request $request, LmsMaterial $material, LmsCbtIntegrationService $integration)
    {
        $school = $this->currentSchoolOrFail();
        $data = $this->validatedLinkData($request);
        $exam = CbtExam::findOrFail((int) $data['cbt_exam_id']);

        $integration->attachToMaterial($school, $request->user(), $material, $exam, $data);

        return back()->with('success', 'CBT activity linked to LMS material.');
    }

    public function destroy(Request $request, LmsCbtActivity $activity, LmsCbtIntegrationService $integration)
    {
        $integration->archive($this->currentSchoolOrFail(), $request->user(), $activity);

        return back()->with('success', 'CBT activity unlinked from LMS.');
    }

    private function validatedLinkData(Request $request): array
    {
        return $request->validate([
            'cbt_exam_id' => ['required', 'integer', 'exists:cbt_exams,id'],
            'title' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:1000'],
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
