<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Performance\CacheReadinessService;
use App\Services\Performance\LogReadinessService;
use App\Services\Performance\PerformanceAuditService;
use App\Services\Performance\QueueReadinessService;
use App\Services\Performance\SharedHostingLimitService;
use App\Services\System\DeploymentModeService;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    public function __construct(
        private PerformanceAuditService $audit,
        private SharedHostingLimitService $sharedHosting,
        private CacheReadinessService $cache,
        private QueueReadinessService $queues,
        private LogReadinessService $logs,
        private DeploymentModeService $deployment,
    ) {}

    public function index(): View
    {
        return view('admin.performance.index', [
            'label' => $this->label(),
            'report' => $this->audit->report(),
        ]);
    }

    public function audit(): View
    {
        return view('admin.performance.audit', [
            'label' => $this->label(),
            'report' => $this->audit->report(),
        ]);
    }

    public function sharedHosting(): View
    {
        return view('admin.performance.shared-hosting', [
            'label' => $this->label(),
            'checks' => $this->sharedHosting->checks(),
            'recommendations' => $this->sharedHosting->recommendations(),
        ]);
    }

    public function cache(): View
    {
        return view('admin.performance.cache', [
            'label' => $this->label(),
            'checks' => $this->cache->checks(),
        ]);
    }

    public function queues(): View
    {
        return view('admin.performance.queues', [
            'label' => $this->label(),
            'checks' => $this->queues->checks(),
        ]);
    }

    public function logs(): View
    {
        return view('admin.performance.logs', [
            'label' => $this->label(),
            'checks' => $this->logs->checks(),
        ]);
    }

    private function label(): string
    {
        return (string) config(
            'performance.labels.'.$this->deployment->mode(),
            str($this->deployment->mode())->replace('_', ' ')->title().' Performance',
        );
    }
}
