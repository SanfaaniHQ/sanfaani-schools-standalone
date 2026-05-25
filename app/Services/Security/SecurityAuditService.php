<?php

namespace App\Services\Security;

class SecurityAuditService
{
    public function __construct(
        private ProductionReadinessService $production,
        private EmailSafetyService $email,
        private LoggingSafetyService $logging,
        private TokenSafetyService $tokens,
    ) {}

    public function report(): array
    {
        $sections = [
            'production' => [
                'label' => 'Production readiness',
                'checks' => $this->production->checks(),
            ],
            'email' => [
                'label' => 'Outbound email safety',
                'checks' => $this->email->checks(),
            ],
            'logging' => [
                'label' => 'Logging and redaction',
                'checks' => $this->logging->checks(),
            ],
            'tokens' => [
                'label' => 'Token and signed URL safety',
                'checks' => $this->tokens->checks(),
            ],
        ];

        $checks = collect($sections)->flatMap(fn (array $section): array => $section['checks'])->values();

        return [
            'generated_at' => now()->toIso8601String(),
            'safe_mode' => (bool) config('security.production_error_safe_mode', true),
            'sections' => $sections,
            'summary' => [
                'pass' => $checks->where('status', 'pass')->count(),
                'warning' => $checks->where('status', 'warning')->count(),
                'fail' => $checks->where('status', 'fail')->count(),
                'info' => $checks->where('status', 'info')->count(),
                'total' => $checks->count(),
            ],
        ];
    }

    public function section(string $section): array
    {
        return data_get($this->report(), "sections.{$section}", [
            'label' => str($section)->replace('_', ' ')->title()->toString(),
            'checks' => [],
        ]);
    }
}
