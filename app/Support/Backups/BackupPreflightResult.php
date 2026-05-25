<?php

namespace App\Support\Backups;

class BackupPreflightResult
{
    private array $checks = [];

    public function add(
        string $key,
        string $label,
        string $status,
        string $severity,
        string $message,
        bool $blocks = false,
        array $context = [],
    ): void {
        $this->checks[] = [
            'key' => $key,
            'label' => $label,
            'status' => $status,
            'severity' => $severity,
            'message' => $message,
            'blocks' => $blocks,
            'context' => $context,
        ];
    }

    public function passed(): bool
    {
        return collect($this->checks)->doesntContain(fn (array $check): bool => (bool) ($check['blocks'] ?? false));
    }

    public function checks(): array
    {
        return $this->checks;
    }

    public function summary(): string
    {
        if ($this->passed()) {
            return 'Backup preflight completed with no blocking issues.';
        }

        return 'Backup preflight found blocking issues that must be resolved before backup work continues.';
    }

    public function toArray(): array
    {
        return [
            'passed' => $this->passed(),
            'summary' => $this->summary(),
            'checks' => $this->checks,
            'checked_at' => now()->toIso8601String(),
        ];
    }
}
