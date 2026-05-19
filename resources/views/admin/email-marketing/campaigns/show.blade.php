<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-text-primary">{{ $campaign->name }}</h2>
                <p class="mt-1 text-sm text-text-secondary">{{ $campaign->subject }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.email-marketing.campaigns.edit', $campaign) }}" class="ui-button-secondary">Edit</a>
                <form method="POST" action="{{ route('admin.email-marketing.campaigns.send', $campaign) }}">@csrf<button class="ui-button-primary">Send</button></form>
                <form method="POST" action="{{ route('admin.email-marketing.campaigns.duplicate', $campaign) }}">@csrf<button class="ui-button-secondary">Duplicate</button></form>
                <form method="POST" action="{{ route('admin.email-marketing.campaigns.pause', $campaign) }}">@csrf<button class="ui-button-secondary">Pause</button></form>
                <form method="POST" action="{{ route('admin.email-marketing.campaigns.resume', $campaign) }}">@csrf<button class="ui-button-secondary">Resume</button></form>
                <form method="POST" action="{{ route('admin.email-marketing.campaigns.archive', $campaign) }}">@csrf<button class="ui-button-danger">Archive</button></form>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.notice>{{ session('success') }}</x-ui.notice>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-ui.stat-card label="Recipients" :value="$campaign->recipients_count" meta="Total targeted" />
            <x-ui.stat-card label="Sent" :value="$campaign->sent_count" meta="Delivered attempts" tone="success" />
            <x-ui.stat-card label="Opened" :value="$campaign->opened_count" meta="Open tracking" />
            <x-ui.stat-card label="Clicked" :value="$campaign->clicked_count" meta="Click tracking" />
            <x-ui.stat-card label="Failed" :value="$campaign->failed_count" meta="Retry candidates" tone="warning" />
        </section>

        <section class="ui-card p-5">
            <div class="grid gap-4 md:grid-cols-3">
                <div><p class="text-xs text-text-tertiary">Status</p><x-status-badge :status="$campaign->status" /></div>
                <div><p class="text-xs text-text-tertiary">Audience</p><p class="font-semibold text-text-primary">{{ str($campaign->target_type)->replace('_', ' ')->title() }}</p></div>
                <div><p class="text-xs text-text-tertiary">Scheduled</p><p class="font-semibold text-text-primary">{{ $campaign->scheduled_at?->format('d M Y H:i') ?? 'Not scheduled' }}</p></div>
            </div>
        </section>

        <div class="ui-table-wrap">
            <table class="enterprise-table">
                <thead>
                    <tr>
                        <th>Recipient</th>
                        <th>Status</th>
                        <th>Sent</th>
                        <th>Opened</th>
                        <th>Clicked</th>
                        <th>Failure</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recipients as $recipient)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $recipient->name ?: $recipient->email }}</div>
                                <div class="text-xs text-text-tertiary">{{ $recipient->email }}</div>
                            </td>
                            <td><x-status-badge :status="$recipient->status" /></td>
                            <td>{{ $recipient->sent_at?->format('d M Y H:i') ?? 'N/A' }}</td>
                            <td>{{ $recipient->opened_at?->format('d M Y H:i') ?? 'N/A' }}</td>
                            <td>{{ $recipient->clicked_at?->format('d M Y H:i') ?? 'N/A' }}</td>
                            <td class="max-w-md">{{ $recipient->failure_reason ?: 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-text-secondary">No recipients have been queued yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $recipients->links() }}
    </div>
</x-app-layout>
