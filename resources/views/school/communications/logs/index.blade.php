<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">{{ __('ui.communication_center') }}</p>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ __('ui.notification_logs') }}</h2>
                <p class="mt-1 text-sm text-text-secondary">Search delivery outcomes and operational communication records for {{ $school->name }}.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.communications.index') }}" class="ui-button-secondary">{{ __('ui.communication_center') }}</a>
                <a href="{{ route('school.communications.templates') }}" class="ui-button-secondary">Templates</a>
                <a href="{{ route('school.communications.bulk') }}" class="ui-button-primary">{{ __('ui.bulk_communication') }}</a>
            </div>
        </div>
    </x-slot>

    @php
        $activeFilterCount = collect($filters)->filter(fn ($value) => filled($value))->count();
        $logTone = [
            'sent' => 'success',
            'failed' => 'danger',
            'deferred' => 'warning',
            'logged' => 'info',
            'pending' => 'outline',
        ];
    @endphp

    <div class="space-y-6">
        @if ($errors->any())
            <x-ui.alert tone="danger" body="{{ $errors->first() }}" />
        @endif

        <x-ui.panel title="Filters" description="Narrow logs by event, status, channel, date, or recipient details.">
            <form method="GET" action="{{ route('school.communications.logs') }}" class="space-y-4">
                <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
                    <div>
                        <label for="event_type" class="block text-sm font-medium text-text-primary">Event type</label>
                        <select id="event_type" name="event_type" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">All events</option>
                            @foreach ($eventTypes as $eventType)
                                <option value="{{ $eventType }}" @selected(($filters['event_type'] ?? null) === $eventType)>{{ str($eventType)->replace('.', ' ')->replace('_', ' ')->title() }}</option>
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
                        <label for="date_from" class="block text-sm font-medium text-text-primary">From</label>
                        <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div>
                        <label for="date_to" class="block text-sm font-medium text-text-primary">To</label>
                        <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <div>
                        <label for="search" class="block text-sm font-medium text-text-primary">Recipient or text</label>
                        <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-text-secondary">{{ $activeFilterCount }} active {{ str('filter')->plural($activeFilterCount) }}</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('school.communications.logs') }}" class="ui-button-secondary">Clear filters</a>
                        <button class="ui-button-primary" data-loading-text="Filtering...">Apply filters</button>
                    </div>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Notification Log" description="Delivery status, recipient summaries, and related operational events.">
            <div class="hidden overflow-x-auto lg:block">
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
                                <td class="px-3 py-3">
                                    <span class="block font-semibold text-text-primary">{{ str($log->event_type)->replace('.', ' ')->replace('_', ' ')->title() }}</span>
                                    <span class="mt-1 block font-mono text-xs text-text-tertiary">{{ $log->event_type }}</span>
                                </td>
                                <td class="px-3 py-3 text-text-secondary">
                                    <span class="block max-w-md font-medium text-text-primary">{{ $log->subject ?: 'No subject' }}</span>
                                    <span class="mt-1 block max-w-md text-xs leading-5 text-text-secondary">{{ $log->message_summary }}</span>
                                </td>
                                <td class="px-3 py-3 text-text-secondary">
                                    {{ $log->recipient_name ?: str($log->recipient_type)->replace('_', ' ')->title() }}
                                    <span class="block text-xs text-text-tertiary">{{ $log->recipient_email ?: str($log->recipient_type)->replace('_', ' ')->title() }}</span>
                                </td>
                                <td class="px-3 py-3"><x-ui.badge tone="outline">{{ str($log->channel)->replace('_', ' ')->title() }}</x-ui.badge></td>
                                <td class="px-3 py-3"><x-ui.badge :tone="$logTone[$log->status] ?? 'outline'">{{ str($log->status)->replace('_', ' ')->title() }}</x-ui.badge></td>
                                <td class="px-3 py-3 text-text-secondary">{{ $log->created_at?->format('d M Y H:i') }}</td>
                                <td class="px-3 py-3 text-right">
                                    <a href="{{ route('school.communications.logs.show', $log) }}" class="ui-button-secondary">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-8">
                                    <x-ui.empty-state title="No matching notification logs" body="Clear the filters or try a broader date range." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="grid gap-3 lg:hidden">
                @forelse ($logs as $log)
                    <article class="rounded-md border border-border-subtle bg-bg-primary p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-text-primary">{{ str($log->event_type)->replace('.', ' ')->replace('_', ' ')->title() }}</p>
                                <p class="mt-1 font-mono text-xs text-text-tertiary">{{ $log->event_type }}</p>
                            </div>
                            <x-ui.badge :tone="$logTone[$log->status] ?? 'outline'">{{ str($log->status)->replace('_', ' ')->title() }}</x-ui.badge>
                        </div>

                        <div class="mt-4 space-y-3 text-sm">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-text-tertiary">Subject</p>
                                <p class="mt-1 font-medium text-text-primary">{{ $log->subject ?: 'No subject' }}</p>
                                @if ($log->message_summary)
                                    <p class="mt-1 leading-5 text-text-secondary">{{ $log->message_summary }}</p>
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-text-tertiary">Recipient</p>
                                    <p class="mt-1 text-text-primary">{{ $log->recipient_name ?: str($log->recipient_type)->replace('_', ' ')->title() }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-text-tertiary">Channel</p>
                                    <p class="mt-1 text-text-primary">{{ str($log->channel)->replace('_', ' ')->title() }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-text-secondary">{{ $log->created_at?->format('d M Y H:i') }}</p>
                                <a href="{{ route('school.communications.logs.show', $log) }}" class="ui-button-secondary">Open</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-ui.empty-state title="No matching notification logs" body="Clear the filters or try a broader date range." />
                @endforelse
            </div>

            @if ($logs->hasPages())
                <div class="mt-4">{{ $logs->links() }}</div>
            @endif
        </x-ui.panel>
    </div>
</x-app-layout>
