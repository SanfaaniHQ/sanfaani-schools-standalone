<?php

namespace App\Console\Commands;

use App\Services\Standalone\StandaloneEditionService;
use App\Services\Standalone\StandaloneSyncService;
use Illuminate\Console\Command;

class StandaloneStatusCommand extends Command
{
    protected $signature = 'standalone:status {--json : Output a JSON status report}';

    protected $description = 'Show standalone school local-first/offline configuration and sync foundation status.';

    public function handle(StandaloneEditionService $edition, StandaloneSyncService $sync): int
    {
        $status = $edition->status();
        $syncStatus = $sync->status();

        if ($this->option('json')) {
            $this->line(json_encode([
                'edition' => $status,
                'sync' => $syncStatus,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->line('Product edition: '.$status['product_label']);
        $this->line('Portal mode: '.$status['deployment_mode']);
        $this->line('Installer: '.($status['installer_enabled'] ? 'enabled' : 'disabled'));
        $this->line('Installed: '.($status['installed'] ? 'yes' : 'no'));
        $this->line('Offline mode: '.$status['offline_mode']);
        $this->line('Sync: '.($status['sync_enabled'] ? 'enabled' : 'disabled'));
        $this->line('Sync endpoint: '.($status['sync_endpoint_configured'] ? 'configured' : 'missing'));
        $this->line('Pending outbox items: '.($syncStatus['pending_count'] ?? 'tables not migrated'));

        foreach ($status['warnings'] as $warning) {
            $this->warn($warning);
        }

        return self::SUCCESS;
    }
}
