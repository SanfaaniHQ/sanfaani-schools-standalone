<?php

namespace App\Jobs\Updates;

use App\Models\UpdatePackage;
use App\Models\User;
use App\Services\Updates\UpdatePreflightService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunUpdatePreflightJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(
        public int $updatePackageId,
        public ?int $actorId = null,
    ) {
        $this->onQueue('updates');
    }

    public function handle(UpdatePreflightService $preflight): void
    {
        $package = UpdatePackage::find($this->updatePackageId);

        if (! $package) {
            return;
        }

        $preflight->run($package, $this->actorId ? User::find($this->actorId) : null);
    }
}
