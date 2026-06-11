<?php

namespace App\Console\Commands;

use App\Services\Standalone\StandaloneSchedulerHeartbeatService;
use Illuminate\Console\Command;
use Throwable;

class RecordStandaloneSchedulerHeartbeatCommand extends Command
{
    protected $signature = 'standalone:scheduler-heartbeat {--json : Output the heartbeat status as JSON}';

    protected $description = 'Record a safe heartbeat showing that Laravel scheduler cron is running.';

    public function handle(StandaloneSchedulerHeartbeatService $heartbeat): int
    {
        try {
            $status = $heartbeat->record();
        } catch (Throwable $exception) {
            $status = [
                'status' => 'failed',
                'label' => 'Failed',
                'message' => 'Scheduler heartbeat could not be recorded.',
            ];
        }

        if ($this->option('json')) {
            $this->line(json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $status['status'] === 'failed' ? self::FAILURE : self::SUCCESS;
        }

        if ($status['status'] === 'failed') {
            $this->error($status['message']);

            return self::FAILURE;
        }

        $this->info('Standalone scheduler heartbeat recorded: '.$status['label']);

        return self::SUCCESS;
    }
}
