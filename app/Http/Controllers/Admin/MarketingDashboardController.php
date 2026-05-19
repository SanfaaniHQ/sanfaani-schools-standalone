<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignRecipient;
use App\Services\MarketingAutomationService;

class MarketingDashboardController extends Controller
{
    public function __invoke(MarketingAutomationService $marketing)
    {
        return view('admin.email-marketing.dashboard', [
            'analytics' => $marketing->analytics(),
            'recentCampaigns' => MarketingCampaign::withCount('recipients')
                ->latest()
                ->limit(8)
                ->get(),
            'recentFailures' => MarketingCampaignRecipient::query()
                ->with('campaign:id,name')
                ->where('status', MarketingCampaignRecipient::STATUS_FAILED)
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
