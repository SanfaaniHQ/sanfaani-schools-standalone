<?php

namespace App\Http\Controllers;

use App\Models\MarketingCampaignRecipient;
use App\Services\MarketingAutomationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MarketingTrackingController extends Controller
{
    public function open(Request $request, MarketingCampaignRecipient $recipient, MarketingAutomationService $marketing): Response
    {
        $marketing->recordOpen($recipient, [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response(base64_decode('R0lGODlhAQABAPAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function click(Request $request, MarketingCampaignRecipient $recipient, MarketingAutomationService $marketing): RedirectResponse
    {
        $url = $request->query('url', config('sanfaani.product_url'));

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $url = config('sanfaani.product_url');
        }

        $marketing->recordClick($recipient, $url, [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->away($url);
    }

    public function unsubscribe(Request $request, MarketingCampaignRecipient $recipient, MarketingAutomationService $marketing)
    {
        $marketing->suppress($recipient->email, 'unsubscribed', metadata: [
            'source' => 'email_footer',
            'campaign_id' => $recipient->marketing_campaign_id,
            'recipient_id' => $recipient->id,
            'ip' => $request->ip(),
        ]);

        $recipient->forceFill([
            'status' => MarketingCampaignRecipient::STATUS_UNSUBSCRIBED,
            'unsubscribed_at' => now(),
        ])->save();

        return view('public.marketing.unsubscribe', [
            'email' => $recipient->email,
        ]);
    }
}
