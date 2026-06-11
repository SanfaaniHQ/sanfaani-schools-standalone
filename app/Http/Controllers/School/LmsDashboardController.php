<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\LmsMaterial;
use App\Models\School;
use App\Services\CurrentSchoolService;
use App\Services\Lms\LmsAccessService;
use App\Services\Lms\LmsClassroomService;
use App\Services\Lms\LmsResourceStorageService;
use Illuminate\Http\Request;

class LmsDashboardController extends Controller
{
    public function __invoke(
        Request $request,
        LmsAccessService $access,
        LmsClassroomService $classrooms,
        LmsResourceStorageService $resources,
    ) {
        $school = $this->currentSchoolOrFail();
        abort_unless($access->canView($request->user(), $school), 403);

        $classroomRows = $classrooms->classroomsForUser($school, $request->user());

        return view('school.lms.index', [
            'school' => $school,
            'classrooms' => $classroomRows,
            'schoolClasses' => $school->schoolClasses()->where('status', 'active')->orderBy('name')->get(),
            'subjects' => $school->subjects()->where('status', 'active')->orderBy('name')->get(),
            'academicSessions' => $school->academicSessions()->where('status', 'active')->latest()->get(),
            'terms' => $school->terms()->where('status', 'active')->with('academicSession')->latest()->get(),
            'canManageSchool' => $access->canManageSchool($request->user(), $school),
            'studentPortalSafe' => $access->studentPortalIsSafe(),
            'studentPortalBoundary' => $access->studentPortalBoundaryNote(),
            'allowedExtensions' => $resources->allowedExtensions(),
            'maxUploadMb' => $resources->maxUploadMb(),
            'stats' => [
                'classrooms' => $classroomRows->count(),
                'materials' => $school->lmsMaterials()->count(),
                'published' => $school->lmsMaterials()->where('status', LmsMaterial::STATUS_PUBLISHED)->count(),
                'resources' => $school->lmsResources()->where('status', 'active')->count(),
            ],
        ]);
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
