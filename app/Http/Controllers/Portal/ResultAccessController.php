<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ResultAccessRequest;
use App\Services\CurrentSchoolService;
use App\Services\Portals\PortalResultAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResultAccessController extends Controller
{
    public function __construct(
        private PortalResultAccessService $portalResults,
        private CurrentSchoolService $currentSchool
    ) {}

    public function index(Request $request): View
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        return view('portal.results.index', array_merge(
            ['school' => $school],
            $this->portalResults->portalIndexData($request->user(), $school)
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $data = $request->validate([
            'student_id' => ['required', 'integer'],
            'academic_session_id' => ['required', 'integer'],
            'term_id' => ['required', 'integer'],
            'result_type' => ['required', Rule::in(['term_result'])],
            'access_method' => ['required', Rule::in([
                ResultAccessRequest::METHOD_MANUAL_APPROVAL,
                ResultAccessRequest::METHOD_PAYMENT_REQUEST,
                ResultAccessRequest::METHOD_SCRATCH_CARD,
            ])],
            'scratch_card_serial' => ['nullable', 'string', 'max:100'],
            'scratch_card_pin' => ['nullable', 'string', 'max:100'],
            'request_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['access_method'] === ResultAccessRequest::METHOD_SCRATCH_CARD) {
            $request->validate([
                'scratch_card_serial' => ['required', 'string', 'max:100'],
                'scratch_card_pin' => ['required', 'string', 'max:100'],
            ]);
        }

        $accessRequest = $this->portalResults->createAccessRequest(
            $request->user(),
            $school,
            $data,
            $request
        );

        if ($accessRequest->isApproved()) {
            return redirect()
                ->route('portal.results.show', ['resultAccessRequest' => $accessRequest->id])
                ->with('success', 'Result access approved. You can now view the published result.');
        }

        return redirect()
            ->route('portal.results.index')
            ->with('success', $accessRequest->status === ResultAccessRequest::STATUS_PENDING_PAYMENT
                ? 'Payment request submitted. The school will confirm and approve access.'
                : 'Result access request submitted. The school will review it.');
    }

    public function show(Request $request, int|string $resultAccessRequest): View
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $accessRequest = ResultAccessRequest::query()
            ->whereKey($resultAccessRequest)
            ->with(['student.schoolClass', 'academicSession', 'term', 'scratchCard', 'paymentTransaction'])
            ->firstOrFail();

        abort_if((int) $accessRequest->school_id !== (int) $school->id, 403);
        abort_if((int) $accessRequest->requester_user_id !== (int) $request->user()->id, 403);
        abort_if(! $accessRequest->isApproved(), 403);

        $results = $this->portalResults->resultsForApprovedRequest($accessRequest);

        abort_if($results->isEmpty(), 404);

        $totalScore = $results->sum(fn ($result) => (float) $result->total_score);
        $averageScore = $results->count() > 0 ? round($totalScore / $results->count(), 2) : 0;

        return view('portal.results.show', [
            'school' => $school,
            'accessRequest' => $accessRequest,
            'student' => $accessRequest->student,
            'academicSession' => $accessRequest->academicSession,
            'term' => $accessRequest->term,
            'results' => $results,
            'totalScore' => $totalScore,
            'averageScore' => $averageScore,
        ]);
    }
}
