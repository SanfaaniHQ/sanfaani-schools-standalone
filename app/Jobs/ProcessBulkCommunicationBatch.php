<?php

namespace App\Jobs;

use App\Models\BulkCommunicationBatch;
use App\Models\User;
use App\Services\BulkCommunicationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessBulkCommunicationBatch implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 90;

    public function __construct(
        public int $bulkCommunicationBatchId,
        public ?int $actorId = null
    ) {}

    public function handle(BulkCommunicationService $bulkCommunications): void
    {
        $batch = BulkCommunicationBatch::find($this->bulkCommunicationBatchId);

        if (! $batch) {
            return;
        }

        $actor = $this->actorId ? User::find($this->actorId) : null;

        $bulkCommunications->processPendingBatch($batch, $actor);
    }
}
