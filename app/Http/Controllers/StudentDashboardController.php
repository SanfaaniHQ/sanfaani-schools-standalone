<?php

namespace App\Http\Controllers;

use App\Services\CurrentSchoolService;
use App\Services\LiveClasses\LiveClassAccessService;
use App\Services\LiveClasses\LiveClassService;
use App\Services\Portals\StudentPortalLinkService;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function __invoke(
        CurrentSchoolService $currentSchool,
        StudentPortalLinkService $portalLinks,
        LiveClassAccessService $liveClassAccess,
        LiveClassService $liveClasses
    ): View {
        $user = auth()->user();
        $school = $currentSchool->get($user);

        if (! $school) {
            return view('school.dashboard-not-assigned');
        }

        $student = $portalLinks->studentForUser($user, $school);
        $upcomingLiveClasses = $liveClassAccess->canView($user, $school)
            ? $liveClasses->sessionsForUser($school, $user, ['status' => \App\Models\LiveClass::STATUS_SCHEDULED])
                ->where('starts_at', '>=', now())
                ->limit(3)
                ->get()
            : collect();

        return view('student.dashboard', [
            'school' => $school,
            'student' => $student,
            'upcomingLiveClasses' => $upcomingLiveClasses,
        ]);
    }
}
