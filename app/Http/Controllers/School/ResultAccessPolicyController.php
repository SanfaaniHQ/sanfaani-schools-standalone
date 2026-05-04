<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SchoolResultAccessPolicy;
use App\Services\CurrentSchoolService;

class ResultAccessPolicyController extends Controller
{
    public function show(CurrentSchoolService $currentSchool)
    {
        $school = $currentSchool->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        $policy = SchoolResultAccessPolicy::with(['rules.academicSession', 'rules.term'])
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest()
            ->first();

        return view('school.result-access-policy.show', [
            'school' => $school,
            'policy' => $policy,
        ]);
    }
}
