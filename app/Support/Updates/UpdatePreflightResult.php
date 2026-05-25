<?php

namespace App\Support\Updates;

class UpdatePreflightResult
{
    /**
     * @param  array<int, array<string, mixed>>  $checks
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private array $checks = [],
        private array $metadata = [],
    ) {}

    public static function make(array $metadata = []): self
    {
        return new self(metadata: $metadata);
    }

    public function add(
        string $key,
        string $label,
        string $status,
        string $severity,
        string $message,
        bool $blocks = false,
        array $context = [],
    ): self {
        $this->checks[] = [
            'key' => $key,
            'label' => $label,
            'status' => $status,
            'severity' => $severity,
            'message' => $message,
            'blocks' => $blocks,
            'context' => $context,
        ];

        return $this;
    }

    public function passed(): bool
    {
        return ! $this->hasBlockers();
    }

    public function hasBlockers(): bool
    {
        return collect($this->checks)->contains(fn (array $check): bool => (bool) ($check['blocks'] ?? false));
    }

    public function summary(): string
    {
        if ($this->passed()) {
            return 'Preflight checks are ready for manual update review.';
        }

        return 'Preflight checks found blockers that must be resolved before update readiness.';
    }

    public function checks(): array
    {
        return $this->checks;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function blockingChecks(): array
    {
        return collect($this->checks)
            ->filter(fn (array $check): bool => (bool) ($check['blocks'] ?? false))
            ->values()
            ->all();
    }

    public function warnings(): array
    {
        return collect($this->checks)
            ->filter(fn (array $check): bool => in_array($check['severity'] ?? null, ['warning', 'pending'], true))
            ->values()
            ->all();
    }

    public function toArray(): array
    {
        return [
            'passed' => $this->passed(),
            'summary' => $this->summary(),
            'checks' => $this->checks,
            'metadata' => $this->metadata,
        ];
    }
}
