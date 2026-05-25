<x-ui.panel>
    <h3 class="text-base font-semibold text-text-primary">Backup logs</h3>
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-border-subtle text-sm">
            <thead class="bg-bg-tertiary text-xs uppercase text-text-tertiary">
                <tr>
                    <th class="px-4 py-3 text-left">Event</th>
                    <th class="px-4 py-3 text-left">Severity</th>
                    <th class="px-4 py-3 text-left">Message</th>
                    <th class="px-4 py-3 text-left">When</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-subtle">
                @forelse ($logs as $log)
                    @php
                        $redactor = app(\App\Services\Security\SecretRedactionService::class);
                        $safeMessage = $redactor->redact((string) $log->message);
                        $safeContext = $log->context ? $redactor->redactArray((array) $log->context) : null;
                    @endphp
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs text-text-primary">{{ $log->event }}</td>
                        <td class="px-4 py-3"><x-ui.badge>{{ str($log->severity)->title() }}</x-ui.badge></td>
                        <td class="px-4 py-3 text-text-secondary">
                            {{ $safeMessage }}
                            @if ($safeContext)
                                <div class="mt-1 max-w-xl break-words font-mono text-xs text-text-tertiary">{{ json_encode($safeContext, JSON_UNESCAPED_SLASHES) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-text-secondary">{{ $log->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-text-secondary">No backup logs yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.panel>
