<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\CbtAttempt;
use App\Models\CbtExam;
use App\Services\CurrentSchoolService;

class CbtDashboardController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

        $examMetrics = CbtExam::query()
            ->where('school_id', $school->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status IN ('scheduled', 'open', 'published') THEN 1 ELSE 0 END) as active")
            ->selectRaw("SUM(CASE WHEN supports_public_candidates = 1 THEN 1 ELSE 0 END) as public_count")
            ->first();

        $attemptMetrics = CbtAttempt::query()
            ->where('school_id', $school->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status IN ('in_progress', 'resumed') THEN 1 ELSE 0 END) as in_progress")
            ->selectRaw("SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as needs_marking")
            ->selectRaw("SUM(CASE WHEN result_release_status = 'published' THEN 1 ELSE 0 END) as released")
            ->first();

        return view('school.cbt.dashboard', [
            'school' => $school,
            'examMetrics' => $examMetrics,
            'attemptMetrics' => $attemptMetrics,
            'recentExams' => $school->cbtExams()
                ->with(['subject', 'schoolClass'])
                ->withCount(['examQuestions', 'attempts'])
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    private function currentSchoolOrFail()
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }
}
