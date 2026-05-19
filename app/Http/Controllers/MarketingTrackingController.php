<?php

namespace App\Http\Controllers;

use App\Models\MarketingCampaignRecipient;
use App\Services\MarketingAutomationService;
use App\Support\MailSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MarketingTrackingController extends Controller
{
    public function open(Request $request, MarketingCampaignRecipient $recipient, MarketingAutomationService $marketing): Response
    {
        $marketing->recordOpen($recipient, $this->requestMetadata($request));

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

        $marketing->recordClick($recipient, $url, $this->requestMetadata($request));

        return redirect()->away($url);
    }

    public function unsubscribe(Request $request, MarketingCampaignRecipient $recipient, MarketingAutomationService $marketing)
    {
        $marketing->suppress($recipient->email, 'unsubscribed', metadata: [
            'source' => 'email_footer',
            'campaign_id' => $recipient->marketing_campaign_id,
            'recipient_id' => $recipient->id,
            ...$this->requestMetadata($request),
        ]);

        $recipient->forceFill([
            'status' => MarketingCampaignRecipient::STATUS_UNSUBSCRIBED,
            'unsubscribed_at' => now(),
        ])->save();

        return view('public.marketing.unsubscribe', [
            'email' => $recipient->email,
        ]);
    }

    public function openToken(Request $request, string $token, MarketingAutomationService $marketing): Response
    {
        return $this->open($request, $this->recipientFromToken($token), $marketing);
    }

    public function clickToken(Request $request, string $token, MarketingAutomationService $marketing): RedirectResponse
    {
        return $this->click($request, $this->recipientFromToken($token), $marketing);
    }

    public function unsubscribeToken(Request $request, string $token, MarketingAutomationService $marketing)
    {
        return $this->unsubscribe($request, $this->recipientFromToken($token), $marketing);
    }

    private function recipientFromToken(string $token): MarketingCampaignRecipient
    {
        $recipient = MarketingCampaignRecipient::where('tracking_token', $token)->first();

        if (! $recipient) {
            throw new NotFoundHttpException;
        }

        return $recipient;
    }

    private function requestMetadata(Request $request): array
    {
        return array_filter([
            'ip_hash' => MailSecurity::fingerprint($request->ip()),
            'user_agent_hash' => MailSecurity::fingerprint($request->userAgent()),
        ]);
    }
}
