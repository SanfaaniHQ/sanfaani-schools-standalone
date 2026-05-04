<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Services\CurrentSchoolService;

class ResultSystemController extends Controller
{
    public function index(CurrentSchoolService $currentSchool)
    {
        $school = $currentSchool->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return view('school.result-system.index', [
            'school' => $school,
        ]);
    }
}
