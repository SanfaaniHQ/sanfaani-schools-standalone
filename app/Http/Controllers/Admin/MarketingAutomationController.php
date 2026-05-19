<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunMarketingAutomations;
use App\Models\LeadRequest;
use App\Models\MarketingAutomation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketingAutomationController extends Controller
{
    public function index(): View
    {
        return view('admin.email-marketing.automations.index', [
            'automations' => MarketingAutomation::latest()->paginate(15),
            'leadStatuses' => LeadRequest::statusLabels(),
            'triggerTypes' => $this->triggerTypes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        MarketingAutomation::create($data);

        return back()->with('success', 'Automation saved.');
    }

    public function update(Request $request, MarketingAutomation $automation): RedirectResponse
    {
        $data = $this->validated($request);
        $data['updated_by'] = $request->user()->id;

        $automation->update($data);

        return back()->with('success', 'Automation updated.');
    }

    public function run(Request $request): RedirectResponse
    {
        RunMarketingAutomations::dispatch($request->user()->id)
            ->onQueue((string) config('sanfaani.marketing.queue', 'marketing'));

        return back()->with('success', 'Automation run queued.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trigger_type' => ['required', Rule::in(array_keys($this->triggerTypes()))],
            'status' => ['required', Rule::in([MarketingAutomation::STATUS_ACTIVE, MarketingAutomation::STATUS_PAUSED])],
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['string', Rule::in(LeadRequest::ACCEPTED_STATUSES)],
            'step_subject' => ['required', 'string', 'max:255'],
            'step_preview_text' => ['nullable', 'string', 'max:255'],
            'step_body' => ['required', 'string'],
        ]);

        return [
            'name' => $data['name'],
            'trigger_type' => $data['trigger_type'],
            'status' => $data['status'],
            'audience_filters' => [
                'statuses' => $data['statuses'] ?? [],
            ],
            'steps' => [[
                'subject' => $data['step_subject'],
                'preview_text' => $data['step_preview_text'] ?? null,
                'body' => $data['step_body'],
            ]],
        ];
    }

    private function triggerTypes(): array
    {
        return [
            'welcome_sequence' => 'Welcome Sequence',
            'trial_nurturing' => 'Trial Nurturing',
            'trial_expiry' => 'Trial Expiry',
            'inactive_recovery' => 'Inactive Lead Recovery',
            'product_announcement' => 'Product Announcement',
        ];
    }
}
