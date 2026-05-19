<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Email Marketing</h2>
                <p class="mt-1 text-sm text-text-secondary">Lead nurturing, campaigns, automation, delivery tracking, and suppression safety.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.email-marketing.campaigns.create') }}" class="ui-button-primary">New Campaign</a>
                <a href="{{ route('admin.email-marketing.templates.create') }}" class="ui-button-secondary">New Template</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.notice>{{ session('success') }}</x-ui.notice>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Total Leads" :value="$analytics['total_leads']" meta="Captured lead profiles" />
            <x-ui.stat-card label="Conversion Rate" :value="$analytics['conversion_rate'].'%'" meta="Converted leads" tone="success" />
            <x-ui.stat-card label="Open Rate" :value="$analytics['open_rate'].'%'" meta="Tracked opens" />
            <x-ui.stat-card label="Click Rate" :value="$analytics['click_rate'].'%'" meta="Tracked clicks" />
            <x-ui.stat-card label="Failed Deliveries" :value="$analytics['failed_deliveries']" meta="Needs review" tone="warning" />
            <x-ui.stat-card label="Active Campaigns" :value="$analytics['active_campaigns']" meta="Scheduled or sending" />
            <x-ui.stat-card label="Automations" :value="$analytics['active_automations']" meta="Active workflows" />
            <x-ui.stat-card label="Marketing Queue" value="marketing" meta="Separate from transactional mail" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="ui-card overflow-hidden">
                <div class="border-b border-border-subtle px-5 py-4">
                    <h3 class="font-semibold text-text-primary">Recent Campaigns</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="enterprise-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Recipients</th>
                                <th>Scheduled</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentCampaigns as $campaign)
                                <tr>
                                    <td class="font-semibold">{{ $campaign->name }}</td>
                                    <td><x-status-badge :status="$campaign->status" /></td>
                                    <td>{{ $campaign->recipients_count }}</td>
                                    <td>{{ $campaign->scheduled_at?->format('d M Y H:i') ?? 'Not scheduled' }}</td>
                                    <td><a href="{{ route('admin.email-marketing.campaigns.show', $campaign) }}" class="font-semibold text-brand-primary">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-text-secondary">No campaigns yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="ui-card p-5">
                <h3 class="font-semibold text-text-primary">Top Lead Sources</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($analytics['top_lead_sources'] as $source => $count)
                        <div class="flex items-center justify-between gap-3 rounded-md border border-border-subtle bg-bg-primary px-3 py-2">
                            <span class="truncate text-sm text-text-secondary">{{ $source ?: 'Unknown' }}</span>
                            <span class="font-semibold text-text-primary">{{ $count }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-text-secondary">Lead source analytics will appear after capture.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
