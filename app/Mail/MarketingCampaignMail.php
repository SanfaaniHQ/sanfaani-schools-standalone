<?php

namespace App\Mail;

use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MarketingCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MarketingCampaign $campaign,
        public MarketingCampaignRecipient $recipient,
        public string $renderedSubject,
        public string $renderedBody,
        public array $trackingUrls = []
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->renderedSubject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.marketing.campaign',
            text: 'emails.marketing.campaign-text',
            with: [
                'campaign' => $this->campaign,
                'recipient' => $this->recipient,
                'renderedBody' => $this->renderedBody,
                'trackingUrls' => $this->trackingUrls,
            ]
        );
    }
}
