<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolResultAccessPolicy;
use App\Models\SchoolResultAccessPolicyRule;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\ResultVerification;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PublicResultAccessService
{
    private const SESSION_KEY = 'public_result_access_tokens';

    private const TOKEN_TTL_MINUTES = 30;

    private const MAX_SESSION_TOKENS = 10;

    public function __construct(
        private SchoolFeatureAccessService $featureAccess
    ) {}

    public function evaluateAccess(
        School $school,
        AcademicSession $academicSession,
        Term $term,
        string $resultType
    ): array {
        if ($school->status !== 'active' || $school->subscription_status === 'expired') {
            return $this->failure(__('public_result.result_checking_unavailable'));
        }

        $policy = $this->activeAccessPolicy($school);

        if ($this->featureAccess->isExplicitlyDisabled($school, 'public_result_checker')) {
            return $this->failure(__('public_result.result_checking_unavailable'));
        }

        if (! $policy) {
            return $this->scratchCardRequired();
        }

        $rule = $this->matchingRule($policy, $academicSession, $term, $resultType);

        if ($rule?->requires_scratch_card) {
            return $this->scratchCardRequired();
        }

        return match ($policy->access_mode) {
            'scratch_card' => $this->scratchCardRequired(),
            'hybrid' => $this->scratchCardRequired(),
            'school_paid' => $this->failure(__('public_result.school_paid_access_coming_soon')),
            'parent_paid' => $this->failure(__('public_result.parent_payment_coming_soon')),
            default => $this->scratchCardRequired(),
        };
    }

    public function publishedResults(
        School $school,
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        string $resultType
    ): Collection {
        return $this->publishedResultsQuery($school, $student, $academicSession, $term, $resultType)
            ->with(['subject', 'schoolClass', 'academicSession', 'term'])
            ->orderBy('subject_id')
            ->get();
    }

    public function hasPublishedResults(
        School $school,
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        string $resultType
    ): bool {
        return $this->publishedResultsQuery($school, $student, $academicSession, $term, $resultType)
            ->exists();
    }

    public function createToken(
        Request $request,
        School $school,
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        string $resultType,
        string $locale
    ): string {
        $token = Str::random(64);
        $tokens = $this->pruneTokens($request->session()->get(self::SESSION_KEY, []));

        $tokens[$token] = [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => $resultType,
            'locale' => $locale,
            'created_at' => now()->timestamp,
        ];

        $request->session()->put(self::SESSION_KEY, $tokens);

        return $token;
    }

    public function verificationFor(
        School $school,
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        string $resultType
    ): ResultVerification {
        $verification = ResultVerification::where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('academic_session_id', $academicSession->id)
            ->where('term_id', $term->id)
            ->where('result_type', $resultType)
            ->first();

        if ($verification) {
            return $verification;
        }

        return ResultVerification::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => $resultType,
            'verification_code' => $this->uniqueVerificationCode(),
            'status' => 'active',
            'issued_at' => now(),
        ]);
    }

    public function tokenData(Request $request, string $token): ?array
    {
        $tokens = $this->pruneTokens($request->session()->get(self::SESSION_KEY, []));

        if (! isset($tokens[$token]) || ! is_array($tokens[$token])) {
            $request->session()->put(self::SESSION_KEY, $tokens);

            return null;
        }

        $data = $tokens[$token];
        $request->session()->put(self::SESSION_KEY, $tokens);

        return $data;
    }

    private function pruneTokens(mixed $tokens): array
    {
        if (! is_array($tokens)) {
            return [];
        }

        $expiresBefore = now()->subMinutes(self::TOKEN_TTL_MINUTES)->timestamp;

        $tokens = array_filter(
            $tokens,
            fn ($data) => is_array($data) && (int) ($data['created_at'] ?? 0) >= $expiresBefore
        );

        return array_slice($tokens, -self::MAX_SESSION_TOKENS, null, true);
    }

    private function publishedResultsQuery(
        School $school,
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        string $resultType
    ) {
        return StudentResult::where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('academic_session_id', $academicSession->id)
            ->where('term_id', $term->id)
            ->where('result_type', $resultType)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->whereNull('unpublished_at');
    }

    private function activeAccessPolicy(School $school): ?SchoolResultAccessPolicy
    {
        return $school->resultAccessPolicies()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->latest()
            ->first();
    }

    private function matchingRule(
        SchoolResultAccessPolicy $policy,
        AcademicSession $academicSession,
        Term $term,
        string $resultType
    ): ?SchoolResultAccessPolicyRule {
        return $policy->rules()
            ->where('status', 'active')
            ->where(function ($query) use ($academicSession) {
                $query->whereNull('academic_session_id')
                    ->orWhere('academic_session_id', $academicSession->id);
            })
            ->where(function ($query) use ($term) {
                $query->whereNull('term_id')
                    ->orWhere('term_id', $term->id);
            })
            ->where(function ($query) use ($resultType) {
                $query->whereNull('result_type')
                    ->orWhere('result_type', $resultType);
            })
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->get()
            ->sortByDesc(function ($rule) use ($academicSession, $term, $resultType) {
                return (int) ((int) $rule->academic_session_id === (int) $academicSession->id)
                    + (int) ((int) $rule->term_id === (int) $term->id)
                    + (int) ($rule->result_type === $resultType);
            })
            ->first();
    }

    private function scratchCardRequired(): array
    {
        return [
            'success' => true,
            'requires_scratch_card' => true,
            'message' => null,
        ];
    }

    private function failure(string $message): array
    {
        return [
            'success' => false,
            'requires_scratch_card' => true,
            'message' => $message,
        ];
    }

    private function uniqueVerificationCode(): string
    {
        do {
            $code = 'RV-' . strtoupper(Str::random(10));
        } while (ResultVerification::where('verification_code', $code)->exists());

        return $code;
    }
}
