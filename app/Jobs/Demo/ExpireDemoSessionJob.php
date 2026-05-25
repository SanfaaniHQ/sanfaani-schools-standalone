<?php

namespace App\Jobs\Demo;

use App\Models\DemoSession;
use App\Models\User;
use App\Services\Demo\DemoExpiryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExpireDemoSessionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DemoSession $demoSession,
        public bool $manual = false,
        public ?User $actor = null,
    ) {}

    public function handle(DemoExpiryService $expiry): void
    {
        $expiry->expire($this->demoSession, $this->manual, $this->actor);
    }
}
