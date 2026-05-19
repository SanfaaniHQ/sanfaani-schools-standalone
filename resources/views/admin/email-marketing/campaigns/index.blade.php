<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-text-primary">Campaigns</h2>
                <p class="mt-1 text-sm text-text-secondary">Draft, schedule, pause, resume, archive, and track marketing campaigns.</p>
            </div>
            <a href="{{ route('admin.email-marketing.campaigns.create') }}" class="ui-button-primary">Create Campaign</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.notice>{{ session('success') }}</x-ui.notice>
        @endif

        <form method="GET" class="grid gap-3 rounded-lg border border-border-subtle bg-bg-secondary p-4 md:grid-cols-4">
            <input name="search" value="{{ $filters['search'] ?? '' }}" class="ui-input md:col-span-2" placeholder="Search campaigns">
            <select name="status" class="ui-input">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="ui-button-primary">Filter</button>
                <a href="{{ route('admin.email-marketing.campaigns.index') }}" class="ui-button-secondary">Reset</a>
            </div>
        </form>

        <div class="ui-table-wrap">
            <table class="enterprise-table">
                <thead>
                    <tr>
                        <th>Campaign</th>
                        <th>Status</th>
                        <th>Target</th>
                        <th>Recipients</th>
                        <th>Schedule</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($campaigns as $campaign)
                        <tr>
                            <td>
                                <div class="font-semibold text-text-primary">{{ $campaign->name }}</div>
                                <div class="text-xs text-text-tertiary">{{ $campaign->subject }}</div>
                            </td>
                            <td><x-status-badge :status="$campaign->status" /></td>
                            <td>{{ str($campaign->target_type)->replace('_', ' ')->title() }}</td>
                            <td>{{ $campaign->recipients_count }}</td>
                            <td>{{ $campaign->scheduled_at?->format('d M Y H:i') ?? 'Draft' }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.email-marketing.campaigns.show', $campaign) }}" class="font-semibold text-brand-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-text-secondary">No marketing campaigns found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $campaigns->links() }}
    </div>
</x-app-layout>
