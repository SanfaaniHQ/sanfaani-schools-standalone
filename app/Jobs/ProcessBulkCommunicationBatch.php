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

    public int $maxExceptions = 2;

    public function __construct(
        public int $bulkCommunicationBatchId,
        public ?int $actorId = null,
        public ?int $schoolId = null
    ) {}

    public function handle(BulkCommunicationService $bulkCommunications): void
    {
        $batch = BulkCommunicationBatch::find($this->bulkCommunicationBatchId);

        if (! $batch) {
            return;
        }

        if ($this->schoolId && (int) $batch->school_id !== (int) $this->schoolId) {
            return;
        }

        $actor = $this->actorId ? User::find($this->actorId) : null;

        $bulkCommunications->processPendingBatch($batch, $actor);
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHour();
    }
}
