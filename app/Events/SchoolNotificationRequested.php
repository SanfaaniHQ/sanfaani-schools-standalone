<?php

namespace App\Events;

use App\Models\School;
use App\Models\SchoolSubscription;
use App\Models\SupportThread;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SchoolNotificationRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public School $school,
        public string $eventKey,
        public string $subject,
        public string $headline,
        public string $body,
        public array $targetRoles = ['school_admin'],
        public bool $includeSchoolContact = true,
        public array $metadata = [],
        public string $type = 'school_notification',
        public bool $respectPreferences = true
    ) {}

    public static function subscriptionActivated(SchoolSubscription $subscription): self
    {
        $subscription->loadMissing(['school', 'subscriptionPlan']);
        $school = $subscription->school;
        $plan = $subscription->subscriptionPlan;

        return new self(
            $school,
            'subscription_activated',
            'Subscription activated',
            'Subscription activated',
            'Your school subscription has been activated.'
                ."\nPlan: ".($plan?->name ?? $subscription->plan_name_snapshot ?? 'Assigned plan')
                ."\nStatus: ".ucfirst($subscription->status)
                ."\nBilling cycle: ".str_replace('_', ' ', (string) ($subscription->billing_cycle_snapshot ?: $subscription->billing_cycle ?: 'N/A'))
                ."\nValid until: ".($subscription->ends_at?->format('d M Y') ?? 'N/A'),
            ['school_admin'],
            true,
            [
                'subscription_id' => $subscription->id,
                'subscription_plan_id' => $subscription->subscription_plan_id,
                'plan_name' => $plan?->name ?? $subscription->plan_name_snapshot,
                'status' => $subscription->status,
                'starts_at' => $subscription->starts_at?->toDateString(),
                'ends_at' => $subscription->ends_at?->toDateString(),
                'action_url' => url(route('school.subscription.show', [], false)),
                'action_label' => 'View Subscription',
            ],
            'subscription_activated'
        );
    }

    public static function supportTicketUpdated(SupportThread $thread, string $updateType, array $context = []): self
    {
        $thread->loadMissing('school');
        $school = $thread->school;
        $status = str_replace('_', ' ', $thread->status);
        $priority = str_replace('_', ' ', $thread->priority);
        $message = trim((string) ($context['message'] ?? ''));
        $messageLine = $message !== '' ? "\n\nLatest update:\n".$message : '';
        $eventKey = $updateType === 'status_updated' ? 'support_ticket_status' : 'support_ticket_reply';

        return new self(
            $school,
            $eventKey,
            'Support ticket update: '.$thread->subject,
            'Support ticket update',
            'Ticket #'.$thread->id.' has a support update.'
                ."\nSubject: ".$thread->subject
                ."\nStatus: ".ucfirst($status)
                ."\nPriority: ".ucfirst($priority)
                .$messageLine,
            ['school_admin'],
            true,
            array_merge($context, [
                'support_thread_id' => $thread->id,
                'support_update_type' => $updateType,
                'status' => $thread->status,
                'priority' => $thread->priority,
                'action_url' => url(route('school.support.show', $thread, false)),
                'action_label' => 'Open Ticket',
            ]),
            $eventKey
        );
    }

    public static function systemAnnouncement(
        School $school,
        string $subject,
        string $message,
        array $targetRoles = ['school_admin'],
        bool $includeSchoolContact = true,
        string $segment = 'school'
    ): self {
        return new self(
            $school,
            'system_announcement',
            $subject,
            'System announcement',
            $message,
            $targetRoles,
            $includeSchoolContact,
            [
                'segment' => $segment,
                'target_roles' => $targetRoles,
                'include_school_contact' => $includeSchoolContact,
            ],
            'system_announcement'
        );
    }
}
