<?php

namespace App\Console\Commands;

use App\Services\Performance\PerformanceAuditService;
use Illuminate\Console\Command;

class PerformanceAuditCommand extends Command
{
    protected $signature = 'performance:audit {--json : Output a JSON performance readiness report}';

    protected $description = 'Read-only shared-hosting and performance readiness diagnostics.';

    public function handle(PerformanceAuditService $audit): int
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
            'Performance audit complete: %d pass, %d warning, %d fail, %d info. No files were modified.',
            $summary['pass'],
            $summary['warning'],
            $summary['fail'],
            $summary['info'],
        ));
        $this->line('No files were modified.');

        return self::SUCCESS;
    }
}
