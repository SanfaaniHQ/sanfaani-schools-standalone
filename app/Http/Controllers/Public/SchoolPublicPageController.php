<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SchoolPublicPage;
use App\Services\PlatformSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SchoolPublicPageController extends Controller
{
    public function show(string $slug)
    {
        return $this->render($slug);
    }

    public function admissions(string $slug)
    {
        return $this->render($slug, 'admissions');
    }

    public function contact(string $slug)
    {
        return $this->render($slug, 'contact');
    }

    public function results(Request $request, string $slug)
    {
        $page = $this->pageFor($slug);

        if (! $this->resultCheckerAvailable($page)) {
            return response()->view('public.school-page.unavailable', status: 404);
        }

        return app(ResultCheckerController::class)->index($request, $page->school, $page);
    }

    public function identifyResult(Request $request, string $slug)
    {
        $page = $this->pageFor($slug);

        if (! $this->resultCheckerAvailable($page)) {
            return response()->view('public.school-page.unavailable', status: 404);
        }

        return app(ResultCheckerController::class)->identify($request, $page->school, $page);
    }

    public function checkResult(Request $request, string $slug)
    {
        $page = $this->pageFor($slug);

        if (! $this->resultCheckerAvailable($page)) {
            return response()->view('public.school-page.unavailable', status: 404);
        }

        return app(ResultCheckerController::class)->check($request, $page->school, $page);
    }

    private function render(string $slug, string $section = 'home')
    {
        $page = $this->pageFor($slug);

        if (! $this->publicPageAvailable($page)) {
            return response()->view('public.school-page.unavailable', status: 404);
        }

        $websiteSetting = $page->school->websiteSetting;

        if ($section === 'admissions' && ! $websiteSetting?->admissions_enabled) {
            return response()->view('public.school-page.unavailable', status: 404);
        }

        if ($section === 'contact' && ! $websiteSetting?->contact_page_enabled) {
            return response()->view('public.school-page.unavailable', status: 404);
        }

        return view('public.school-page.show', [
            'page' => $page,
            'school' => $page->school,
            'websiteSetting' => $websiteSetting,
            'activeSection' => $section,
            'canonicalUrl' => $this->canonicalUrl($page, $section),
        ]);
    }

    private function pageFor(string $slug): ?SchoolPublicPage
    {
        $cacheKey = $this->cacheKey($slug);

        $cachedId = Cache::get($cacheKey);

        if (is_numeric($cachedId)) {
            return SchoolPublicPage::with(['school.websiteSetting'])
                ->find((int) $cachedId);
        }

        $page = SchoolPublicPage::with(['school.websiteSetting'])
            ->where('slug', $slug)
            ->first();

        if ($page) {
            Cache::put($cacheKey, $page->id, now()->addMinutes(10));
        }

        return $page;
    }

    private function publicPageAvailable(?SchoolPublicPage $page): bool
    {
        if (! $page || ! $page->is_active || $page->school?->status !== 'active') {
            return false;
        }

        return app(PlatformSettingService::class)->publicPagesEnabled();
    }

    private function resultCheckerAvailable(?SchoolPublicPage $page): bool
    {
        if (! $this->publicPageAvailable($page)) {
            return false;
        }

        if (! $page->result_checker_enabled || ! $page->school?->websiteSetting?->result_checker_enabled) {
            return false;
        }

        return app(PlatformSettingService::class)->publicResultCheckerEnabled();
    }

    private function canonicalUrl(SchoolPublicPage $page, string $section): string
    {
        return match ($section) {
            'admissions' => route('public.schools.admissions', $page->slug),
            'contact' => route('public.schools.contact', $page->slug),
            default => route('public.schools.show', $page->slug),
        };
    }

    public static function cacheKey(string $slug): string
    {
        return 'school_public_page:'.$slug;
    }
}
