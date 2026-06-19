<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ResultSystemController extends Controller
{
    public function index(CurrentSchoolService $currentSchool)
    {
        $school = $this->currentSchoolOrFail($currentSchool);
        $resultSetting = $school->resultSetting()->firstOrCreate([
            'school_id' => $school->id,
        ]);

        return view('school.result-system.index', [
            'school' => $school,
            'resultSetting' => $resultSetting,
        ]);
    }

    public function update(Request $request, CurrentSchoolService $currentSchool, AuditLogService $audit)
    {
        $school = $this->currentSchoolOrFail($currentSchool);
        $data = $request->validate([
            'pass_mark' => ['required', 'numeric', 'min:0', 'max:1000'],
            'maximum_score' => ['required', 'numeric', 'min:1', 'max:1000'],
            'ca_max_score' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'exam_max_score' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'default_result_type' => ['required', Rule::in(['term_result'])],
            'require_all_subjects' => ['nullable', 'boolean'],
            'show_positions' => ['nullable', 'boolean'],
        ]);

        if ((float) $data['pass_mark'] > (float) $data['maximum_score']) {
            throw ValidationException::withMessages([
                'pass_mark' => 'The pass mark cannot be greater than the maximum score.',
            ]);
        }

        $old = $school->resultSetting?->only([
            'pass_mark',
            'maximum_score',
            'ca_max_score',
            'exam_max_score',
            'default_result_type',
            'require_all_subjects',
            'show_positions',
        ]) ?? [];

        $setting = $school->resultSetting()->updateOrCreate([
            'school_id' => $school->id,
        ], [
            'pass_mark' => $data['pass_mark'],
            'maximum_score' => $data['maximum_score'],
            'ca_max_score' => $data['ca_max_score'] ?? null,
            'exam_max_score' => $data['exam_max_score'] ?? null,
            'default_result_type' => $data['default_result_type'],
            'require_all_subjects' => (bool) ($data['require_all_subjects'] ?? false),
            'show_positions' => (bool) ($data['show_positions'] ?? false),
            'updated_by' => $request->user()?->id,
            'metadata' => [
                'source' => 'school_result_system',
            ],
        ]);

        $audit->log('school_result_settings_updated', $setting, $school, $old, $setting->only(array_keys($old ?: [
            'pass_mark' => true,
            'maximum_score' => true,
            'ca_max_score' => true,
            'exam_max_score' => true,
            'default_result_type' => true,
            'require_all_subjects' => true,
            'show_positions' => true,
        ])), [
            'school_id' => $school->id,
            'actor_id' => $request->user()?->id,
        ], request: $request);

        return back()->with('success', 'Result settings updated successfully.');
    }

    private function currentSchoolOrFail(CurrentSchoolService $currentSchool): School
    {
        $school = $currentSchool->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }
}
