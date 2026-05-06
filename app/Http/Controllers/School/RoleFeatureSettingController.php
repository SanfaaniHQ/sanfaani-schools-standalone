<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\SchoolRoleFeatureService;
use Illuminate\Http\Request;

class RoleFeatureSettingController extends Controller
{
    public function edit(SchoolRoleFeatureService $featureService)
    {
        $school = $this->currentSchoolOrFail();

        $resultOfficerFeatures = $featureService->getFeatures($school->id, 'result_officer');
        $teacherFeatures = $featureService->getFeatures($school->id, 'teacher');

        return view('school.role-features.edit', [
            'school' => $school,
            'resultOfficerFeatures' => $resultOfficerFeatures,
            'teacherFeatures' => $teacherFeatures,
        ]);
    }

    public function update(Request $request, SchoolRoleFeatureService $featureService)
    {
        $school = $this->currentSchoolOrFail();

        $data = $request->validate([
            'result_officer' => ['nullable', 'array'],
            'result_officer.*' => ['boolean'],
            'teacher' => ['nullable', 'array'],
            'teacher.*' => ['boolean'],
        ]);

        // Update Result Officer features
        $resultOfficerFeatureKeys = array_keys($featureService->getAvailableFeatures('result_officer'));
        foreach ($resultOfficerFeatureKeys as $key) {
            $enabled = isset($data['result_officer'][$key]) && $data['result_officer'][$key];
            $featureService->setFeature($school->id, 'result_officer', $key, $enabled);
        }

        // Update Teacher features
        $teacherFeatureKeys = array_keys($featureService->getAvailableFeatures('teacher'));
        foreach ($teacherFeatureKeys as $key) {
            $enabled = isset($data['teacher'][$key]) && $data['teacher'][$key];
            $featureService->setFeature($school->id, 'teacher', $key, $enabled);
        }

        return redirect()
            ->route('school.role-features.edit')
            ->with('success', 'Role feature access settings updated successfully.');
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(\App\Services\CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }
}
