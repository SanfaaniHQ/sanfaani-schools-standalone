<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\CbtQuestion;
use App\Models\CbtQuestionBank;
use App\Models\CbtQuestionOption;
use App\Models\School;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CbtQuestionController extends Controller
{
    public function store(Request $request, CbtQuestionBank $questionBank)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSchoolScope($questionBank, $school);

        $data = $request->validate([
            'question_type' => ['required', Rule::in([
                'multiple_choice',
                'checkbox',
                'true_false',
                'fill_blank',
                'short_answer',
                'long_answer',
                'essay',
                'matching',
                'practical_instruction',
                'image_based',
                'diagram_based',
                'table_based',
                'comprehension',
            ])],
            'prompt' => ['required', 'string'],
            'prompt_html' => ['nullable', 'string'],
            'explanation' => ['nullable', 'string'],
            'default_locale' => ['required', Rule::in(array_keys(config('sanfaani.languages', [])))],
            'direction' => ['required', Rule::in(['ltr', 'rtl'])],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard', 'advanced'])],
            'topic' => ['nullable', 'string', 'max:160'],
            'default_marks' => ['required', 'numeric', 'min:0', 'max:1000'],
            'acceptable_answers' => ['nullable', 'string', 'max:1000'],
            'options' => ['nullable', 'array'],
            'options.*.body' => ['nullable', 'string'],
            'options.*.is_correct' => ['nullable', 'boolean'],
        ]);

        $question = CbtQuestion::create([
            'school_id' => $school->id,
            'cbt_question_bank_id' => $questionBank->id,
            'subject_id' => $questionBank->subject_id,
            'school_class_id' => $questionBank->school_class_id,
            'question_type' => $data['question_type'],
            'prompt' => $data['prompt'],
            'prompt_html' => $data['prompt_html'] ?? null,
            'explanation' => $data['explanation'] ?? null,
            'default_locale' => $data['default_locale'],
            'direction' => $data['direction'],
            'difficulty' => $data['difficulty'],
            'topic' => $data['topic'] ?? $questionBank->topic,
            'content' => [
                'translations' => [
                    $data['default_locale'] => [
                        'prompt' => $data['prompt'],
                        'prompt_html' => $data['prompt_html'] ?? null,
                        'explanation' => $data['explanation'] ?? null,
                    ],
                ],
            ],
            'scoring' => [
                'answers' => $this->csv($data['acceptable_answers'] ?? null),
            ],
            'default_marks' => $data['default_marks'],
            'status' => 'active',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        foreach (($data['options'] ?? []) as $index => $option) {
            if (! filled($option['body'] ?? null)) {
                continue;
            }

            CbtQuestionOption::create([
                'school_id' => $school->id,
                'cbt_question_id' => $question->id,
                'option_key' => chr(65 + $index),
                'body' => $option['body'],
                'locale' => $data['default_locale'],
                'direction' => $data['direction'],
                'is_correct' => (bool) ($option['is_correct'] ?? false),
                'sort_order' => $index,
            ]);
        }

        return back()->with('success', __('cbt.question_created'));
    }

    private function authorizeSchoolScope(CbtQuestionBank $bank, School $school): void
    {
        if ((int) $bank->school_id !== (int) $school->id) {
            abort(403);
        }
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function csv(?string $value): array
    {
        return collect(explode(',', (string) $value))->map(fn ($item) => trim($item))->filter()->values()->all();
    }
}
