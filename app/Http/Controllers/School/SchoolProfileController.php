<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SchoolProfileController extends Controller
{
    public function edit()
    {
        return view('school.profile.edit', [
            'school' => $this->currentSchoolOrFail(),
        ]);
    }

    public function update(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'sender_email' => ['nullable', 'email', 'max:255'],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'school_motto' => ['nullable', 'string', 'max:255'],
            'result_checker_slug' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('schools', 'result_checker_slug')->ignore($school->id),
            ],
            'is_result_checker_enabled' => ['nullable', 'boolean'],
            'custom_css' => ['nullable', 'string', 'max:10000'],
            'default_language' => ['required', Rule::in(config('sanfaani.supported_languages', ['en', 'ar', 'fr', 'yo', 'ha']))],
            'supports_rtl' => ['nullable', 'boolean'],
            'logo_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'favicon_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,ico', 'max:1024'],
            'login_background_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'report_header_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'email_logo_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset(
            $data['logo_upload'],
            $data['favicon_upload'],
            $data['login_background_upload'],
            $data['report_header_upload'],
            $data['email_logo_upload']
        );

        $data['supports_rtl'] = (bool) ($data['supports_rtl'] ?? false);
        $data['is_result_checker_enabled'] = (bool) ($data['is_result_checker_enabled'] ?? false);

        if ($request->hasFile('logo_upload')) {
            $this->deleteStoredFile($school->logo_path ?: $school->logo);
            $data['logo_path'] = $request->file('logo_upload')->store('schools/logos', 'public');
        }

        foreach ([
            'favicon_upload' => ['column' => 'favicon_path', 'directory' => 'schools/favicons'],
            'login_background_upload' => ['column' => 'login_background_path', 'directory' => 'schools/login-backgrounds'],
            'report_header_upload' => ['column' => 'report_header_path', 'directory' => 'schools/report-headers'],
            'email_logo_upload' => ['column' => 'email_logo_path', 'directory' => 'schools/email-logos'],
        ] as $field => $target) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $this->deleteStoredFile($school->{$target['column']});
            $data[$target['column']] = $request->file($field)->store($target['directory'], 'public');
        }

        $school->update($data);

        return redirect()
            ->route('school.profile.edit')
            ->with('success', 'School profile updated successfully.');
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function deleteStoredFile(?string $path): void
    {
        if (! filled($path) || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
