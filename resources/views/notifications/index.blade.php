<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Notifications</h2>
                <p class="mt-1 text-sm text-text-secondary">{{ $unreadCount }} unread notification{{ $unreadCount === 1 ? '' : 's' }}.</p>
            </div>

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="ui-button-secondary" data-loading-text="Updating...">Mark all as read</button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-lg border border-border-subtle bg-bg-secondary shadow-sm">
        <div class="divide-y divide-border-subtle">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data ?? [];
                    $title = data_get($data, 'title', class_basename($notification->type));
                    $body = data_get($data, 'body');
                    $severity = data_get($data, 'severity', 'info');
                    $actionUrl = data_get($data, 'action_url');
                    $isUnread = is_null($notification->read_at);
                    $tone = match ($severity) {
                        'critical', 'error' => 'border-red-500 bg-red-500',
                        'warning' => 'border-amber-500 bg-amber-500',
                        'success' => 'border-emerald-500 bg-emerald-500',
                        default => 'border-brand-primary bg-brand-primary',
                    };
                @endphp

                <article class="flex gap-4 px-5 py-4 {{ $isUnread ? 'bg-bg-tertiary/40' : '' }}">
                    <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $tone }}" aria-hidden="true"></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-semibold text-text-primary">{{ $title }}</p>
                                @if ($body)
                                    <p class="mt-1 text-sm text-text-secondary">{{ $body }}</p>
                                @endif
                                <p class="mt-2 text-xs text-text-tertiary">
                                    {{ data_get($data, 'category', 'system') }} &middot; {{ $notification->created_at?->diffForHumans() }}
                                </p>
                            </div>

                            <div class="flex shrink-0 flex-wrap gap-2">
                                @if ($actionUrl)
                                    <a href="{{ $actionUrl }}" class="ui-button-secondary min-h-9 px-3 py-1 text-xs">Open</a>
                                @endif
                                @if ($isUnread)
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                        @csrf
                                        <button type="submit" class="ui-button-primary min-h-9 px-3 py-1 text-xs" data-loading-text="Updating...">Mark read</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="px-5 py-12">
                    <x-empty-state title="No notifications yet" description="Operational alerts and workflow updates will appear here." />
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="border-t border-border-subtle px-5 py-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
