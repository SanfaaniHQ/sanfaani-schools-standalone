<?php

namespace App\Services;

use App\Models\School;
use App\Models\ScratchCardBatch;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\SupportThread;
use App\Models\User;

class DashboardWidgetService
{
    public function __construct(
        private CurrentSchoolService $currentSchool,
        private ScratchAnalyticsService $scratchAnalytics
    ) {}

    public function forUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        if ($user->hasRole('super_admin')) {
            return $this->superAdminWidgets();
        }

        return $this->schoolWidgets($this->currentSchool->get($user));
    }

    private function superAdminWidgets(): array
    {
        $scratch = $this->scratchAnalytics->summary();

        return [
            'schools' => School::count(),
            'active_schools' => School::where('status', 'active')->count(),
            'users' => User::count(),
            'scratch_revenue' => $scratch['revenue'],
            'pending_scratch_requests' => $scratch['pending_requests'],
            'open_support_threads' => SupportThread::whereIn('status', ['open', 'pending'])->count(),
        ];
    }

    private function schoolWidgets(?School $school): array
    {
        if (! $school) {
            return [];
        }

        $scratch = $this->scratchAnalytics->summary($school->id);

        return [
            'students' => Student::where('school_id', $school->id)->count(),
            'published_results' => StudentResult::where('school_id', $school->id)->where('status', 'published')->count(),
            'scratch_cards_remaining' => $scratch['cards_unused'],
            'scratch_usage_last_30_days' => $scratch['usage_last_30_days'],
            'pending_scratch_requests' => ScratchCardBatch::where('school_id', $school->id)
                ->whereIn('status', ['pending_payment', 'pending_approval'])
                ->count(),
            'open_support_threads' => SupportThread::where('school_id', $school->id)->whereIn('status', ['open', 'pending'])->count(),
        ];
    }
}
