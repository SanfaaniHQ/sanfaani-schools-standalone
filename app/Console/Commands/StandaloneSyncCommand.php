<?php

namespace App\Console\Commands;

use App\Services\Standalone\StandaloneSyncService;
use Illuminate\Console\Command;

class StandaloneSyncCommand extends Command
{
    protected $signature = 'standalone:sync {--dry-run : Show pending items without contacting any external service} {--json : Output a JSON sync report}';

    protected $description = 'Run the standalone sync foundation safely. Real transport is disabled until explicitly configured.';

    public function handle(StandaloneSyncService $sync): int
    {
        $result = $sync->runSync((bool) $this->option('dry-run'));

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } elseif ($result['success']) {
            $this->info($result['message']);

            if (array_key_exists('would_sync_count', $result)) {
                $this->line('Pending items: '.$result['would_sync_count']);
            } elseif (array_key_exists('pending_count', $result)) {
                $this->line('Pending items: '.$result['pending_count']);
            }
        } else {
            $this->error($result['message']);
        }

        return $result['success'] ? self::SUCCESS : self::FAILURE;
    }
}
