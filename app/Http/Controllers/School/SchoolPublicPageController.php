<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolPublicPage;
use App\Models\SchoolWebsiteSetting;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SchoolPublicPageController extends Controller
{
    public function edit()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.public-page.edit', [
            'school' => $school,
            'page' => $this->pageFor($school),
            'websiteSetting' => $this->websiteSettingFor($school),
            'websiteModes' => SchoolWebsiteSetting::MODES,
        ]);
    }

    public function update(Request $request, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $page = $this->pageFor($school);
        $websiteSetting = $this->websiteSettingFor($school);

        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:150', Rule::unique('school_public_pages', 'slug')->ignore($page->id)],
            'title' => ['nullable', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:100'],
            'whatsapp' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'logo_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'banner_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'website_mode' => ['required', Rule::in(SchoolWebsiteSetting::MODES)],
            'preferred_domain' => ['nullable', 'string', 'max:255'],
            'subdomain' => ['nullable', 'string', 'max:150'],
            'custom_domain' => ['nullable', 'string', 'max:255'],
            'homepage_enabled' => ['nullable', 'boolean'],
            'events_enabled' => ['nullable', 'boolean'],
            'announcements_enabled' => ['nullable', 'boolean'],
            'admissions_enabled' => ['nullable', 'boolean'],
            'contact_page_enabled' => ['nullable', 'boolean'],
        ]);

        $oldSlug = $page->slug;

        $pageData = [
            'slug' => Str::slug($validated['slug']),
            'title' => $validated['title'] ?? null,
            'headline' => $validated['headline'] ?? null,
            'description' => $validated['description'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'whatsapp' => $validated['whatsapp'] ?? null,
            'address' => $validated['address'] ?? null,
        ];

        foreach ([
            'logo_upload' => 'logo_path',
            'banner_upload' => 'banner_path',
        ] as $input => $attribute) {
            if (! $request->hasFile($input)) {
                continue;
            }

            $this->deleteStoredFile($page->{$attribute});
            $pageData[$attribute] = $request->file($input)->store('school-public-pages/'.$school->id, 'public');
        }

        $page->update($pageData);

        $websiteSetting->update([
            'website_mode' => $validated['website_mode'],
            'preferred_domain' => $validated['preferred_domain'] ?? null,
            'subdomain' => $validated['subdomain'] ?? null,
            'custom_domain' => $validated['custom_domain'] ?? null,
            'custom_domain_status' => filled($validated['custom_domain'] ?? null) ? 'pending_verification' : null,
            'homepage_enabled' => (bool) ($validated['homepage_enabled'] ?? false),
            'events_enabled' => (bool) ($validated['events_enabled'] ?? false),
            'announcements_enabled' => (bool) ($validated['announcements_enabled'] ?? false),
            'admissions_enabled' => (bool) ($validated['admissions_enabled'] ?? false),
            'contact_page_enabled' => (bool) ($validated['contact_page_enabled'] ?? false),
        ]);
        $this->forgetPublicPageCache($oldSlug, $page->slug);

        $auditLog->log('school_public_page_updated', $page, $school, metadata: [
            'slug' => $page->slug,
        ], request: $request);

        return back()->with('success', 'Public page settings saved successfully.');
    }

    private function pageFor(School $school): SchoolPublicPage
    {
        return SchoolPublicPage::firstOrCreate(['school_id' => $school->id], [
            'slug' => $this->uniqueSlug($school),
            'title' => $school->name,
            'headline' => $school->name.' Result Checker',
            'description' => 'Use this page to access school result checking services.',
            'contact_email' => $school->email,
            'contact_phone' => $school->phone,
            'address' => $school->address,
        ]);
    }

    private function uniqueSlug(School $school): string
    {
        $base = Str::slug($school->slug ?: $school->name) ?: 'school-'.$school->id;
        $slug = $base;
        $counter = 2;

        while (SchoolPublicPage::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function websiteSettingFor(School $school): SchoolWebsiteSetting
    {
        return SchoolWebsiteSetting::firstOrCreate(['school_id' => $school->id], [
            'website_mode' => 'result_link_only',
            'result_checker_enabled' => true,
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

    private function forgetPublicPageCache(?string ...$slugs): void
    {
        foreach (array_filter(array_unique($slugs)) as $slug) {
            Cache::forget(\App\Http\Controllers\Public\SchoolPublicPageController::cacheKey($slug));
        }
    }

    private function deleteStoredFile(?string $path): void
    {
        if (! filled($path) || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
