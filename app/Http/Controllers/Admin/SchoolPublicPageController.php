<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolPublicPage;
use App\Models\SchoolWebsiteSetting;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SchoolPublicPageController extends Controller
{
    public function edit(School $school)
    {
        return view('admin.schools.public-page', [
            'school' => $school,
            'page' => $this->pageFor($school),
            'websiteSetting' => $this->websiteSettingFor($school),
            'websiteModes' => SchoolWebsiteSetting::MODES,
        ]);
    }

    public function update(Request $request, School $school, AuditLogService $auditLog)
    {
        $page = $this->pageFor($school);
        $websiteSetting = $this->websiteSettingFor($school);
        $data = $this->validated($request, $page);
        $oldSlug = $page->slug;

        foreach ([
            'logo_upload' => 'logo_path',
            'banner_upload' => 'banner_path',
        ] as $input => $attribute) {
            if (! $request->hasFile($input)) {
                continue;
            }

            $this->deleteStoredFile($page->{$attribute});
            $data['page'][$attribute] = $request->file($input)->store('school-public-pages/'.$school->id, 'public');
        }

        $page->update($data['page']);
        $websiteSetting->update($data['website']);
        $this->forgetPublicPageCache($oldSlug, $page->slug);

        $auditLog->log('school_public_page_updated', $page, $school, metadata: [
            'slug' => $page->slug,
            'is_active' => $page->is_active,
            'website_mode' => $websiteSetting->website_mode,
        ], request: $request);

        return back()->with('success', 'School public page settings saved successfully.');
    }

    private function validated(Request $request, SchoolPublicPage $page): array
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:150', Rule::unique('school_public_pages', 'slug')->ignore($page->id)],
            'is_active' => ['nullable', 'boolean'],
            'result_checker_enabled' => ['nullable', 'boolean'],
            'scratch_card_purchase_enabled' => ['nullable', 'boolean'],
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
            'website_enabled' => ['nullable', 'boolean'],
            'preferred_domain' => ['nullable', 'string', 'max:255'],
            'subdomain' => ['nullable', 'string', 'max:150'],
            'custom_domain' => ['nullable', 'string', 'max:255'],
            'homepage_enabled' => ['nullable', 'boolean'],
            'events_enabled' => ['nullable', 'boolean'],
            'announcements_enabled' => ['nullable', 'boolean'],
            'admissions_enabled' => ['nullable', 'boolean'],
            'contact_page_enabled' => ['nullable', 'boolean'],
        ]);

        return [
            'page' => [
                'slug' => Str::slug($validated['slug']),
                'is_active' => (bool) ($validated['is_active'] ?? false),
                'result_checker_enabled' => (bool) ($validated['result_checker_enabled'] ?? false),
                'scratch_card_purchase_enabled' => (bool) ($validated['scratch_card_purchase_enabled'] ?? false),
                'title' => $validated['title'] ?? null,
                'headline' => $validated['headline'] ?? null,
                'description' => $validated['description'] ?? null,
                'contact_email' => $validated['contact_email'] ?? null,
                'contact_phone' => $validated['contact_phone'] ?? null,
                'whatsapp' => $validated['whatsapp'] ?? null,
                'address' => $validated['address'] ?? null,
            ],
            'website' => [
                'website_mode' => $validated['website_mode'],
                'website_enabled' => (bool) ($validated['website_enabled'] ?? false),
                'result_checker_enabled' => (bool) ($validated['result_checker_enabled'] ?? false),
                'preferred_domain' => $validated['preferred_domain'] ?? null,
                'subdomain' => $validated['subdomain'] ?? null,
                'custom_domain' => $validated['custom_domain'] ?? null,
                'custom_domain_status' => filled($validated['custom_domain'] ?? null) ? 'pending_verification' : null,
                'homepage_enabled' => (bool) ($validated['homepage_enabled'] ?? false),
                'events_enabled' => (bool) ($validated['events_enabled'] ?? false),
                'announcements_enabled' => (bool) ($validated['announcements_enabled'] ?? false),
                'admissions_enabled' => (bool) ($validated['admissions_enabled'] ?? false),
                'contact_page_enabled' => (bool) ($validated['contact_page_enabled'] ?? false),
            ],
        ];
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

    private function websiteSettingFor(School $school): SchoolWebsiteSetting
    {
        return SchoolWebsiteSetting::firstOrCreate(['school_id' => $school->id], [
            'website_mode' => 'result_link_only',
            'result_checker_enabled' => true,
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
