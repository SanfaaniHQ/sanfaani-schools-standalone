<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">Marketing Sequences</h2>
            <p class="mt-1 text-sm text-text-secondary">Saved records for nurture and re-engagement workflows.</p>
        </div>
    </x-slot>

    <div class="ui-table-wrap">
        <table class="enterprise-table">
            <thead>
                <tr>
                    <th>Sequence</th>
                    <th>Trigger</th>
                    <th>Status</th>
                    <th>Steps</th>
                    <th>Enrollments</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sequences as $sequence)
                    <tr>
                        <td class="font-semibold">{{ $sequence->name }}</td>
                        <td>{{ $sequence->trigger_event ?: 'Manual' }}</td>
                        <td><x-status-badge :status="$sequence->status" /></td>
                        <td>{{ $sequence->steps_count }}</td>
                        <td>{{ $sequence->enrollments_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-secondary">No marketing sequences yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $sequences->links() }}</div>
</x-app-layout>
