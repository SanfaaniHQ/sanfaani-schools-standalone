<?php

namespace App\Console\Commands;

use App\Services\Updates\UpdateLogService;
use App\Services\Updates\UpdateServerClient;
use Illuminate\Console\Command;

class CheckForUpdatesCommand extends Command
{
    protected $signature = 'updates:check {--channel= : Optional update channel to check}';

    protected $description = 'Safely check the local update configuration without making external network requests.';

    public function handle(UpdateServerClient $client, UpdateLogService $logs): int
    {
        $channel = $this->option('channel') ?: (string) config('updates.channel', 'stable');
        $result = $client->checkForUpdates((string) $channel);

        $logs->log(
            'update.check_stubbed',
            'Update check completed through the safe local stub. No external network request was made.',
            severity: 'info',
            context: $result,
        );

        $this->info('Update check completed safely.');
        $this->line('Channel: '.$result['channel']);
        $this->line('Server configured: '.($result['server_configured'] ? 'yes' : 'no'));
        $this->line('No external network request was made.');

        return self::SUCCESS;
    }
}
