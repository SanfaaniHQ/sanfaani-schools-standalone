<?php

namespace App\Mail;

use App\Models\DemoSession;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DemoCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DemoSession $demoSession) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Sanfaani Schools demo access');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.demo.credentials',
            with: [
                'demoSession' => $this->demoSession,
            ]
        );
    }
}
