@props(['checks'])

<div class="space-y-3">
    @forelse ($checks as $check)
        <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="font-semibold text-text-primary">{{ $check['label'] }}</p>
                    <p class="mt-1 text-sm leading-6 text-text-secondary">{{ $check['message'] }}</p>
                </div>
                <x-status-badge :status="$check['status']" />
            </div>
            @if (! empty($check['context']))
                <dl class="mt-3 grid gap-2 text-xs sm:grid-cols-2">
                    @foreach ($check['context'] as $key => $value)
                        <div class="rounded-md bg-bg-tertiary px-3 py-2">
                            <dt class="font-semibold text-text-tertiary">{{ str($key)->replace('_', ' ')->title() }}</dt>
                            <dd class="mt-1 break-words text-text-secondary">
                                @if (is_array($value))
                                    {{ implode(', ', array_map(fn ($item) => is_scalar($item) ? (string) $item : json_encode($item), $value)) }}
                                @elseif (is_bool($value))
                                    {{ $value ? 'Yes' : 'No' }}
                                @else
                                    {{ $value ?? 'None' }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </div>
    @empty
        <x-ui.empty-state title="No checks available" body="Diagnostics will appear here when the safety service returns results." />
    @endforelse
</div>
