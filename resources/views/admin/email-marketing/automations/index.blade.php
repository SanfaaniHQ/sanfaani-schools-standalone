<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-text-primary">Automations</h2>
                <p class="mt-1 text-sm text-text-secondary">Queue-driven workflows for welcome, trial, expiry, recovery, and announcements.</p>
            </div>
            <form method="POST" action="{{ route('admin.email-marketing.automations.run') }}">
                @csrf
                <button class="ui-button-primary">Run Automations</button>
            </form>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <section class="ui-card p-5">
            <h3 class="font-semibold text-text-primary">Create Automation</h3>
            <form method="POST" action="{{ route('admin.email-marketing.automations.store') }}" class="mt-4 space-y-4">
                @csrf
                <input name="name" class="ui-input" placeholder="Workflow name" required>
                <select name="trigger_type" class="ui-input" required>
                    @foreach ($triggerTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="ui-input" required>
                    <option value="paused">Paused</option>
                    <option value="active">Active</option>
                </select>
                <div class="max-h-48 space-y-2 overflow-y-auto rounded-md border border-border-subtle bg-bg-primary p-3">
                    @foreach ($leadStatuses as $value => $label)
                        <label class="flex items-center gap-2 text-sm text-text-secondary">
                            <input type="checkbox" name="statuses[]" value="{{ $value }}" class="rounded border-border-subtle text-brand-primary">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <input name="step_subject" class="ui-input" placeholder="Step subject" required>
                <input name="step_preview_text" class="ui-input" placeholder="Step preview text">
                <textarea name="step_body" rows="8" class="ui-input font-mono" placeholder="Step body" required></textarea>
                <button class="ui-button-primary">Save Automation</button>
            </form>
        </section>

        <section class="ui-card overflow-hidden">
            <div class="border-b border-border-subtle px-5 py-4">
                <h3 class="font-semibold text-text-primary">Existing Automations</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="enterprise-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Trigger</th>
                            <th>Status</th>
                            <th>Last Run</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($automations as $automation)
                            <tr>
                                <td class="font-semibold">{{ $automation->name }}</td>
                                <td>{{ $triggerTypes[$automation->trigger_type] ?? $automation->trigger_type }}</td>
                                <td><x-status-badge :status="$automation->status" /></td>
                                <td>{{ $automation->last_run_at?->format('d M Y H:i') ?? 'Never' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-text-secondary">No automations configured.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $automations->links() }}</div>
        </section>
    </div>
</x-app-layout>
