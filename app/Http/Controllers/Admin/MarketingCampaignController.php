<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DispatchMarketingCampaign;
use App\Models\LeadRequest;
use App\Models\MarketingCampaign;
use App\Models\MarketingEmailTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketingCampaignController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'status' => ['nullable', Rule::in(MarketingCampaign::STATUSES)],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        return view('admin.email-marketing.campaigns.index', [
            'campaigns' => MarketingCampaign::query()
                ->withCount('recipients')
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
                ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->input('search').'%'))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'statuses' => MarketingCampaign::STATUSES,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create(): View
    {
        return view('admin.email-marketing.campaigns.form', $this->formData(new MarketingCampaign));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        $campaign = MarketingCampaign::create($data);

        if ($request->boolean('send_now')) {
            DispatchMarketingCampaign::dispatch($campaign->id, $request->user()->id)
                ->onQueue((string) config('sanfaani.marketing.queue', 'marketing'));
        }

        return redirect()
            ->route('admin.email-marketing.campaigns.show', $campaign)
            ->with('success', $request->boolean('send_now') ? 'Campaign queued for marketing delivery.' : 'Campaign saved.');
    }

    public function show(MarketingCampaign $campaign): View
    {
        return view('admin.email-marketing.campaigns.show', [
            'campaign' => $campaign->load('template', 'creator')
                ->loadCount([
                    'recipients',
                    'recipients as sent_count' => fn ($query) => $query->whereNotNull('sent_at'),
                    'recipients as failed_count' => fn ($query) => $query->where('status', 'failed'),
                    'recipients as opened_count' => fn ($query) => $query->whereNotNull('opened_at'),
                    'recipients as clicked_count' => fn ($query) => $query->whereNotNull('clicked_at'),
                ]),
            'recipients' => $campaign->recipients()->latest()->paginate(25),
        ]);
    }

    public function edit(MarketingCampaign $campaign): View
    {
        return view('admin.email-marketing.campaigns.form', $this->formData($campaign));
    }

    public function update(Request $request, MarketingCampaign $campaign): RedirectResponse
    {
        $data = $this->validated($request);
        $data['updated_by'] = $request->user()->id;

        $campaign->update($data);

        if ($request->boolean('send_now')) {
            DispatchMarketingCampaign::dispatch($campaign->id, $request->user()->id)
                ->onQueue((string) config('sanfaani.marketing.queue', 'marketing'));
        }

        return redirect()
            ->route('admin.email-marketing.campaigns.show', $campaign)
            ->with('success', $request->boolean('send_now') ? 'Campaign queued for marketing delivery.' : 'Campaign updated.');
    }

    public function duplicate(Request $request, MarketingCampaign $campaign): RedirectResponse
    {
        $copy = $campaign->replicate([
            'status',
            'scheduled_at',
            'sent_at',
            'paused_at',
            'archived_at',
        ]);
        $copy->name = $campaign->name.' Copy';
        $copy->status = MarketingCampaign::STATUS_DRAFT;
        $copy->created_by = $request->user()->id;
        $copy->updated_by = $request->user()->id;
        $copy->save();

        return redirect()
            ->route('admin.email-marketing.campaigns.edit', $copy)
            ->with('success', 'Campaign duplicated as a draft.');
    }

    public function send(Request $request, MarketingCampaign $campaign): RedirectResponse
    {
        DispatchMarketingCampaign::dispatch($campaign->id, $request->user()->id)
            ->onQueue((string) config('sanfaani.marketing.queue', 'marketing'));

        return back()->with('success', 'Campaign queued on the marketing queue.');
    }

    public function pause(MarketingCampaign $campaign): RedirectResponse
    {
        $campaign->forceFill([
            'status' => MarketingCampaign::STATUS_PAUSED,
            'paused_at' => now(),
        ])->save();

        return back()->with('success', 'Campaign paused.');
    }

    public function resume(Request $request, MarketingCampaign $campaign): RedirectResponse
    {
        $campaign->forceFill([
            'status' => MarketingCampaign::STATUS_SENDING,
            'paused_at' => null,
        ])->save();

        DispatchMarketingCampaign::dispatch($campaign->id, $request->user()->id)
            ->onQueue((string) config('sanfaani.marketing.queue', 'marketing'));

        return back()->with('success', 'Campaign resumed.');
    }

    public function archive(MarketingCampaign $campaign): RedirectResponse
    {
        $campaign->forceFill([
            'status' => MarketingCampaign::STATUS_ARCHIVED,
            'archived_at' => now(),
        ])->save();

        return back()->with('success', 'Campaign archived.');
    }

    private function formData(MarketingCampaign $campaign): array
    {
        return [
            'campaign' => $campaign,
            'templates' => MarketingEmailTemplate::where('status', MarketingEmailTemplate::STATUS_ACTIVE)->orderBy('name')->get(),
            'statuses' => MarketingCampaign::STATUSES,
            'targetTypes' => [
                'all_leads' => 'All leads',
                'lead_stage' => 'Lead stages',
                'trial_users' => 'Trial users',
                'inactive_schools' => 'Inactive leads/schools',
                'specific_schools' => 'Specific schools',
                'subscription_plans' => 'Converted schools',
                'tags' => 'Tags',
                'custom_segment' => 'Custom segment',
            ],
            'leadStatuses' => LeadRequest::statusLabels(),
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'marketing_email_template_id' => ['nullable', Rule::exists('marketing_email_templates', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'preview_text' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'status' => ['required', Rule::in(MarketingCampaign::STATUSES)],
            'target_type' => ['required', 'string', 'max:50'],
            'scheduled_at' => ['nullable', 'date'],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['string', Rule::in(LeadRequest::ACCEPTED_STATUSES)],
            'tags' => ['nullable', 'string', 'max:1000'],
            'source' => ['nullable', 'string', 'max:1000'],
            'country' => ['nullable', 'string', 'max:1000'],
            'school_names' => ['nullable', 'string', 'max:3000'],
        ]);

        $data['target_filters'] = [
            'statuses' => $data['statuses'] ?? [],
            'tags' => $this->csv($data['tags'] ?? ''),
            'source' => $this->csv($data['source'] ?? ''),
            'country' => $this->csv($data['country'] ?? ''),
            'school_names' => $this->csv($data['school_names'] ?? ''),
        ];

        unset($data['statuses'], $data['tags'], $data['source'], $data['country'], $data['school_names']);

        if ($request->boolean('send_now')) {
            $data['status'] = MarketingCampaign::STATUS_SCHEDULED;
            $data['scheduled_at'] = now();
        }

        return $data;
    }

    private function csv(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
