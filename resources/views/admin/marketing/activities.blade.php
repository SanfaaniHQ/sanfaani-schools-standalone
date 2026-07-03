<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">Marketing Activities</h2>
            <p class="mt-1 text-sm text-text-secondary">Conversion signals from demo, onboarding, and CRM events.</p>
        </div>
    </x-slot>

    <div class="ui-table-wrap">
        <table class="enterprise-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Lead/Demo</th>
                    <th>School</th>
                    <th>When</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activities as $activity)
                    <tr>
                        <td>
                            <div class="font-semibold">{{ $activity->event }}</div>
                            <div class="text-xs text-text-tertiary">{{ $activity->description }}</div>
                        </td>
                        <td>{{ $activity->leadRequest?->name ?? $activity->demoRequest?->name ?? 'System' }}</td>
                        <td>{{ $activity->school?->name ?? 'Platform' }}</td>
                        <td>{{ $activity->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-text-secondary">No marketing activities yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $activities->links() }}</div>
</x-app-layout>
