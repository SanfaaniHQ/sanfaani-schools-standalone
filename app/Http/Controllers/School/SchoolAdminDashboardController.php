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
    'totalSubjects' => $school->subjects()->count(),
    'totalSessions' => $school->academicSessions()->count(),
    'activeSession' => $school->academicSessions()->where('is_active', true)->first(),
    'totalTerms' => $school->terms()->count(),
    'activeTerm' => $school->terms()->where('is_active', true)->first(),
    'totalStudents' => $school->students()->count(),
]);
    }
}
