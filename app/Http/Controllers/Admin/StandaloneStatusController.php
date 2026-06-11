<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Standalone\StandaloneEditionService;
use App\Services\Standalone\StandaloneSystemHealthService;
use App\Services\Standalone\StandaloneSyncService;
use Illuminate\Contracts\View\View;

class StandaloneStatusController extends Controller
{
    public function __invoke(
        StandaloneEditionService $edition,
        StandaloneSyncService $sync,
        StandaloneSystemHealthService $health,
    ): View
    {
        return view('admin.standalone.status', [
            'editionStatus' => $edition->status(),
            'syncStatus' => $sync->status(),
            'systemHealth' => $health->summary(),
            'pendingOutboxItems' => $sync->pendingOutboxItems(10),
        ]);
    }
}
