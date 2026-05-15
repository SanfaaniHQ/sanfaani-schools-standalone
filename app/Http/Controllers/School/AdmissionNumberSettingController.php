<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\AdmissionNumberSetting;
use App\Models\School;
use App\Services\AdmissionNumberGeneratorService;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdmissionNumberSettingController extends Controller
{
    public function edit(AdmissionNumberGeneratorService $generator)
    {
        $school = $this->currentSchoolOrFail();
        $setting = $this->settingForSchool($school);

        return view('school.admission-number-settings.edit', [
            'school' => $school,
            'setting' => $setting,
            'preview' => $generator->previewForSetting($setting),
        ]);
    }

    public function update(Request $request, AdmissionNumberGeneratorService $generator)
    {
        $school = $this->currentSchoolOrFail();
        $setting = $this->settingForSchool($school);

        $data = $request->validate([
            'prefix' => ['nullable', 'string', 'max:30'],
            'separator' => ['required', 'string', 'max:10'],
            'year_format' => ['nullable', 'string', 'max:20'],
            'next_number' => ['required', 'integer', 'min:1', 'max:999999999'],
            'padding_length' => ['required', 'integer', 'min:1', 'max:10'],
            'suffix' => ['nullable', 'string', 'max:30'],
            'reset_cycle' => ['required', Rule::in(['never', 'yearly', 'session'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['prefix'] = filled($data['prefix'] ?? null) ? strtoupper(trim($data['prefix'])) : null;
        $data['suffix'] = filled($data['suffix'] ?? null) ? strtoupper(trim($data['suffix'])) : null;
        $data['year_format'] = filled($data['year_format'] ?? null) ? trim($data['year_format']) : 'Y';

        $setting->update($data);

        return redirect()
            ->route('school.admission-number-settings.edit')
            ->with('success', 'Admission number settings updated successfully.')
            ->with('preview', $generator->previewForSetting($setting->fresh()));
    }

    private function settingForSchool(School $school): AdmissionNumberSetting
    {
        return AdmissionNumberSetting::firstOrCreate(
            ['school_id' => $school->id],
            [
                'prefix' => $this->defaultPrefix($school),
                'separator' => '/',
                'year_format' => 'Y',
                'next_number' => 1,
                'padding_length' => 3,
                'status' => 'active',
            ]
        );
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function defaultPrefix(School $school): string
    {
        return app(AdmissionNumberGeneratorService::class)->defaultPrefixForSchool($school);
    }
}
