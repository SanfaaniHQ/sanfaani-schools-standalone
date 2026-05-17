<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunicationLog;
use App\Services\MailSettingService;
use Illuminate\Support\Facades\Schema;

class PlatformMailSystemController extends Controller
{
    public function index(MailSettingService $mailSettings)
    {
        $setting = $mailSettings->current();
        $summary = [
            'sent' => 0,
            'failed' => 0,
            'pending' => 0,
            'fallback_used' => 0,
        ];
        $recentFailures = collect();

        if (Schema::hasTable('communication_logs')) {
            $platformLogs = CommunicationLog::query()->whereNull('school_id');
            $summary = [
                'sent' => (clone $platformLogs)->where('status', CommunicationLog::STATUS_SENT)->count(),
                'failed' => (clone $platformLogs)->where('status', CommunicationLog::STATUS_FAILED)->count(),
                'pending' => (clone $platformLogs)->where('status', CommunicationLog::STATUS_PENDING)->count(),
                'fallback_used' => (clone $platformLogs)->where('metadata->delivery->fallback_used', true)->count(),
            ];
            $recentFailures = (clone $platformLogs)
                ->where('status', CommunicationLog::STATUS_FAILED)
                ->latest()
                ->limit(8)
                ->get();
        }

        return view('admin.platform-mail-system.index', [
            'setting' => $setting,
            'governance' => $mailSettings->mailGovernance(),
            'summary' => $summary,
            'recentFailures' => $recentFailures,
        ]);
    }
}
