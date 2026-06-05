<?php

namespace App\Console\Commands;

use App\Services\Demo\MarketplaceLiveDemoService;
use Illuminate\Console\Command;

class SeedMarketplaceDemoCommand extends Command
{
    protected $signature = 'demo:seed-marketplace';

    protected $description = 'Seed the public marketplace live demo school, users, and safe credentials.';

    public function handle(MarketplaceLiveDemoService $demo): int
    {
        $result = $demo->seed();

        $this->info('Marketplace live demo seeded.');
        $this->line('School: '.$result['school']->name.' ('.$result['school']->slug.')');
        $this->line('Users: '.$result['users']->count());
        $this->line('Credentials: '.$result['credentials']->count());
        $this->line('Reset window: '.$demo->resetHours().' hour(s).');

        return self::SUCCESS;
    }
}
