<?php

namespace App\Services;

use App\Models\CbtAccessCode;
use App\Models\CbtAttempt;
use App\Models\CbtAttemptAnswer;
use App\Models\CbtCandidate;
use App\Models\CbtExam;
use App\Models\CbtExamQuestion;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CbtAttemptService
{
    public function __construct(
        private CbtGradingService $grading,
        private CbtEventLogger $events,
        private CbtResultIntegrationService $resultIntegration
    ) {}

    public function resolveCandidateByCode(CbtExam $exam, string $code, ?Student $student = null): CbtCandidate
    {
        $candidate = CbtCandidate::query()
            ->where('school_id', $exam->school_id)
            ->where('cbt_exam_id', $exam->id)
            ->where('candidate_code', trim($code))
            ->first();

        if ($candidate) {
            return $candidate;
        }

        $accessCode = CbtAccessCode::query()
            ->where('school_id', $exam->school_id)
            ->where('cbt_exam_id', $exam->id)
            ->where('code', trim($code))
            ->first();

        if (! $accessCode || ! $accessCode->isUsable()) {
            throw ValidationException::withMessages([
                'code' => __('cbt.invalid_or_expired_code'),
            ]);
        }

        $accessCode->increment('used_count');

        return CbtCandidate::create([
            'school_id' => $exam->school_id,
            'cbt_exam_id' => $exam->id,
            'student_id' => $student?->id,
            'name' => $student?->fullName(),
            'email' => $student?->guardian_email,
            'admission_number' => $student?->admission_number,
            'candidate_code' => $this->uniqueCandidateCode(),
            'source' => $student ? 'student' : 'generated_code',
            'status' => 'registered',
            'registered_at' => now(),
            'expires_at' => $exam->ends_at,
            'metadata' => [
                'access_code_id' => $accessCode->id,
            ],
        ]);
    }

    public function candidateForStudent(CbtExam $exam, Student $student): CbtCandidate
    {
        return CbtCandidate::firstOrCreate(
            [
                'school_id' => $exam->school_id,
                'cbt_exam_id' => $exam->id,
                'student_id' => $student->id,
            ],
            [
                'name' => $student->fullName(),
                'email' => $student->guardian_email,
                'admission_number' => $student->admission_number,
                'candidate_code' => $this->uniqueCandidateCode(),
                'source' => 'student',
                'status' => 'registered',
                'registered_at' => now(),
                'expires_at' => $exam->ends_at,
            ]
        );
    }

    public function start(CbtExam $exam, CbtCandidate $candidate, Request $request, ?User $user = null, string $accessChannel = 'internal'): CbtAttempt
    {
        $this->assertCanStart($exam, $candidate);

        if ($exam->allow_resume) {
            $resumable = $candidate->attempts()
                ->where('cbt_exam_id', $exam->id)
                ->whereIn('status', ['in_progress', 'resumed'])
                ->latest()
                ->first();

            if ($resumable?->isOpen()) {
                $this->events->log('attempt_resumed', $exam, $resumable, $candidate, request: $request);

                return $resumable->load(['exam.examQuestions.question.options', 'answers']);
            }
        }

        return DB::transaction(function () use ($exam, $candidate, $request, $user, $accessChannel) {
            $attemptNo = ((int) CbtAttempt::query()
                ->where('cbt_exam_id', $exam->id)
                ->where('cbt_candidate_id', $candidate->id)
                ->lockForUpdate()
                ->max('attempt_no')) + 1;

            if ($attemptNo > (int) $exam->max_attempts) {
                throw ValidationException::withMessages([
                    'attempt' => __('cbt.attempt_limit_reached'),
                ]);
            }

            $questions = $this->examQuestionSet($exam);

            if ($questions->isEmpty()) {
                throw ValidationException::withMessages([
                    'exam' => __('cbt.exam_has_no_questions'),
                ]);
            }

            $attempt = CbtAttempt::create([
                'attempt_uuid' => (string) Str::uuid(),
                'school_id' => $exam->school_id,
                'cbt_exam_id' => $exam->id,
                'cbt_candidate_id' => $candidate->id,
                'student_id' => $candidate->student_id,
                'user_id' => $user?->id,
                'attempt_no' => $attemptNo,
                'status' => 'in_progress',
                'access_channel' => $accessChannel,
                'started_at' => now(),
                'expires_at' => $exam->duration_minutes ? now()->addMinutes((int) $exam->duration_minutes) : $exam->ends_at,
                'max_score' => round($questions->sum(fn (CbtExamQuestion $item) => (float) $item->marks), 2),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'device_fingerprint' => $this->fingerprint($request),
                'client_snapshot' => [
                    'locale' => app()->getLocale(),
                    'timezone' => config('app.timezone'),
                ],
            ]);

            $questions->each(function (CbtExamQuestion $examQuestion) use ($attempt) {
                CbtAttemptAnswer::create([
                    'school_id' => $attempt->school_id,
                    'cbt_attempt_id' => $attempt->id,
                    'cbt_exam_question_id' => $examQuestion->id,
                    'cbt_question_id' => $examQuestion->cbt_question_id,
                    'question_type' => $examQuestion->question?->question_type ?: 'unknown',
                    'max_score' => $examQuestion->marks,
                    'status' => 'draft',
                ]);
            });

            $candidate->forceFill([
                'status' => 'active',
                'registered_at' => $candidate->registered_at ?: now(),
            ])->save();

            $this->events->log('attempt_started', $exam, $attempt, $candidate, [
                'attempt_no' => $attemptNo,
                'question_count' => $questions->count(),
            ], request: $request);

            return $attempt->load(['exam.examQuestions.question.options', 'answers']);
        });
    }

    public function saveAnswer(CbtAttempt $attempt, int $examQuestionId, array $payload, Request $request): CbtAttemptAnswer
    {
        $this->assertAttemptOpen($attempt);

        $answer = CbtAttemptAnswer::query()
            ->where('school_id', $attempt->school_id)
            ->where('cbt_attempt_id', $attempt->id)
            ->where('cbt_exam_question_id', $examQuestionId)
            ->firstOrFail();

        $answer->forceFill([
            'answer_payload' => $payload,
            'answer_text' => $this->answerTextFrom($payload),
            'selected_option_ids' => $this->selectedOptionIdsFrom($payload),
            'status' => 'saved',
            'autosaved_at' => now(),
        ])->save();

        $this->grading->gradeAnswer($answer);
        $attempt->forceFill(['last_autosaved_at' => now()])->save();
        $this->grading->recalculateAttempt($attempt->fresh(['answers.question', 'school']));

        $this->events->log('answer_autosaved', $attempt->exam, $attempt, $attempt->candidate, [
            'exam_question_id' => $examQuestionId,
        ], request: $request);

        return $answer->fresh();
    }

    public function submit(CbtAttempt $attempt, Request $request, bool $autoSubmitted = false): CbtAttempt
    {
        if (! in_array($attempt->status, ['in_progress', 'resumed'], true)) {
            return $attempt->load(['answers', 'exam']);
        }

        return DB::transaction(function () use ($attempt, $request, $autoSubmitted) {
            $attempt = CbtAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            $attempt->load(['answers.question.options', 'answers.examQuestion', 'exam.school', 'candidate']);

            foreach ($attempt->answers as $answer) {
                $this->grading->gradeAnswer($answer);
            }

            $attempt->refresh()->load(['answers.question', 'exam.school', 'school']);
            $hasPendingManual = $attempt->answers->contains(fn (CbtAttemptAnswer $answer) => $answer->status === 'needs_marking');
            $status = $hasPendingManual ? 'submitted' : 'graded';

            $attempt->forceFill([
                'status' => $autoSubmitted && $hasPendingManual ? 'auto_submitted' : $status,
                'submitted_at' => now(),
                'security_snapshot' => array_merge($attempt->security_snapshot ?? [], [
                    'submitted_ip' => $request->ip(),
                    'submitted_user_agent' => (string) $request->userAgent(),
                    'auto_submitted' => $autoSubmitted,
                ]),
            ])->save();

            $attempt = $this->grading->recalculateAttempt($attempt->fresh(['answers.question', 'school']), finalize: ! $hasPendingManual);

            if (! $hasPendingManual) {
                $this->resultIntegration->syncAttemptToStudentResult($attempt);
            }

            $this->events->log($autoSubmitted ? 'attempt_auto_submitted' : 'attempt_submitted', $attempt->exam, $attempt, $attempt->candidate, [
                'pending_manual_marking' => $hasPendingManual,
            ], request: $request);

            return $attempt->fresh(['answers.question.options', 'exam', 'candidate', 'studentResult']);
        });
    }

    public function markAnswer(CbtAttemptAnswer $answer, float $score, ?string $comment, User $marker, Request $request): CbtAttempt
    {
        $answer->loadMissing(['attempt.exam', 'question']);
        $score = max(0, min($score, (float) $answer->max_score));

        return DB::transaction(function () use ($answer, $score, $comment, $marker, $request) {
            $answer->forceFill([
                'manual_score' => $score,
                'marker_comment' => $comment,
                'marked_by' => $marker->id,
                'marked_at' => now(),
                'status' => 'marked',
            ])->save();

            $answer->markingRecords()->create([
                'school_id' => $answer->school_id,
                'cbt_exam_id' => $answer->attempt->cbt_exam_id,
                'cbt_attempt_id' => $answer->cbt_attempt_id,
                'marked_by' => $marker->id,
                'score' => $score,
                'max_score' => $answer->max_score,
                'comments' => $comment,
                'moderation_status' => 'final',
                'is_final' => true,
            ]);

            $attempt = $this->grading->recalculateAttempt($answer->attempt->fresh(['answers.question', 'school']), finalize: true);

            if (! $attempt->answers()->where('status', 'needs_marking')->exists()) {
                $attempt->forceFill([
                    'status' => 'graded',
                    'graded_at' => now(),
                ])->save();

                $this->resultIntegration->syncAttemptToStudentResult($attempt->fresh(['exam', 'school', 'student']));
            }

            $this->events->log('theory_answer_marked', $attempt->exam, $attempt, $attempt->candidate, [
                'answer_id' => $answer->id,
                'score' => $score,
            ], request: $request);

            return $attempt->fresh(['answers.question', 'exam', 'studentResult']);
        });
    }

    public function assertAttemptOpen(CbtAttempt $attempt): void
    {
        if (! $attempt->isOpen()) {
            throw ValidationException::withMessages([
                'attempt' => __('cbt.attempt_is_closed'),
            ]);
        }
    }

    private function assertCanStart(CbtExam $exam, CbtCandidate $candidate): void
    {
        if ((int) $candidate->school_id !== (int) $exam->school_id || (int) $candidate->cbt_exam_id !== (int) $exam->id) {
            throw ValidationException::withMessages(['exam' => __('cbt.invalid_candidate_scope')]);
        }

        if (! $exam->isOpenForEntry()) {
            throw ValidationException::withMessages(['exam' => __('cbt.exam_not_open')]);
        }

        if ($candidate->expires_at && now()->gt($candidate->expires_at)) {
            throw ValidationException::withMessages(['candidate' => __('cbt.invitation_expired')]);
        }
    }

    private function examQuestionSet(CbtExam $exam): Collection
    {
        $questions = $exam->examQuestions()
            ->with('question.options')
            ->get();

        if ($exam->randomize_questions) {
            $questions = $questions->shuffle()->values();
        }

        if ($exam->question_count > 0) {
            $questions = $questions->take((int) $exam->question_count)->values();
        }

        return $questions;
    }

    private function answerTextFrom(array $payload): ?string
    {
        $value = data_get($payload, 'text');

        return is_string($value) ? trim($value) : null;
    }

    private function selectedOptionIdsFrom(array $payload): array
    {
        $ids = data_get($payload, 'selected_option_ids', data_get($payload, 'selected'));

        if (! is_array($ids)) {
            $ids = filled($ids) ? [$ids] : [];
        }

        return collect($ids)->filter(fn ($id) => filled($id))->map(fn ($id) => (int) $id)->values()->all();
    }

    private function fingerprint(Request $request): string
    {
        return hash('sha256', implode('|', [
            $request->ip(),
            (string) $request->userAgent(),
            $request->header('Accept-Language'),
        ]));
    }

    private function uniqueCandidateCode(): string
    {
        do {
            $code = 'CBT-'.strtoupper(Str::random(10));
        } while (CbtCandidate::where('candidate_code', $code)->exists());

        return $code;
    }
}
