<?php

namespace App\Console\Commands;

use App\Services\Security\SecurityAuditService;
use Illuminate\Console\Command;

class SecurityAuditCommand extends Command
{
    protected $signature = 'security:audit {--json : Output a JSON security readiness report}';

    protected $description = 'Read-only production security and outbound email safety diagnostics.';

    public function handle(SecurityAuditService $audit): int
    {
        $report = $audit->report();

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        foreach ($report['sections'] as $section) {
            $this->line('');
            $this->line($section['label']);

            foreach ($section['checks'] as $check) {
                $this->line(sprintf(
                    '[%s] %s: %s',
                    strtoupper((string) $check['status']),
                    $check['label'],
                    $check['message'],
                ));
            }
        }

        $summary = $report['summary'];

        $this->line('');
        $this->info(sprintf(
            'Security audit complete: %d pass, %d warning, %d fail, %d info. No files were modified. No emails were sent.',
            $summary['pass'],
            $summary['warning'],
            $summary['fail'],
            $summary['info'],
        ));
        $this->line('No files were modified.');
        $this->line('No emails were sent.');

        return self::SUCCESS;
    }
}
