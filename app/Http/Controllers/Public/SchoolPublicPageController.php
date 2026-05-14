<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SchoolPublicPage;
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

    private function render(string $slug, string $section = 'home')
    {
        $page = $this->pageFor($slug);

        if (! $page || ! $page->is_active || $page->school?->status !== 'active') {
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

        if ($page = Cache::get($cacheKey)) {
            return $page;
        }

        $page = SchoolPublicPage::with(['school.websiteSetting'])
            ->where('slug', $slug)
            ->first();

        if ($page) {
            Cache::put($cacheKey, $page, now()->addMinutes(10));
        }

        return $page;
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
