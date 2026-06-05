<?php

namespace App\Console\Commands;

use App\Services\Demo\MarketplaceLiveDemoService;
use Illuminate\Console\Command;

class ResetMarketplaceDemoCommand extends Command
{
    protected $signature = 'demo:reset-marketplace {--dry-run : Show the scoped reset plan without refreshing demo records}';

    protected $description = 'Safely refresh only the public marketplace demo school, users, and credentials.';

    public function handle(MarketplaceLiveDemoService $demo): int
    {
        if ($this->option('dry-run')) {
            $this->info('Marketplace demo reset dry run.');
            $this->line('Would refresh the configured demo school, known demo users, known credentials, and sample fake data.');
            $this->line('Would not delete schools, users, or data outside the configured marketplace demo school.');

            return self::SUCCESS;
        }

        $result = $demo->seed();

        $this->info('Marketplace live demo reset refreshed.');
        $this->line('School: '.$result['school']->name.' ('.$result['school']->slug.')');
        $this->line('Users: '.$result['users']->count());
        $this->line('Credentials: '.$result['credentials']->count());
        $this->line('No non-demo schools or users were touched.');

        return self::SUCCESS;
    }
}
