<?php

namespace App\Services;

use App\Models\CbtQuestion;
use App\Models\CbtQuestionBank;
use App\Models\CbtQuestionOption;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class CbtQuestionImportService
{
    public int $createdCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function importCsv(School $school, CbtQuestionBank $bank, string $path, ?User $actor = null): void
    {
        $handle = fopen($path, 'rb');

        if (! $handle) {
            throw new RuntimeException('Unable to open question import file.');
        }

        $headers = null;
        $rowNumber = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($rowNumber === 1) {
                $headers = $this->normalizeHeaders($row);

                continue;
            }

            if ($this->emptyRow($row)) {
                continue;
            }

            try {
                $data = array_combine($headers, array_pad($row, count($headers), null));
                $this->createQuestion($school, $bank, $data, $actor);
            } catch (\Throwable $exception) {
                $this->errors[] = "Row {$rowNumber}: ".$exception->getMessage();
            }
        }

        fclose($handle);
    }

    private function createQuestion(School $school, CbtQuestionBank $bank, array $data, ?User $actor): void
    {
        $prompt = trim((string) ($data['prompt'] ?? ''));

        if ($prompt === '') {
            throw new RuntimeException('Prompt is required.');
        }

        $type = $this->normalizeType((string) ($data['question_type'] ?? 'multiple_choice'));
        $marks = is_numeric($data['marks'] ?? null) ? (float) $data['marks'] : 1.0;
        $direction = in_array($data['direction'] ?? null, ['rtl', 'ltr'], true) ? $data['direction'] : 'ltr';
        $locale = trim((string) ($data['locale'] ?? $bank->default_locale ?: 'en')) ?: 'en';

        $question = CbtQuestion::create([
            'school_id' => $school->id,
            'cbt_question_bank_id' => $bank->id,
            'subject_id' => $bank->subject_id,
            'school_class_id' => $bank->school_class_id,
            'question_type' => $type,
            'prompt' => $prompt,
            'prompt_html' => $data['prompt_html'] ?? null,
            'explanation' => $data['explanation'] ?? null,
            'default_locale' => $locale,
            'direction' => $direction,
            'difficulty' => $data['difficulty'] ?: 'medium',
            'topic' => $data['topic'] ?: $bank->topic,
            'tags' => $this->tags($data['tags'] ?? null),
            'content' => [
                'translations' => $this->translationPayload($data, $locale),
                'passage' => $data['passage'] ?? null,
                'table' => $data['table'] ?? null,
                'code' => $data['code'] ?? null,
                'math' => $data['math'] ?? null,
            ],
            'media' => [
                'image' => $data['image'] ?? null,
                'diagram' => $data['diagram'] ?? null,
                'audio' => $data['audio'] ?? null,
                'video' => $data['video'] ?? null,
            ],
            'scoring' => [
                'answers' => $this->tags($data['acceptable_answers'] ?? null),
                'pairs' => $this->pairs($data['matching_pairs'] ?? null),
            ],
            'default_marks' => $marks,
            'status' => 'active',
            'created_by' => $actor?->id,
            'updated_by' => $actor?->id,
        ]);

        $this->createOptions($school, $question, $data, $locale, $direction);
        $this->createdCount++;
    }

    private function createOptions(School $school, CbtQuestion $question, array $data, string $locale, string $direction): void
    {
        $correctKeys = collect($this->tags($data['correct_options'] ?? $data['correct_option'] ?? null))
            ->map(fn (string $key) => Str::upper($key))
            ->all();

        foreach (range('a', 'h') as $index => $key) {
            $body = trim((string) ($data["option_{$key}"] ?? ''));

            if ($body === '') {
                continue;
            }

            $optionKey = Str::upper($key);

            CbtQuestionOption::create([
                'school_id' => $school->id,
                'cbt_question_id' => $question->id,
                'option_key' => $optionKey,
                'body' => $body,
                'locale' => $locale,
                'direction' => $direction,
                'is_correct' => in_array($optionKey, $correctKeys, true),
                'sort_order' => $index,
            ]);
        }
    }

    private function normalizeHeaders(array $row): array
    {
        return collect($row)
            ->map(fn ($header) => Str::of((string) $header)->lower()->trim()->replace([' ', '-'], '_')->toString())
            ->all();
    }

    private function emptyRow(array $row): bool
    {
        return collect($row)->every(fn ($value) => trim((string) $value) === '');
    }

    private function normalizeType(string $type): string
    {
        $type = Str::of($type)->lower()->replace([' ', '-'], '_')->toString();

        return match ($type) {
            'multi_answer', 'multiple_answer', 'multi_select' => 'checkbox',
            'truefalse' => 'true_false',
            'fill_in_the_blank' => 'fill_blank',
            'long_theory_answer' => 'long_answer',
            'practical_instructions' => 'practical_instruction',
            default => $type ?: 'multiple_choice',
        };
    }

    private function tags(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        return collect(explode(',', (string) $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function pairs(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $pairs = [];

        foreach (explode(';', (string) $value) as $pair) {
            [$left, $right] = array_pad(explode('=', $pair, 2), 2, null);

            if (filled($left) && filled($right)) {
                $pairs[trim($left)] = trim($right);
            }
        }

        return $pairs;
    }

    private function translationPayload(array $data, string $locale): array
    {
        return [
            $locale => [
                'prompt' => $data['prompt'] ?? null,
                'prompt_html' => $data['prompt_html'] ?? null,
                'explanation' => $data['explanation'] ?? null,
            ],
        ];
    }
}
