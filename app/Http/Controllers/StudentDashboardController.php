<?php

namespace App\Http\Controllers;

use App\Services\CurrentSchoolService;
use App\Services\Portals\StudentPortalLinkService;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function __invoke(
        CurrentSchoolService $currentSchool,
        StudentPortalLinkService $portalLinks
    ): View {
        $user = auth()->user();
        $school = $currentSchool->get($user);

        if (! $school) {
            return view('school.dashboard-not-assigned');
        }

        $student = $portalLinks->studentForUser($user, $school);

        return view('student.dashboard', [
            'school' => $school,
            'student' => $student,
        ]);
    }
}