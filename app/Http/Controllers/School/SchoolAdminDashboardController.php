<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;

class SchoolAdminDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $school = $user->school;

        if (! $school) {
            return view('school.dashboard-not-assigned');
        }

        return view('school.dashboard', [
            'school' => $school,
            'totalSchoolUsers' => $school->users()->count(),
            'totalClasses' => $school->schoolClasses()->count(),
        ]);
    }
}
