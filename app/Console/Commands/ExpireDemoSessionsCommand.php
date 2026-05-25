<?php

namespace App\Console\Commands;

use App\Services\Demo\DemoExpiryService;
use Illuminate\Console\Command;

class ExpireDemoSessionsCommand extends Command
{
    protected $signature = 'demo:expire-sessions';

    protected $description = 'Expire due Sanfaani demo sessions and revoke temporary demo credentials.';

    public function handle(DemoExpiryService $expiry): int
    {
        $count = $expiry->expireDueSessions();

        $this->info("Expired {$count} demo session(s).");

        return self::SUCCESS;
    }
}
