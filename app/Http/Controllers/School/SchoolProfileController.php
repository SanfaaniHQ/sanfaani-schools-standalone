<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
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
            'default_language' => ['required', Rule::in(['en', 'fr', 'ar'])],
            'supports_rtl' => ['nullable', 'boolean'],
            'logo_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($data['logo_upload']);

        $data['supports_rtl'] = (bool) ($data['supports_rtl'] ?? false);

        if ($request->hasFile('logo_upload')) {
            $this->deleteStoredFile($school->logo);
            $data['logo'] = $request->file('logo_upload')->store('schools/logos', 'public');
        }

        $school->update($data);

        return redirect()
            ->route('school.profile.edit')
            ->with('success', 'School profile updated successfully.');
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(\App\Services\CurrentSchoolService::class)->get();

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
