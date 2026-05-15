<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Public\ResultCheckerController;
use App\Models\School;
use Illuminate\Http\Request;

class PublicResultController extends Controller
{
    public function showForm(Request $request, string $slug)
    {
        $school = $this->resolveSchool($slug);
        $this->markSlugRoute($request, $slug);

        return app(ResultCheckerController::class)->index($request, $school);
    }

    public function identify(Request $request, string $slug)
    {
        $school = $this->resolveSchool($slug);
        $this->markSlugRoute($request, $slug);

        return app(ResultCheckerController::class)->identify($request, $school);
    }

    public function check(Request $request, string $slug)
    {
        $school = $this->resolveSchool($slug);
        $this->markSlugRoute($request, $slug);

        return app(ResultCheckerController::class)->check($request, $school);
    }

    private function resolveSchool(string $slug): School
    {
        return School::query()
            ->where('status', 'active')
            ->where('result_checker_slug', $slug)
            ->where('is_result_checker_enabled', true)
            ->firstOrFail();
    }

    private function markSlugRoute(Request $request, string $slug): void
    {
        $request->attributes->set('result_checker_slug', $slug);
    }
}
