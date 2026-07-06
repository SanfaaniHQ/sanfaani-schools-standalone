<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CbtAttempt;
use App\Models\CbtCandidate;
use App\Models\CbtExam;
use App\Models\School;
use App\Models\Student;
use App\Services\CbtAttemptService;
use App\Services\CbtQuestionRenderer;
use App\Services\PdfSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CbtAccessController extends Controller
{
    public function entry(School $school, string $exam)
    {
        $exam = $this->examForSchool($school, $exam);

        return view('public.cbt.entry', [
            'school' => $school,
            'exam' => $exam,
        ]);
    }

    public function access(Request $request, School $school, string $exam, CbtAttemptService $attempts)
    {
        $exam = $this->examForSchool($school, $exam);
        $data = $request->validate([
            'access_mode' => ['required', Rule::in(['candidate_code', 'admission_number', 'public_registration'])],
            'code' => ['nullable', 'required_if:access_mode,candidate_code', 'string', 'max:120'],
            'admission_number' => ['nullable', 'required_if:access_mode,admission_number', 'string', 'max:120'],
            'name' => ['nullable', 'required_if:access_mode,public_registration', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'website_url' => ['nullable', 'max:0'],
        ]);

        $candidate = match ($data['access_mode']) {
            'admission_number' => $attempts->candidateForStudent($exam, $this->studentForAdmission($school, (string) $data['admission_number'])),
            'public_registration' => $this->publicCandidate($exam, $data),
            default => $attempts->resolveCandidateByCode($exam, (string) $data['code']),
        };

        $attempt = $attempts->start($exam, $candidate, $request, accessChannel: 'public');
        $this->rememberAttempt($request, $attempt);

        return redirect()->route('public.cbt.take', ['attempt' => $attempt->attempt_uuid]);
    }

    public function take(Request $request, CbtAttempt $attempt, CbtQuestionRenderer $renderer)
    {
        $this->authorizeAttemptSession($request, $attempt);

        if (! $attempt->isOpen()) {
            return redirect()->route('public.cbt.result', ['attempt' => $attempt->attempt_uuid]);
        }

        $attempt->load(['exam.school', 'answers.examQuestion.question.options']);
        $locale = app()->getLocale();
        $answerRows = $attempt->answers->sortBy('id')->values();

        return view('public.cbt.take', [
            'attempt' => $attempt,
            'exam' => $attempt->exam,
            'renderedQuestions' => $answerRows
                ->map(function ($answer) use ($attempt, $renderer, $locale) {
                    $examQuestion = $answer->examQuestion;
                    $question = $renderer->render($examQuestion->question, $locale, false);

                    if ($attempt->exam->randomize_options) {
                        $question['options'] = collect($question['options'])
                            ->sortBy(fn ($option) => hash('sha256', $attempt->attempt_uuid.'|'.$question['id'].'|'.$option['id']))
                            ->values()
                            ->all();
                    }

                    return [
                        'exam_question' => $examQuestion,
                        'question' => $question,
                        'answer' => $answer,
                    ];
                }),
        ]);
    }

    public function save(Request $request, CbtAttempt $attempt, CbtAttemptService $attempts)
    {
        $this->authorizeAttemptSession($request, $attempt);
        $data = $request->validate([
            'exam_question_id' => ['required', 'integer'],
            'answer' => ['nullable'],
            'selected_option_ids' => ['nullable', 'array'],
            'selected_option_ids.*' => ['integer'],
        ]);

        $answer = $attempts->saveAnswer($attempt, (int) $data['exam_question_id'], [
            'text' => is_string($data['answer'] ?? null) ? $data['answer'] : null,
            'selected_option_ids' => $data['selected_option_ids'] ?? [],
        ], $request);

        return response()->json([
            'success' => true,
            'answer_id' => $answer->id,
            'status' => $answer->status,
            'autosaved_at' => optional($answer->autosaved_at)->toDateTimeString(),
        ]);
    }

    public function submit(Request $request, CbtAttempt $attempt, CbtAttemptService $attempts)
    {
        $this->authorizeAttemptSession($request, $attempt);
        $autoSubmitted = $request->boolean('auto_submitted') || ! $attempt->isOpen();

        if ($attempt->isOpen()) {
            foreach ((array) $request->input('answers', []) as $examQuestionId => $payload) {
                $attempts->saveAnswer($attempt, (int) $examQuestionId, [
                    'text' => is_string($payload) ? $payload : data_get($payload, 'text'),
                    'selected_option_ids' => is_array($payload) ? (array) data_get($payload, 'selected_option_ids', []) : [],
                    'pairs' => is_array($payload) ? (array) data_get($payload, 'pairs', []) : [],
                ], $request);
            }
        }

        $attempt = $attempts->submit($attempt->fresh(), $request, $autoSubmitted);

        return redirect()->route('public.cbt.result', ['attempt' => $attempt->attempt_uuid]);
    }

    public function result(Request $request, CbtAttempt $attempt)
    {
        $this->authorizeAttemptSession($request, $attempt);
        $attempt->load(['exam', 'candidate', 'studentResult']);

        return view('public.cbt.result', [
            'attempt' => $attempt,
            'exam' => $attempt->exam,
            'canShowScore' => $attempt->exam->show_result_immediately || $attempt->result_release_status === 'published',
        ]);
    }

    public function snapshot(Request $request, CbtAttempt $attempt, PdfSnapshotService $snapshots)
    {
        $this->authorizeAttemptSession($request, $attempt);
        $attempt->load(['exam.school', 'candidate', 'student', 'answers.question']);

        abort_unless($attempt->exam->show_result_immediately || $attempt->result_release_status === 'published', 403);

        $snapshot = $snapshots->captureAndGenerate(
            'cbt_result',
            $attempt->exam->title.' - '.__('cbt.result'),
            [
                'candidate' => [
                    'name' => $attempt->candidate?->name ?? $attempt->student?->fullName(),
                    'code' => $attempt->candidate?->candidate_code,
                ],
                'exam' => [
                    'title' => $attempt->exam->title,
                    'type' => $attempt->exam->exam_type,
                    'submitted_at' => $attempt->submitted_at?->toDateTimeString(),
                ],
                'score' => [
                    'total_score' => (float) $attempt->total_score,
                    'max_score' => (float) $attempt->max_score,
                    'grade' => $attempt->grade,
                    'remark' => $attempt->remark,
                ],
            ],
            $attempt->exam->school,
            $attempt,
            $attempt->student,
            referenceCode: $attempt->attempt_uuid,
            locale: app()->getLocale()
        );

        return Storage::disk($snapshot->pdf_disk)->download($snapshot->pdf_path, 'cbt-result-'.$attempt->attempt_uuid.'.pdf');
    }

    private function examForSchool(School $school, string $slug): CbtExam
    {
        return CbtExam::query()
            ->where('school_id', $school->id)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function studentForAdmission(School $school, string $admissionNumber): Student
    {
        return Student::query()
            ->where('school_id', $school->id)
            ->where('admission_number', trim($admissionNumber))
            ->where('status', 'active')
            ->firstOrFail();
    }

    private function publicCandidate(CbtExam $exam, array $data)
    {
        if (! $exam->supports_public_candidates) {
            abort(403);
        }

        return $exam->candidates()->create([
            'school_id' => $exam->school_id,
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'candidate_code' => $this->uniquePublicCandidateCode(),
            'source' => 'public',
            'status' => 'registered',
            'registered_at' => now(),
            'expires_at' => $exam->ends_at,
        ]);
    }

    private function uniquePublicCandidateCode(): string
    {
        do {
            $code = 'PUB-'.strtoupper(Str::random(12));
        } while (CbtCandidate::where('candidate_code', $code)->exists());

        return $code;
    }

    private function rememberAttempt(Request $request, CbtAttempt $attempt): void
    {
        $attempts = $request->session()->get('cbt_attempts', []);
        $attempts[$attempt->attempt_uuid] = [
            'id' => $attempt->id,
            'started_at' => now()->timestamp,
        ];

        $request->session()->put('cbt_attempts', array_slice($attempts, -5, null, true));
    }

    private function authorizeAttemptSession(Request $request, CbtAttempt $attempt): void
    {
        $attempts = $request->session()->get('cbt_attempts', []);

        if (! isset($attempts[$attempt->attempt_uuid])) {
            abort(403);
        }
    }
}
