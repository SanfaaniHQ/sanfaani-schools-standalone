<?php

namespace App\Jobs\Marketing;

use App\Services\Marketing\SalesTaskService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateSalesTaskJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(public array $data)
    {
        $this->onQueue((string) config('marketing.queues.default', 'marketing'));
    }

    public function handle(SalesTaskService $tasks): void
    {
        if (! (bool) config('marketing.sales_tasks_enabled', true)) {
            return;
        }

        $tasks->create($this->data);
    }
}
