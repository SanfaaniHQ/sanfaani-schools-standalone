<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-text-primary">Email Templates</h2>
                <p class="mt-1 text-sm text-text-secondary">Reusable marketing layouts with dynamic SaaS placeholders.</p>
            </div>
            <a href="{{ route('admin.email-marketing.templates.create') }}" class="ui-button-primary">Create Template</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.notice>{{ session('success') }}</x-ui.notice>
        @endif

        <div class="ui-table-wrap">
            <table class="enterprise-table">
                <thead>
                    <tr>
                        <th>Template</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($templates as $template)
                        <tr>
                            <td class="font-semibold">{{ $template->name }}</td>
                            <td>{{ $template->subject }}</td>
                            <td><x-status-badge :status="$template->status" /></td>
                            <td>{{ $template->updated_at?->format('d M Y H:i') }}</td>
                            <td class="text-right"><a href="{{ route('admin.email-marketing.templates.edit', $template) }}" class="font-semibold text-brand-primary">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-text-secondary">No email templates yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $templates->links() }}
    </div>
</x-app-layout>
