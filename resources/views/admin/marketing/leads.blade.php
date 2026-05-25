<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">Marketing Leads</h2>
            <p class="mt-1 text-sm text-text-secondary">CRM leads with scoring and segmentation signals.</p>
        </div>
    </x-slot>

    <div class="ui-table-wrap">
        <table class="enterprise-table">
            <thead>
                <tr>
                    <th>Lead</th>
                    <th>School</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Segment</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leads as $lead)
                    @php($score = $lead->marketingLeadScores->sortByDesc('score')->first())
                    <tr>
                        <td>
                            <div class="font-semibold">{{ $lead->name }}</div>
                            <div class="text-xs text-text-tertiary">{{ $lead->email }}</div>
                        </td>
                        <td>{{ $lead->school_name ?: $lead->convertedSchool?->name ?: 'N/A' }}</td>
                        <td><x-status-badge :status="$lead->status" /></td>
                        <td>{{ $score?->score ?? 0 }}</td>
                        <td>{{ $score?->segment ?? 'unscored' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-secondary">No marketing leads yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $leads->links() }}</div>
</x-app-layout>
