<?php

namespace App\Services;

use App\Models\CbtAttempt;
use App\Models\CbtAttemptAnswer;
use App\Models\CbtQuestion;
use Illuminate\Support\Collection;

class CbtGradingService
{
    public function __construct(private ResultGradingService $grading) {}

    public function gradeAnswer(CbtAttemptAnswer $answer): CbtAttemptAnswer
    {
        $answer->loadMissing(['question.options', 'examQuestion']);
        $question = $answer->question;

        if (! $question) {
            return $answer;
        }

        $maxScore = (float) ($answer->max_score ?: $answer->examQuestion?->marks ?: $question->default_marks ?: 1);

        if ($question->requiresManualMarking()) {
            $answer->forceFill([
                'max_score' => $maxScore,
                'status' => $answer->status === 'marked' ? 'marked' : 'needs_marking',
            ])->save();

            return $answer;
        }

        [$score, $isCorrect] = match ($question->question_type) {
            'multiple_choice', 'true_false' => $this->gradeSingleChoice($question, $answer->selected_option_ids ?? [], $maxScore),
            'checkbox' => $this->gradeCheckbox($question, $answer->selected_option_ids ?? [], $maxScore),
            'fill_blank', 'short_answer' => $this->gradeTextAnswer($question, (string) $answer->answer_text, $maxScore),
            'matching' => $this->gradeMatching($question, $answer->answer_payload ?? [], $maxScore),
            default => [0.0, false],
        };

        $answer->forceFill([
            'auto_score' => round($score, 2),
            'is_correct' => $isCorrect,
            'max_score' => $maxScore,
            'status' => 'marked',
        ])->save();

        return $answer;
    }

    public function recalculateAttempt(CbtAttempt $attempt, bool $finalize = false): CbtAttempt
    {
        $attempt->loadMissing(['answers.question', 'exam.school']);
        $objectiveTypes = CbtQuestion::AUTO_GRADED_TYPES;
        $answers = $attempt->answers;

        $objectiveScore = $answers
            ->filter(fn (CbtAttemptAnswer $answer) => in_array($answer->question_type, $objectiveTypes, true))
            ->sum(fn (CbtAttemptAnswer $answer) => (float) $answer->auto_score);

        $theoryScore = $answers
            ->reject(fn (CbtAttemptAnswer $answer) => in_array($answer->question_type, $objectiveTypes, true))
            ->sum(fn (CbtAttemptAnswer $answer) => (float) $answer->manual_score);

        $maxScore = $answers->sum(fn (CbtAttemptAnswer $answer) => (float) $answer->max_score);
        $totalScore = round($objectiveScore + $theoryScore, 2);
        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0.0;
        $grade = $attempt->school ? $this->grading->calculate($attempt->school, $percentage) : ['grade' => null, 'remark' => null];
        $hasPendingManual = $answers->contains(fn (CbtAttemptAnswer $answer) => $answer->status === 'needs_marking');

        $attempt->forceFill([
            'objective_score' => round($objectiveScore, 2),
            'theory_score' => round($theoryScore, 2),
            'total_score' => $totalScore,
            'max_score' => round($maxScore, 2),
            'grade' => $grade['grade'] ?? null,
            'remark' => $grade['remark'] ?? null,
            'answers_hash' => $this->answersHash($answers),
            'graded_at' => $finalize && ! $hasPendingManual ? now() : $attempt->graded_at,
        ])->save();

        return $attempt;
    }

    private function gradeSingleChoice(CbtQuestion $question, array $selectedIds, float $maxScore): array
    {
        $correctIds = $this->correctOptionIds($question);
        $selectedIds = $this->normalizeIds($selectedIds);
        $isCorrect = count($selectedIds) === 1 && $selectedIds === $correctIds;

        return [$isCorrect ? $maxScore : 0.0, $isCorrect];
    }

    private function gradeCheckbox(CbtQuestion $question, array $selectedIds, float $maxScore): array
    {
        $correctIds = $this->correctOptionIds($question);
        $selectedIds = $this->normalizeIds($selectedIds);

        if ($correctIds === []) {
            return [0.0, false];
        }

        $correctSelected = count(array_intersect($selectedIds, $correctIds));
        $incorrectSelected = count(array_diff($selectedIds, $correctIds));
        $rawScore = max(0, $correctSelected - $incorrectSelected);
        $score = ($rawScore / count($correctIds)) * $maxScore;
        $isCorrect = $selectedIds === $correctIds;

        return [$score, $isCorrect];
    }

    private function gradeTextAnswer(CbtQuestion $question, string $answerText, float $maxScore): array
    {
        $expectedAnswers = collect(data_get($question->scoring, 'answers', []))
            ->merge($question->options->where('is_correct', true)->pluck('body'))
            ->map(fn ($value) => $this->normalizeText((string) $value))
            ->filter()
            ->unique()
            ->values();

        $normalizedAnswer = $this->normalizeText($answerText);
        $isCorrect = $normalizedAnswer !== '' && $expectedAnswers->contains($normalizedAnswer);

        return [$isCorrect ? $maxScore : 0.0, $isCorrect];
    }

    private function gradeMatching(CbtQuestion $question, array $payload, float $maxScore): array
    {
        $expectedPairs = collect(data_get($question->scoring, 'pairs', []))
            ->mapWithKeys(fn ($value, $key) => [$this->normalizeText((string) $key) => $this->normalizeText((string) $value)]);

        $submittedPairs = collect(data_get($payload, 'pairs', []))
            ->mapWithKeys(fn ($value, $key) => [$this->normalizeText((string) $key) => $this->normalizeText((string) $value)]);

        if ($expectedPairs->isEmpty()) {
            return [0.0, false];
        }

        $correct = $expectedPairs
            ->filter(fn ($value, $key) => $submittedPairs->get($key) === $value)
            ->count();

        $score = ($correct / $expectedPairs->count()) * $maxScore;

        return [$score, $correct === $expectedPairs->count()];
    }

    private function correctOptionIds(CbtQuestion $question): array
    {
        return $this->normalizeIds($question->options
            ->where('is_correct', true)
            ->pluck('id')
            ->all());
    }

    private function normalizeIds(array $ids): array
    {
        $ids = collect($ids)
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $ids;
    }

    private function normalizeText(string $text): string
    {
        return str($text)
            ->lower()
            ->squish()
            ->trim()
            ->toString();
    }

    private function answersHash(Collection $answers): string
    {
        $payload = $answers
            ->sortBy('id')
            ->map(fn (CbtAttemptAnswer $answer) => [
                'question_id' => $answer->cbt_question_id,
                'payload' => $answer->answer_payload,
                'text' => $answer->answer_text,
                'selected' => $answer->selected_option_ids,
                'auto_score' => (float) $answer->auto_score,
                'manual_score' => (float) $answer->manual_score,
            ])
            ->values()
            ->all();

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
