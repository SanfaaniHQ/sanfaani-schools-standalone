<?php

namespace App\Jobs\Marketing;

use App\Mail\Marketing\LeadFollowUpMail;
use App\Mail\Marketing\RenewalReminderMail;
use App\Mail\Marketing\TrialNurtureMail;
use App\Models\LeadRequest;
use App\Services\MailSettingService;
use App\Services\Marketing\UnsubscribeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendMarketingEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 60;

    public function __construct(
        public int $leadRequestId,
        public string $mailType = 'lead_follow_up',
        public array $context = [],
    ) {
        $this->onQueue((string) config('marketing.queues.default', 'marketing'));
    }

    public function handle(UnsubscribeService $unsubscribes): void
    {
        if (! (bool) config('marketing.email_enabled', true)) {
            return;
        }

        if ($this->mailType === 'renewal_reminder'
            && ! (bool) config('sanfaani.license_validation_enabled', false)) {
            return;
        }

        $lead = LeadRequest::find($this->leadRequestId);

        if (! $lead || blank($lead->email) || $unsubscribes->isUnsubscribed($lead->email)) {
            return;
        }

        $mailable = match ($this->mailType) {
            'trial_nurture' => new TrialNurtureMail($lead, $this->context),
            'renewal_reminder' => new RenewalReminderMail($lead, $this->context),
            default => new LeadFollowUpMail($lead, $this->context),
        };

        app(MailSettingService::class)->withPlatformMailContext(function () use ($lead, $mailable): void {
            Mail::to($lead->email)->send($mailable);
        });
    }
}
