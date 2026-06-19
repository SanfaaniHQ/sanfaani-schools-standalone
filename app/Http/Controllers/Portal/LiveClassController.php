<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\LiveClass;
use App\Services\CurrentSchoolService;
use App\Services\LiveClasses\LiveClassAccessService;
use App\Services\LiveClasses\LiveClassProviderRegistry;
use App\Services\LiveClasses\LiveClassService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveClassController extends Controller
{
    public function index(
        Request $request,
        CurrentSchoolService $currentSchool,
        LiveClassAccessService $access,
        LiveClassService $liveClasses,
        LiveClassProviderRegistry $providers
    ): View {
        $school = $this->schoolOrFail($request, $currentSchool);
        abort_unless($access->canView($request->user(), $school), 403);

        $filters = array_merge(['status' => LiveClass::STATUS_SCHEDULED], $request->only(['status', 'date_from', 'date_to']));

        return view('portal.live-classes.index', [
            'school' => $school,
            'liveClasses' => $liveClasses->sessionsForUser($school, $request->user(), $filters)->paginate(15)->withQueryString(),
            'statuses' => LiveClass::STATUSES,
            'filters' => $filters,
            'providerLabels' => $providers->labels(),
        ]);
    }

    public function show(
        Request $request,
        LiveClass $liveClass,
        CurrentSchoolService $currentSchool,
        LiveClassAccessService $access,
        LiveClassProviderRegistry $providers
    ): View {
        $school = $this->schoolOrFail($request, $currentSchool);
        $liveClass->load(['schoolClass', 'subject', 'academicSession', 'term', 'teacher', 'participants.user']);
        abort_unless($access->canViewLiveClass($request->user(), $school, $liveClass), 403);

        return view('portal.live-classes.show', [
            'school' => $school,
            'liveClass' => $liveClass,
            'provider' => $providers->detailsFor($liveClass->provider),
            'participant' => $liveClass->participants->firstWhere('user_id', $request->user()->id),
        ]);
    }

    public function join(
        Request $request,
        LiveClass $liveClass,
        CurrentSchoolService $currentSchool,
        LiveClassService $liveClasses
    ): RedirectResponse {
        $school = $this->schoolOrFail($request, $currentSchool);
        $liveClasses->markJoined($school, $request->user(), $liveClass);

        return redirect()->away($liveClass->meeting_url);
    }

    private function schoolOrFail(Request $request, CurrentSchoolService $currentSchool)
    {
        $school = $currentSchool->get($request->user());

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }
}
