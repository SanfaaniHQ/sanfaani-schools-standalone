<?php

namespace App\Jobs\Demo;

use App\Models\DemoRequest;
use App\Models\User;
use App\Services\Demo\DemoEnvironmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateDemoEnvironmentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DemoRequest $demoRequest,
        public ?User $creator = null,
    ) {}

    public function handle(DemoEnvironmentService $environments): void
    {
        $environments->createEnvironment($this->demoRequest, $this->creator);
    }
}
