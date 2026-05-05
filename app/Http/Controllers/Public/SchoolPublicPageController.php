<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SchoolPublicPage;

class SchoolPublicPageController extends Controller
{
    public function show(string $slug)
    {
        $page = SchoolPublicPage::with('school')
            ->where('slug', $slug)
            ->first();

        if (! $page || ! $page->is_active || $page->school?->status !== 'active') {
            return response()->view('public.school-page.unavailable', status: 404);
        }

        return view('public.school-page.show', [
            'page' => $page,
            'school' => $page->school,
        ]);
    }
}
