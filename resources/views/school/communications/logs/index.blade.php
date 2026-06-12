<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">Communication Center</p>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Notification Logs</h2>
                <p class="mt-1 text-sm text-text-secondary">School-scoped operational notification outbox for {{ $school->name }}.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.communications.index') }}" class="ui-button-secondary">Communication Center</a>
                <a href="{{ route('school.communications.templates') }}" class="ui-button-secondary">Templates</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <x-ui.panel title="Filters" description="Filter by safe log metadata. Logs never include meeting passwords or provider secrets.">
            <form method="GET" action="{{ route('school.communications.logs') }}" class="grid gap-3 md:grid-cols-4">
                <div>
                    <label for="event_type" class="block text-sm font-medium text-text-primary">Event Type</label>
                    <input id="event_type" name="event_type" value="{{ $filters['event_type'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                </div>
                <div>
                    <label for="channel" class="block text-sm font-medium text-text-primary">Channel</label>
                    <select id="channel" name="channel" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        <option value="">All channels</option>
                        @foreach ($channels as $channel)
                            <option value="{{ $channel }}" @selected(($filters['channel'] ?? null) === $channel)>{{ str($channel)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-text-primary">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button class="ui-button-secondary">Filter</button>
                    <a href="{{ route('school.communications.logs') }}" class="ui-button-secondary">Clear</a>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Operational Notification Outbox" description="Entries are prepared and logged. External provider delivery remains deferred unless a future safe provider is configured.">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border-subtle text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-normal text-text-tertiary">
                            <th class="px-3 py-2">Event</th>
                            <th class="px-3 py-2">Subject</th>
                            <th class="px-3 py-2">Recipient</th>
                            <th class="px-3 py-2">Channel</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Logged</th>
                            <th class="px-3 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-3 py-3 font-medium text-text-primary">{{ $log->event_type }}</td>
                                <td class="px-3 py-3 text-text-secondary">
                                    <span class="block max-w-md font-medium text-text-primary">{{ $log->subject ?: 'No subject' }}</span>
                                    <span class="mt-1 block max-w-md text-xs leading-5 text-text-secondary">{{ $log->message_summary }}</span>
                                </td>
                                <td class="px-3 py-3 text-text-secondary">
                                    {{ $log->recipient_name ?: str($log->recipient_type)->replace('_', ' ')->title() }}
                                    <span class="block text-xs text-text-tertiary">{{ str($log->recipient_type)->replace('_', ' ')->title() }}</span>
                                </td>
                                <td class="px-3 py-3"><x-ui.badge tone="outline">{{ str($log->channel)->replace('_', ' ')->title() }}</x-ui.badge></td>
                                <td class="px-3 py-3"><x-ui.badge :status="$log->status" /></td>
                                <td class="px-3 py-3 text-text-secondary">{{ $log->created_at?->format('d M Y H:i') }}</td>
                                <td class="px-3 py-3 text-right">
                                    <a href="{{ route('school.communications.logs.show', $log) }}" class="ui-button-secondary">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-8">
                                    <x-ui.empty-state title="No matching notification logs" body="Try clearing the filters or log an operational event such as scheduling a live class." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="mt-4">{{ $logs->links() }}</div>
            @endif
        </x-ui.panel>
    </div>
</x-app-layout>
