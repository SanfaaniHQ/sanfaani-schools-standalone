<?php

namespace App\Services;

use App\Models\CbtQuestion;
use App\Models\CbtQuestionOption;
use App\Support\MailSecurity;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CbtQuestionRenderer
{
    public function render(CbtQuestion $question, string $locale, bool $includeCorrectAnswers = false): array
    {
        $question->loadMissing('options');
        $locale = $this->normalizeLocale($locale);
        $direction = $this->directionFor($question, $locale);
        $translation = data_get($question->content, "translations.{$locale}", []);
        $promptHtml = $this->htmlValue($translation, 'prompt_html') ?: $question->prompt_html;
        $prompt = $this->textValue($translation, 'prompt') ?: $question->prompt;

        return [
            'id' => $question->id,
            'type' => $question->question_type,
            'prompt' => $prompt,
            'prompt_html' => filled($promptHtml) ? MailSecurity::sanitizeHtml((string) $promptHtml) : e($prompt),
            'explanation' => $includeCorrectAnswers ? ($this->textValue($translation, 'explanation') ?: $question->explanation) : null,
            'locale' => $locale,
            'direction' => $direction,
            'difficulty' => $question->difficulty,
            'topic' => $question->topic,
            'media' => $this->safeMedia($question->media ?? []),
            'options' => $this->renderOptions($question->options, $locale, $direction, $includeCorrectAnswers),
            'content' => [
                'math' => data_get($question->content, 'math'),
                'table' => data_get($question->content, 'table'),
                'code' => data_get($question->content, 'code'),
                'passage' => data_get($question->content, 'passage'),
            ],
        ];
    }

    private function renderOptions(Collection $options, string $locale, string $direction, bool $includeCorrectAnswers): array
    {
        return $options
            ->map(function (CbtQuestionOption $option) use ($locale, $direction, $includeCorrectAnswers) {
                $translation = data_get($option->metadata, "translations.{$locale}", []);
                $bodyHtml = $this->htmlValue($translation, 'body_html') ?: $option->body_html;
                $body = $this->textValue($translation, 'body') ?: $option->body;

                $payload = [
                    'id' => $option->id,
                    'key' => $option->option_key,
                    'body' => $body,
                    'body_html' => filled($bodyHtml) ? MailSecurity::sanitizeHtml((string) $bodyHtml) : e($body),
                    'direction' => $option->direction ?: $direction,
                ];

                if ($includeCorrectAnswers) {
                    $payload['is_correct'] = (bool) $option->is_correct;
                    $payload['score_weight'] = (float) $option->score_weight;
                }

                return $payload;
            })
            ->values()
            ->all();
    }

    private function textValue(array $translation, string $key): ?string
    {
        $value = data_get($translation, $key);

        return is_string($value) && filled($value) ? $value : null;
    }

    private function htmlValue(array $translation, string $key): ?string
    {
        $value = data_get($translation, $key);

        return is_string($value) && filled($value) ? $value : null;
    }

    private function normalizeLocale(string $locale): string
    {
        $locale = Str::of($locale)->lower()->replace('_', '-')->before('-')->toString();

        return in_array($locale, array_keys(config('sanfaani.languages', [])), true) ? $locale : 'en';
    }

    private function directionFor(CbtQuestion $question, string $locale): string
    {
        if (in_array($question->direction, ['ltr', 'rtl'], true)) {
            return $question->direction;
        }

        return in_array($locale, config('sanfaani.rtl_locales', ['ar']), true) ? 'rtl' : 'ltr';
    }

    private function safeMedia(array $media): array
    {
        return collect($media)
            ->only(['image', 'diagram', 'audio', 'video', 'attachments'])
            ->all();
    }
}
