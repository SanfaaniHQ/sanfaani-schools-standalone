<div class="overflow-hidden rounded-2xl bg-white shadow-sm">
    <div class="border-b border-gray-100 px-6 py-4">
        <h3 class="text-base font-semibold text-gray-900">Recent license activity</h3>
        <p class="mt-1 text-sm text-gray-500">Activation, checks, expiry, and temporary access events.</p>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse ($auditLogs as $log)
            <div class="px-6 py-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $log->event }}</p>
                        <p class="mt-1 text-sm text-gray-600">{{ $log->message }}</p>
                    </div>
                    <div class="text-sm text-gray-500">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $log->severity === 'critical' || $log->severity === 'error' ? 'bg-red-50 text-red-700' : ($log->severity === 'warning' ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700') }}">
                            {{ str($log->severity)->title() }}
                        </span>
                        <span class="ms-2">{{ $log->created_at?->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-8 text-sm text-gray-500">No license audit events have been recorded yet.</div>
        @endforelse
    </div>
</div>
