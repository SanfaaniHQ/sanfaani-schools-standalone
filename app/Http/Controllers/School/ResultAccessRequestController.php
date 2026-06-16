<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\ResultAccessRequest;
use App\Services\CurrentSchoolService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultAccessRequestController extends Controller
{
    public function __construct(
        private CurrentSchoolService $currentSchool
    ) {}

    public function index(Request $request): View
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $query = ResultAccessRequest::query()
            ->where('school_id', $school->id)
            ->with([
                'student.schoolClass',
                'requester',
                'academicSession',
                'term',
                'paymentTransaction',
                'scratchCard',
                'approvedBy',
                'rejectedBy',
            ])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('access_method')) {
            $query->where('access_method', $request->string('access_method'));
        }

        return view('school.result-access-requests.index', [
            'school' => $school,
            'requests' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'status' => $request->string('status')->toString(),
                'access_method' => $request->string('access_method')->toString(),
            ],
        ]);
    }

    public function approve(Request $request, int|string $resultAccessRequest): RedirectResponse
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $accessRequest = ResultAccessRequest::query()
            ->whereKey($resultAccessRequest)
            ->firstOrFail();

        abort_if((int) $accessRequest->school_id !== (int) $school->id, 403);

        $data = $request->validate([
            'decision_note' => ['nullable', 'string', 'max:1000'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $accessRequest->forceFill([
            'status' => ResultAccessRequest::STATUS_APPROVED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'expires_at' => now()->addDays((int) ($data['expires_in_days'] ?? 30)),
            'decision_note' => $data['decision_note'] ?? null,
        ])->save();

        if ($accessRequest->payment_transaction_id) {
            PaymentTransaction::query()
                ->whereKey($accessRequest->payment_transaction_id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'confirmed',
                    'paid_at' => now(),
                    'confirmed_by' => $request->user()->id,
                    'confirmed_at' => now(),
                ]);
        }

        return back()->with('success', 'Result access request approved successfully.');
    }

    public function reject(Request $request, int|string $resultAccessRequest): RedirectResponse
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $accessRequest = ResultAccessRequest::query()
            ->whereKey($resultAccessRequest)
            ->firstOrFail();

        abort_if((int) $accessRequest->school_id !== (int) $school->id, 403);

        $data = $request->validate([
            'decision_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $accessRequest->forceFill([
            'status' => ResultAccessRequest::STATUS_REJECTED,
            'rejected_by' => $request->user()->id,
            'rejected_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
            'expires_at' => null,
            'decision_note' => $data['decision_note'] ?? null,
        ])->save();

        return back()->with('success', 'Result access request rejected.');
    }
}
