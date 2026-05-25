<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">Onboarding Progress</h2>
            <p class="mt-1 text-sm text-text-secondary">Platform visibility into guided onboarding progress and internal event logs.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <x-ui.panel>
            <h3 class="text-base font-semibold text-text-primary">Recent progress</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-border-subtle text-sm">
                    <thead class="text-left text-xs uppercase text-text-tertiary">
                        <tr>
                            <th class="px-3 py-2">User</th>
                            <th class="px-3 py-2">School</th>
                            <th class="px-3 py-2">Checklist</th>
                            <th class="px-3 py-2">Step</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse ($progress as $row)
                            <tr>
                                <td class="px-3 py-3">{{ $row->user?->name ?? 'Deleted user' }}</td>
                                <td class="px-3 py-3">{{ $row->school?->name ?? 'Platform' }}</td>
                                <td class="px-3 py-3">{{ $row->checklist?->name }}</td>
                                <td class="px-3 py-3">{{ $row->step?->title }}</td>
                                <td class="px-3 py-3"><x-status-badge :status="$row->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-6 text-text-secondary">No onboarding progress has been recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $progress->links() }}</div>
        </x-ui.panel>

        <x-ui.panel>
            <h3 class="text-base font-semibold text-text-primary">Recent onboarding events</h3>
            <div class="mt-4 divide-y divide-border-subtle">
                @forelse ($events as $event)
                    <div class="py-3 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="font-semibold text-text-primary">{{ $event->event }}</p>
                            <p class="text-xs text-text-tertiary">{{ $event->created_at->toDayDateTimeString() }}</p>
                        </div>
                        <p class="mt-1 text-text-secondary">{{ $event->description }}</p>
                    </div>
                @empty
                    <p class="py-4 text-sm text-text-secondary">No onboarding events have been logged yet.</p>
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
