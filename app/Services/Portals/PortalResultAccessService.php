<?php

namespace App\Services\Portals;

use App\Models\AcademicSession;
use App\Models\PaymentTransaction;
use App\Models\ResultAccessRequest;
use App\Models\School;
use App\Models\ScratchCard;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Term;
use App\Models\User;
use App\Services\ScratchCardAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PortalResultAccessService
{
    public function __construct(
        private StudentPortalLinkService $studentPortalLinks,
        private ScratchCardAccessService $scratchCardAccess
    ) {}

    public function studentsForUser(User $user, School $school): Collection
    {
        if ($user->hasRole('student')) {
            $student = $this->studentPortalLinks->studentForUser($user, $school);

            return $student ? collect([$student]) : collect();
        }

        if ($user->hasRole('parent')) {
            return $this->studentPortalLinks->childrenForParent($user, $school);
        }

        return collect();
    }

    public function canUserAccessStudent(User $user, Student $student, School $school): bool
    {
        if ((int) $student->school_id !== (int) $school->id) {
            return false;
        }

        if ($user->hasRole('student')) {
            return (int) $student->student_user_id === (int) $user->id;
        }

        if ($user->hasRole('parent')) {
            return $student->parentUsers()
                ->where('users.id', $user->id)
                ->wherePivot('school_id', $school->id)
                ->wherePivot('can_view_results', true)
                ->exists();
        }

        return false;
    }

    public function publishedResultGroupsFor(Student $student, School $school): Collection
    {
        $rows = StudentResult::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->whereNull('unpublished_at')
            ->selectRaw('academic_session_id, term_id, result_type, COUNT(*) as results_count, MAX(published_at) as latest_published_at')
            ->groupBy('academic_session_id', 'term_id', 'result_type')
            ->orderByDesc('latest_published_at')
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $sessions = AcademicSession::query()
            ->where('school_id', $school->id)
            ->whereIn('id', $rows->pluck('academic_session_id')->unique())
            ->get()
            ->keyBy('id');

        $terms = Term::query()
            ->where('school_id', $school->id)
            ->whereIn('id', $rows->pluck('term_id')->unique())
            ->get()
            ->keyBy('id');

        return $rows
            ->map(function ($row) use ($sessions, $terms) {
                return [
                    'academic_session_id' => (int) $row->academic_session_id,
                    'term_id' => (int) $row->term_id,
                    'result_type' => $row->result_type ?: 'term_result',
                    'results_count' => (int) $row->results_count,
                    'latest_published_at' => $row->latest_published_at,
                    'academic_session' => $sessions->get((int) $row->academic_session_id),
                    'term' => $terms->get((int) $row->term_id),
                ];
            })
            ->filter(fn ($row) => $row['academic_session'] && $row['term'])
            ->values();
    }

    public function portalIndexData(User $user, School $school): array
    {
        $students = $this->studentsForUser($user, $school);

        $students = $students->map(function (Student $student) use ($user, $school) {
            $groups = $this->publishedResultGroupsFor($student, $school)
                ->map(function (array $group) use ($user, $student, $school) {
                    $request = $this->latestRequestFor(
                        $user,
                        $student,
                        $school,
                        $group['academic_session_id'],
                        $group['term_id'],
                        $group['result_type']
                    );

                    $group['latest_request'] = $request;
                    $group['approved_request'] = $this->approvedRequestFor(
                        $user,
                        $student,
                        $school,
                        $group['academic_session_id'],
                        $group['term_id'],
                        $group['result_type']
                    );

                    return $group;
                });

            $student->setRelation('portalResultGroups', $groups);

            return $student;
        });

        return [
            'students' => $students,
            'summary' => [
                'students' => $students->count(),
                'published_groups' => $students->sum(fn ($student) => $student->portalResultGroups->count()),
                'approved' => $students->sum(fn ($student) => $student->portalResultGroups->filter(fn ($group) => $group['approved_request'])->count()),
                'pending' => ResultAccessRequest::query()
                    ->where('school_id', $school->id)
                    ->where('requester_user_id', $user->id)
                    ->whereIn('status', [ResultAccessRequest::STATUS_PENDING, ResultAccessRequest::STATUS_PENDING_PAYMENT])
                    ->count(),
            ],
        ];
    }

    public function createAccessRequest(User $user, School $school, array $data, Request $httpRequest): ResultAccessRequest
    {
        $student = Student::query()
            ->where('school_id', $school->id)
            ->findOrFail($data['student_id']);

        if (! $this->canUserAccessStudent($user, $student, $school)) {
            abort(403, 'You are not allowed to request result access for this student.');
        }

        $academicSession = AcademicSession::query()
            ->where('school_id', $school->id)
            ->findOrFail($data['academic_session_id']);

        $term = Term::query()
            ->where('school_id', $school->id)
            ->where('academic_session_id', $academicSession->id)
            ->findOrFail($data['term_id']);

        $resultType = $data['result_type'] ?? 'term_result';

        if (! $this->hasPublishedResults($school, $student, $academicSession, $term, $resultType)) {
            throw ValidationException::withMessages([
                'student_id' => 'No published result was found for this selection.',
            ]);
        }

        $approved = $this->approvedRequestFor($user, $student, $school, $academicSession->id, $term->id, $resultType);

        if ($approved) {
            return $approved;
        }

        $existing = $this->latestRequestFor($user, $student, $school, $academicSession->id, $term->id, $resultType);

        if ($existing && in_array($existing->status, [ResultAccessRequest::STATUS_PENDING, ResultAccessRequest::STATUS_PENDING_PAYMENT], true)) {
            return $existing;
        }

        $method = $data['access_method'] ?? ResultAccessRequest::METHOD_MANUAL_APPROVAL;

        return match ($method) {
            ResultAccessRequest::METHOD_SCRATCH_CARD => $this->createScratchCardAccessRequest($user, $school, $student, $academicSession, $term, $resultType, $data, $httpRequest),
            ResultAccessRequest::METHOD_PAYMENT_REQUEST => $this->createPaymentAccessRequest($user, $school, $student, $academicSession, $term, $resultType, $data, $httpRequest),
            default => $this->createManualAccessRequest($user, $school, $student, $academicSession, $term, $resultType, $data, $httpRequest),
        };
    }

    public function approvedRequestFor(
        User $user,
        Student $student,
        School $school,
        int $academicSessionId,
        int $termId,
        string $resultType
    ): ?ResultAccessRequest {
        return ResultAccessRequest::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('requester_user_id', $user->id)
            ->where('academic_session_id', $academicSessionId)
            ->where('term_id', $termId)
            ->where('result_type', $resultType)
            ->where('status', ResultAccessRequest::STATUS_APPROVED)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->latest()
            ->first();
    }

    public function latestRequestFor(
        User $user,
        Student $student,
        School $school,
        int $academicSessionId,
        int $termId,
        string $resultType
    ): ?ResultAccessRequest {
        return ResultAccessRequest::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('requester_user_id', $user->id)
            ->where('academic_session_id', $academicSessionId)
            ->where('term_id', $termId)
            ->where('result_type', $resultType)
            ->latest()
            ->first();
    }

    public function resultsForApprovedRequest(ResultAccessRequest $accessRequest): Collection
    {
        if (! $accessRequest->isApproved()) {
            return collect();
        }

        return StudentResult::query()
            ->where('school_id', $accessRequest->school_id)
            ->where('student_id', $accessRequest->student_id)
            ->where('academic_session_id', $accessRequest->academic_session_id)
            ->where('term_id', $accessRequest->term_id)
            ->where('result_type', $accessRequest->result_type)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->whereNull('unpublished_at')
            ->with(['subject', 'schoolClass', 'academicSession', 'term'])
            ->orderBy('subject_id')
            ->get();
    }

    private function createManualAccessRequest(
        User $user,
        School $school,
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        string $resultType,
        array $data,
        Request $httpRequest
    ): ResultAccessRequest {
        return ResultAccessRequest::query()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'requester_user_id' => $user->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => $resultType,
            'access_method' => ResultAccessRequest::METHOD_MANUAL_APPROVAL,
            'status' => ResultAccessRequest::STATUS_PENDING,
            'request_note' => $data['request_note'] ?? null,
            'metadata' => $this->requestMetadata($httpRequest),
        ]);
    }

    private function createPaymentAccessRequest(
        User $user,
        School $school,
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        string $resultType,
        array $data,
        Request $httpRequest
    ): ResultAccessRequest {
        $transaction = PaymentTransaction::query()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'amount' => (float) ($data['amount'] ?? 0),
            'currency' => config('app.currency', 'NGN'),
            'payment_method' => 'manual_or_gateway',
            'payment_gateway' => $data['payment_gateway'] ?? null,
            'payment_reference' => 'PRA-'.Str::upper(Str::random(12)),
            'status' => 'pending',
            'manual_payment_note' => $data['request_note'] ?? null,
            'metadata' => [
                'source' => 'portal_result_access',
                'academic_session_id' => $academicSession->id,
                'term_id' => $term->id,
                'result_type' => $resultType,
                'requester_user_id' => $user->id,
            ],
        ]);

        return ResultAccessRequest::query()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'requester_user_id' => $user->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => $resultType,
            'access_method' => ResultAccessRequest::METHOD_PAYMENT_REQUEST,
            'status' => ResultAccessRequest::STATUS_PENDING_PAYMENT,
            'payment_transaction_id' => $transaction->id,
            'request_note' => $data['request_note'] ?? null,
            'metadata' => $this->requestMetadata($httpRequest),
        ]);
    }

    private function createScratchCardAccessRequest(
        User $user,
        School $school,
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        string $resultType,
        array $data,
        Request $httpRequest
    ): ResultAccessRequest {
        $serial = (string) ($data['scratch_card_serial'] ?? '');
        $pin = (string) ($data['scratch_card_pin'] ?? '');

        $cardAccess = $this->scratchCardAccess->validateAndRecord(
            $school,
            $student,
            $academicSession,
            $term,
            $resultType,
            $serial,
            $pin,
            $httpRequest,
            true
        );

        if (! $cardAccess['success']) {
            throw ValidationException::withMessages([
                'scratch_card_serial' => $cardAccess['message'] ?: 'Invalid scratch card details.',
            ]);
        }

        /** @var ScratchCard $scratchCard */
        $scratchCard = $cardAccess['scratchCard'];

        return ResultAccessRequest::query()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'requester_user_id' => $user->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => $resultType,
            'access_method' => ResultAccessRequest::METHOD_SCRATCH_CARD,
            'status' => ResultAccessRequest::STATUS_APPROVED,
            'scratch_card_id' => $scratchCard->id,
            'approved_at' => now(),
            'expires_at' => now()->addDays(30),
            'request_note' => $data['request_note'] ?? null,
            'metadata' => $this->requestMetadata($httpRequest),
        ]);
    }

    private function hasPublishedResults(
        School $school,
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        string $resultType
    ): bool {
        return StudentResult::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('academic_session_id', $academicSession->id)
            ->where('term_id', $term->id)
            ->where('result_type', $resultType)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->whereNull('unpublished_at')
            ->exists();
    }

    private function requestMetadata(Request $request): array
    {
        return [
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'source' => 'portal_result_access',
        ];
    }
}
