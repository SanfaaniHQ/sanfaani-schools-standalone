<?php

namespace App\Mail\Marketing;

use App\Models\LeadRequest;
use App\Services\Marketing\UnsubscribeService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialNurtureMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LeadRequest $lead, public array $context = []) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Getting more from your Sanfaani Schools trial');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.marketing.trial-nurture', with: [
            'lead' => $this->lead,
            'context' => $this->context,
            'unsubscribeUrl' => route('marketing.unsubscribe.public', app(UnsubscribeService::class)->tokenForEmail($this->lead->email)),
        ]);
    }
}
