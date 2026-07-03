<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">Sales Tasks</h2>
            <p class="mt-1 text-sm text-text-secondary">Follow-up reminders from demo, trial, and onboarding signals.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if (session('success'))
            <x-ui.notice>{{ session('success') }}</x-ui.notice>
        @endif

        <div class="ui-table-wrap">
            <table class="enterprise-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Lead</th>
                        <th>School</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $task->title }}</div>
                                <div class="text-xs text-text-tertiary">{{ $task->description }}</div>
                            </td>
                            <td>{{ $task->leadRequest?->name ?? $task->demoRequest?->name ?? 'N/A' }}</td>
                            <td>{{ $task->school?->name ?? $task->leadRequest?->school_name ?? $task->demoRequest?->school_name ?? 'Platform' }}</td>
                            <td>{{ $task->due_at?->format('d M Y H:i') ?? 'Not set' }}</td>
                            <td><x-status-badge :status="$task->status" /></td>
                            <td class="text-right">
                                @if ($task->status !== \App\Models\SalesTask::STATUS_COMPLETED)
                                    <form method="POST" action="{{ route('admin.sales.tasks.complete', $task) }}">
                                        @csrf
                                        <button class="ui-button-secondary">Complete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-text-secondary">No sales tasks yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $tasks->links() }}</div>
    </div>
</x-app-layout>
