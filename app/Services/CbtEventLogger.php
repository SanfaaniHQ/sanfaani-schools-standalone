<?php

namespace App\Services;

use App\Models\CbtAttempt;
use App\Models\CbtCandidate;
use App\Models\CbtEventLog;
use App\Models\CbtExam;
use Illuminate\Http\Request;

class CbtEventLogger
{
    public function log(
        string $event,
        ?CbtExam $exam = null,
        ?CbtAttempt $attempt = null,
        ?CbtCandidate $candidate = null,
        array $payload = [],
        string $severity = 'info',
        ?Request $request = null
    ): CbtEventLog {
        $request ??= request();

        return CbtEventLog::create([
            'school_id' => $exam?->school_id ?? $attempt?->school_id ?? $candidate?->school_id,
            'cbt_exam_id' => $exam?->id ?? $attempt?->cbt_exam_id ?? $candidate?->cbt_exam_id,
            'cbt_candidate_id' => $candidate?->id ?? $attempt?->cbt_candidate_id,
            'cbt_attempt_id' => $attempt?->id,
            'user_id' => $request?->user()?->id,
            'event' => $event,
            'severity' => $severity,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? (string) $request->userAgent() : null,
            'payload' => $this->sanitize($payload),
        ]);
    }

    private function sanitize(array $payload): array
    {
        unset(
            $payload['password'],
            $payload['token'],
            $payload['invitation_token'],
            $payload['candidate_code'],
            $payload['scratch_pin'],
            $payload['correct_answers']
        );

        return $payload;
    }
}
