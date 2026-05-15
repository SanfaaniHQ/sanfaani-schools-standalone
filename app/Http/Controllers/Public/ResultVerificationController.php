<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ResultVerification;
use App\Services\PublicResultAccessService;

class ResultVerificationController extends Controller
{
    public function show(string $verificationCode, PublicResultAccessService $resultAccess)
    {
        $verification = ResultVerification::with([
            'school',
            'student.schoolClass',
            'academicSession',
            'term',
        ])->where('verification_code', $verificationCode)->first();

        $isValid = false;

        if (
            $verification
            && $verification->status === 'active'
            && ! $verification->revoked_at
            && $verification->school
            && $verification->student
            && $verification->academicSession
            && $verification->term
        ) {
            $isValid = $resultAccess->hasPublishedResults(
                $verification->school,
                $verification->student,
                $verification->academicSession,
                $verification->term,
                $verification->result_type
            );
        }

        return view('public.results.verify', [
            'verification' => $verification,
            'isValid' => $isValid,
            'maskedStudentName' => $verification?->student ? $this->maskName($verification->student->fullName()) : null,
            'maskedAdmissionNumber' => $verification?->student ? $this->maskValue($verification->student->admission_number) : null,
        ]);
    }

    private function maskName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return collect($parts)
            ->filter()
            ->map(fn (string $part) => mb_substr($part, 0, 1).str_repeat('*', max(mb_strlen($part) - 1, 1)))
            ->implode(' ');
    }

    private function maskValue(string $value): string
    {
        $length = mb_strlen($value);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return mb_substr($value, 0, 2).str_repeat('*', $length - 4).mb_substr($value, -2);
    }
}
