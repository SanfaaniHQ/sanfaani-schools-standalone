<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\ReportCardTemplate;
use App\Models\School;
use App\Services\CurrentSchoolService;
use App\Services\ReportCardService;
use App\Services\SchoolFeatureAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ReportCardSettingController extends Controller
{
    public function edit(ReportCardService $reportCards)
    {
        $school = $this->currentSchoolOrFail();
        $this->abortIfDisabled($school, 'report_card_customization');

        return view('school.report-card-settings.edit', [
            'school' => $school,
            'settings' => $reportCards->settingsFor($school),
            'templates' => ReportCardTemplate::where('status', 'active')->orderByDesc('is_default')->orderBy('name')->get(),
            'reportCard' => $reportCards->sampleDisplayData($school),
        ]);
    }

    public function update(Request $request, ReportCardService $reportCards)
    {
        $school = $this->currentSchoolOrFail();
        $this->abortIfDisabled($school, 'report_card_customization');

        $settings = $reportCards->settingsFor($school);

        $data = $request->validate([
            'report_card_template_id' => ['nullable', Rule::exists('report_card_templates', 'id')->where('status', 'active')],
            'primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'school_name_font' => ['nullable', Rule::in(['default', 'serif', 'sans', 'formal'])],
            'header_type' => ['required', Rule::in(['classic', 'centered', 'compact'])],
            'student_info_layout' => ['required', Rule::in(['two_column', 'single_column', 'compact'])],
            'result_table_style' => ['required', Rule::in(['standard', 'compact', 'bordered'])],
            'show_logo' => ['nullable', 'boolean'],
            'show_school_address' => ['nullable', 'boolean'],
            'show_school_phone' => ['nullable', 'boolean'],
            'show_school_email' => ['nullable', 'boolean'],
            'show_student_photo' => ['nullable', 'boolean'],
            'show_teacher_remark' => ['nullable', 'boolean'],
            'show_class_teacher' => ['nullable', 'boolean'],
            'show_head_teacher' => ['nullable', 'boolean'],
            'class_teacher_title' => ['nullable', 'string', 'max:100'],
            'head_teacher_title' => ['nullable', 'string', 'max:100'],
            'class_teacher_name' => ['nullable', 'string', 'max:150'],
            'head_teacher_name' => ['nullable', 'string', 'max:150'],
            'class_teacher_signature_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'head_teacher_signature_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'enable_auto_class_teacher_comment' => ['nullable', 'boolean'],
            'enable_auto_head_teacher_comment' => ['nullable', 'boolean'],
        ]);

        unset($data['class_teacher_signature_upload'], $data['head_teacher_signature_upload']);

        foreach ([
            'show_logo',
            'show_school_address',
            'show_school_phone',
            'show_school_email',
            'show_student_photo',
            'show_teacher_remark',
            'show_class_teacher',
            'show_head_teacher',
            'enable_auto_class_teacher_comment',
            'enable_auto_head_teacher_comment',
        ] as $booleanField) {
            $data[$booleanField] = $request->boolean($booleanField);
        }

        if ($request->hasFile('class_teacher_signature_upload')) {
            $this->deleteStoredFile($settings->class_teacher_signature_path);
            $data['class_teacher_signature_path'] = $request->file('class_teacher_signature_upload')
                ->store('report-cards/signatures', 'public');
        }

        if ($request->hasFile('head_teacher_signature_upload')) {
            $this->deleteStoredFile($settings->head_teacher_signature_path);
            $data['head_teacher_signature_path'] = $request->file('head_teacher_signature_upload')
                ->store('report-cards/signatures', 'public');
        }

        $settings->update($data);

        return redirect()
            ->route('school.report-card-settings.edit')
            ->with('success', 'Report card settings updated successfully.');
    }

    public function preview(ReportCardService $reportCards)
    {
        $school = $this->currentSchoolOrFail();
        $this->abortIfDisabled($school, 'report_card_basic');

        return view('school.report-card-settings.preview', [
            'school' => $school,
            'reportCard' => $reportCards->sampleDisplayData($school),
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

    private function abortIfDisabled(School $school, string $featureKey): void
    {
        if (app(SchoolFeatureAccessService::class)->isExplicitlyDisabled($school, $featureKey)) {
            abort(403, 'Report card settings are not enabled for this school plan.');
        }
    }

    private function deleteStoredFile(?string $path): void
    {
        if (! filled($path) || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
