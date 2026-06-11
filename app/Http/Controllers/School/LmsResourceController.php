<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\LmsMaterial;
use App\Models\LmsResource;
use App\Models\School;
use App\Services\CurrentSchoolService;
use App\Services\Lms\LmsResourceStorageService;
use Illuminate\Http\Request;

class LmsResourceController extends Controller
{
    public function store(Request $request, LmsMaterial $material, LmsResourceStorageService $resources)
    {
        $school = $this->currentSchoolOrFail();
        $data = $request->validate($resources->validationRules());

        $resources->store($school, $request->user(), $material, $data['resource']);

        return back()->with('success', 'Resource uploaded securely.');
    }

    public function download(Request $request, LmsResource $resource, LmsResourceStorageService $resources)
    {
        return $resources->download($this->currentSchoolOrFail(), $request->user(), $resource);
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }
}
