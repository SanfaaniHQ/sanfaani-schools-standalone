<?php

namespace App\Services;

use App\Jobs\SendMarketingCampaignEmail;
use App\Mail\MarketingCampaignMail;
use App\Models\LeadRequest;
use App\Models\MarketingAutomation;
use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignRecipient;
use App\Models\MarketingDeliveryEvent;
use App\Models\MarketingSuppression;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class MarketingAutomationService
{
    public function __construct(
        private MailSettingService $mailSettings,
        private LeadCrmService $leadCrm
    ) {}

    public function analytics(): array
    {
        $totalLeads = Schema::hasTable('lead_requests') ? LeadRequest::count() : 0;
        $convertedLeads = Schema::hasTable('lead_requests')
            ? LeadRequest::query()->whereNotNull('converted_at')->orWhere('status', LeadRequest::STATUS_CONVERTED)->count()
            : 0;
        $sent = MarketingCampaignRecipient::whereNotNull('sent_at')->count();
        $opened = MarketingCampaignRecipient::whereNotNull('opened_at')->count();
        $clicked = MarketingCampaignRecipient::whereNotNull('clicked_at')->count();

        return [
            'total_leads' => $totalLeads,
            'conversion_rate' => $this->rate($convertedLeads, $totalLeads),
            'open_rate' => $this->rate($opened, $sent),
            'click_rate' => $this->rate($clicked, $sent),
            'failed_deliveries' => MarketingCampaignRecipient::where('status', MarketingCampaignRecipient::STATUS_FAILED)->count(),
            'active_campaigns' => MarketingCampaign::whereIn('status', [
                MarketingCampaign::STATUS_SCHEDULED,
                MarketingCampaign::STATUS_SENDING,
            ])->count(),
            'active_automations' => MarketingAutomation::where('status', MarketingAutomation::STATUS_ACTIVE)->count(),
            'top_lead_sources' => Schema::hasTable('lead_requests')
                ? LeadRequest::query()
                    ->select('source', DB::raw('COUNT(*) as aggregate'))
                    ->groupBy('source')
                    ->orderByDesc('aggregate')
                    ->limit(6)
                    ->pluck('aggregate', 'source')
                    ->all()
                : [],
        ];
    }

    public function dispatchCampaign(MarketingCampaign $campaign, ?User $actor = null): int
    {
        if (! $campaign->canDispatch() && $campaign->status !== MarketingCampaign::STATUS_SENDING) {
            return 0;
        }

        $campaign->forceFill([
            'status' => MarketingCampaign::STATUS_SENDING,
            'updated_by' => $actor?->id,
        ])->save();

        $queue = (string) config('sanfaani.marketing.queue', 'marketing');
        $chunkSize = max(25, (int) config('sanfaani.marketing.chunk_size', 100));
        $delaySeconds = max(0, (int) config('sanfaani.marketing.dispatch_delay_seconds', 2));
        $queued = 0;

        $this->audienceQuery($campaign)
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->orderBy('id')
            ->chunkById($chunkSize, function ($leads) use ($campaign, $queue, $delaySeconds, &$queued) {
                foreach ($leads as $lead) {
                    $email = Str::lower(trim((string) $lead->email));

                    if ($email === '' || $this->isSuppressed($email)) {
                        $this->recordEvent($campaign, null, 'skipped', $email, metadata: ['reason' => 'suppressed_or_blank']);
                        continue;
                    }

                    $recipient = MarketingCampaignRecipient::firstOrCreate(
                        [
                            'marketing_campaign_id' => $campaign->id,
                            'email' => $email,
                        ],
                        [
                            'lead_request_id' => $lead->id,
                            'name' => $lead->name,
                            'school_name' => $lead->school_name,
                            'status' => MarketingCampaignRecipient::STATUS_QUEUED,
                            'queued_at' => now(),
                            'metadata' => [
                                'source' => $lead->source,
                                'lead_status' => $lead->status,
                            ],
                        ]
                    );

                    if ($recipient->wasRecentlyCreated || $recipient->status === MarketingCampaignRecipient::STATUS_QUEUED) {
                        SendMarketingCampaignEmail::dispatch($recipient->id)
                            ->onQueue($queue)
                            ->delay(now()->addSeconds($queued * $delaySeconds));
                        $queued++;
                        $this->recordEvent($campaign, $recipient, 'queued', $email);
                    }
                }
            });

        if ($queued === 0 && ! $campaign->recipients()->where('status', MarketingCampaignRecipient::STATUS_QUEUED)->exists()) {
            $campaign->forceFill([
                'status' => MarketingCampaign::STATUS_SENT,
                'sent_at' => now(),
            ])->save();
        }

        return $queued;
    }

    public function sendRecipient(MarketingCampaignRecipient $recipient): void
    {
        $recipient->loadMissing(['campaign', 'lead']);
        $campaign = $recipient->campaign;

        if (! $campaign || $campaign->status === MarketingCampaign::STATUS_PAUSED || $campaign->status === MarketingCampaign::STATUS_ARCHIVED) {
            return;
        }

        if ($this->isSuppressed($recipient->email)) {
            $recipient->forceFill([
                'status' => MarketingCampaignRecipient::STATUS_SKIPPED,
                'failure_reason' => 'Recipient is on the marketing suppression list.',
            ])->save();
            $this->recordEvent($campaign, $recipient, 'skipped', $recipient->email, metadata: ['reason' => 'suppressed']);

            return;
        }

        try {
            $this->mailSettings->withPlatformMailContext(function () use ($campaign, $recipient) {
                Mail::to($recipient->email)->send(new MarketingCampaignMail(
                    $campaign,
                    $recipient,
                    $this->renderSubject($campaign, $recipient),
                    $this->renderBody($campaign, $recipient),
                    $this->trackingUrls($recipient)
                ));
            });

            $recipient->forceFill([
                'status' => MarketingCampaignRecipient::STATUS_SENT,
                'sent_at' => now(),
                'failure_reason' => null,
            ])->save();

            $this->recordEvent($campaign, $recipient, 'sent', $recipient->email);

            if ($recipient->lead) {
                $this->leadCrm->recordSystemEvent($recipient->lead, 'marketing_email_sent', $campaign->name, null, [
                    'campaign_id' => $campaign->id,
                    'recipient_id' => $recipient->id,
                ]);
            }
        } catch (Throwable $exception) {
            $recipient->forceFill([
                'status' => MarketingCampaignRecipient::STATUS_FAILED,
                'failure_reason' => Str::limit($exception->getMessage(), 4000, ''),
            ])->save();

            $this->recordEvent($campaign, $recipient, 'failed', $recipient->email, metadata: [
                'error' => $exception->getMessage(),
            ]);
        } finally {
            $this->markCampaignFinishedIfComplete($campaign);
        }
    }

    public function recordOpen(MarketingCampaignRecipient $recipient, array $metadata = []): void
    {
        $recipient->forceFill([
            'opened_at' => $recipient->opened_at ?: now(),
        ])->save();

        $this->recordEvent($recipient->campaign, $recipient, 'opened', $recipient->email, metadata: $metadata);
    }

    public function recordClick(MarketingCampaignRecipient $recipient, string $url, array $metadata = []): void
    {
        $recipient->forceFill([
            'clicked_at' => $recipient->clicked_at ?: now(),
        ])->save();

        $this->recordEvent($recipient->campaign, $recipient, 'clicked', $recipient->email, $url, $metadata);
    }

    public function suppress(string $email, string $reason = 'unsubscribed', ?User $actor = null, array $metadata = []): MarketingSuppression
    {
        return MarketingSuppression::updateOrCreate(
            ['email' => Str::lower(trim($email))],
            [
                'reason' => $reason,
                'source' => $metadata['source'] ?? 'marketing',
                'suppressed_at' => now(),
                'created_by' => $actor?->id,
                'metadata' => $metadata,
            ]
        );
    }

    public function runAutomations(?User $actor = null): int
    {
        $dispatched = 0;

        MarketingAutomation::where('status', MarketingAutomation::STATUS_ACTIVE)
            ->orderBy('id')
            ->chunkById(25, function ($automations) use ($actor, &$dispatched) {
                foreach ($automations as $automation) {
                    foreach ((array) $automation->steps as $index => $step) {
                        if (! filled($step['subject'] ?? null) || ! filled($step['body'] ?? null)) {
                            continue;
                        }

                        $campaign = MarketingCampaign::create([
                            'name' => $automation->name.' - Step '.($index + 1).' '.now()->format('YmdHi'),
                            'subject' => $step['subject'],
                            'preview_text' => $step['preview_text'] ?? null,
                            'body' => $step['body'],
                            'status' => MarketingCampaign::STATUS_SCHEDULED,
                            'target_type' => $automation->trigger_type,
                            'target_filters' => $automation->audience_filters ?? [],
                            'scheduled_at' => now(),
                            'created_by' => $actor?->id,
                            'metadata' => [
                                'automation_id' => $automation->id,
                                'automation_step' => $index,
                            ],
                        ]);

                        $dispatched += $this->dispatchCampaign($campaign, $actor);
                    }

                    $automation->forceFill(['last_run_at' => now()])->save();
                }
            });

        return $dispatched;
    }

    public function renderSubject(MarketingCampaign $campaign, MarketingCampaignRecipient $recipient): string
    {
        return $this->replacePlaceholders($campaign->subject, $campaign, $recipient);
    }

    public function renderBody(MarketingCampaign $campaign, MarketingCampaignRecipient $recipient): string
    {
        return $this->replacePlaceholders($campaign->body, $campaign, $recipient);
    }

    public function trackingUrls(MarketingCampaignRecipient $recipient): array
    {
        $demoUrl = config('sanfaani.product_url');

        return [
            'open_url' => Route::has('marketing.track.open') ? URL::signedRoute('marketing.track.open', $recipient) : null,
            'unsubscribe_url' => Route::has('marketing.unsubscribe') ? URL::signedRoute('marketing.unsubscribe', $recipient) : null,
            'demo_link' => Route::has('marketing.track.click')
                ? URL::signedRoute('marketing.track.click', ['recipient' => $recipient, 'url' => $demoUrl])
                : $demoUrl,
        ];
    }

    private function audienceQuery(MarketingCampaign $campaign): Builder
    {
        $query = LeadRequest::query();
        $filters = $campaign->target_filters ?? [];

        match ($campaign->target_type) {
            'lead_stage', 'welcome_sequence', 'trial_nurturing', 'trial_expiry', 'inactive_recovery' => $query->when(
                filled($filters['statuses'] ?? null),
                fn (Builder $query) => $query->whereIn('status', (array) $filters['statuses'])
            ),
            'trial_users' => $query->where('status', LeadRequest::STATUS_TRIAL_STARTED),
            'inactive_schools' => $query->whereIn('status', [LeadRequest::STATUS_INACTIVE, LeadRequest::STATUS_LOST_LEAD, LeadRequest::STATUS_LOST]),
            'specific_schools' => $query->when(
                filled($filters['school_names'] ?? null),
                fn (Builder $query) => $query->whereIn('school_name', (array) $filters['school_names'])
            ),
            'subscription_plans' => $query->whereNotNull('converted_school_id'),
            'tags' => $this->applyTagFilter($query, (array) ($filters['tags'] ?? [])),
            default => null,
        };

        if (filled($filters['source'] ?? null)) {
            $query->whereIn('source', (array) $filters['source']);
        }

        if (filled($filters['country'] ?? null) && Schema::hasColumn('lead_requests', 'country')) {
            $query->whereIn('country', (array) $filters['country']);
        }

        return $query;
    }

    private function applyTagFilter(Builder $query, array $tags): void
    {
        if ($tags === [] || ! Schema::hasColumn('lead_requests', 'tags')) {
            return;
        }

        $query->where(function (Builder $query) use ($tags) {
            foreach ($tags as $tag) {
                $query->orWhereJsonContains('tags', $tag);
            }
        });
    }

    private function replacePlaceholders(string $content, MarketingCampaign $campaign, MarketingCampaignRecipient $recipient): string
    {
        $recipient->loadMissing('lead');
        $lead = $recipient->lead;
        $settings = app(PlatformSettingService::class)->get();
        $tracking = $this->trackingUrls($recipient);

        $values = [
            'school_name' => $recipient->school_name ?: $lead?->school_name ?: 'your school',
            'admin_name' => $recipient->name ?: $lead?->name ?: 'there',
            'plan_name' => data_get($lead?->metadata, 'plan_name', 'your plan'),
            'demo_link' => $tracking['demo_link'] ?? config('sanfaani.product_url'),
            'subscription_expiry' => data_get($lead?->metadata, 'subscription_expiry', 'not set'),
            'platform_name' => $settings->platform_name ?? config('app.name', 'Sanfaani Schools'),
            'unsubscribe_url' => $tracking['unsubscribe_url'] ?? '#',
        ];

        foreach ($values as $key => $value) {
            $content = str_replace('{{'.$key.'}}', (string) $value, $content);
            $content = str_replace('{{ '.$key.' }}', (string) $value, $content);
        }

        return $content;
    }

    private function recordEvent(
        ?MarketingCampaign $campaign,
        ?MarketingCampaignRecipient $recipient,
        string $eventType,
        ?string $email = null,
        ?string $url = null,
        array $metadata = []
    ): void {
        if (! Schema::hasTable('marketing_delivery_events')) {
            return;
        }

        MarketingDeliveryEvent::create([
            'marketing_campaign_id' => $campaign?->id ?? $recipient?->marketing_campaign_id,
            'marketing_campaign_recipient_id' => $recipient?->id,
            'lead_request_id' => $recipient?->lead_request_id,
            'event_type' => $eventType,
            'email' => $email,
            'url' => $url,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    private function markCampaignFinishedIfComplete(MarketingCampaign $campaign): void
    {
        if ($campaign->recipients()->where('status', MarketingCampaignRecipient::STATUS_QUEUED)->exists()) {
            return;
        }

        $campaign->forceFill([
            'status' => MarketingCampaign::STATUS_SENT,
            'sent_at' => $campaign->sent_at ?: now(),
        ])->save();
    }

    private function isSuppressed(string $email): bool
    {
        return MarketingSuppression::where('email', Str::lower(trim($email)))->exists();
    }

    private function rate(int $numerator, int $denominator): int
    {
        return $denominator > 0 ? (int) round(($numerator / $denominator) * 100) : 0;
    }
}
