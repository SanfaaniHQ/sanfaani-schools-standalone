<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ReportCardSnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportCardSnapshotController extends Controller
{
    public function show(Request $request, ReportCardSnapshot $snapshot): View
    {
        $snapshot->loadMissing(['school']);

        abort_unless($snapshot->status === 'active' && $snapshot->school?->status === 'active', 404);

        $expiresAt = $request->query('expires')
            ? CarbonImmutable::createFromTimestamp((int) $request->query('expires'))
            : null;

        return view('public.report-cards.show', [
            'snapshot' => $snapshot,
            'expiresAt' => $expiresAt,
            'printMode' => $request->boolean('print'),
        ]);
    }
}
