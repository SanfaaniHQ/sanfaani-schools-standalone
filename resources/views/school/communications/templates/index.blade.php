<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">Communication Center</p>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Notification Templates</h2>
                <p class="mt-1 text-sm text-text-secondary">School-scoped templates for repeatable operational notifications.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.communications.index') }}" class="ui-button-secondary">Communication Center</a>
                <a href="{{ route('school.communications.templates.create') }}" class="ui-button-primary">Create Template</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success" :body="session('success')" />
        @endif

        <x-ui.panel tone="info" title="Template Boundary">
            <p class="text-sm leading-6 text-text-secondary">
                Templates prepare consistent in-app/logged, email-ready, SMS-ready, and WhatsApp-ready text. SMS and WhatsApp provider APIs are not configured here, provider credentials are not stored, and Blade output is escaped when templates are displayed.
            </p>
        </x-ui.panel>

        <x-ui.panel title="Templates" description="Template keys are unique inside this school workspace.">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border-subtle text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-normal text-text-tertiary">
                            <th class="px-3 py-2">Template</th>
                            <th class="px-3 py-2">Channel</th>
                            <th class="px-3 py-2">Audience</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Logs</th>
                            <th class="px-3 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse ($templates as $template)
                            <tr>
                                <td class="px-3 py-3">
                                    <span class="block font-semibold text-text-primary">{{ $template->title }}</span>
                                    <span class="mt-1 block font-mono text-xs text-text-tertiary">{{ $template->template_key }}</span>
                                </td>
                                <td class="px-3 py-3"><x-ui.badge tone="outline">{{ str($template->channel)->replace('_', ' ')->title() }}</x-ui.badge></td>
                                <td class="px-3 py-3 text-text-secondary">{{ str($template->audience_type)->replace('_', ' ')->title() }}</td>
                                <td class="px-3 py-3">
                                    <x-ui.badge :tone="$template->is_active ? 'success' : 'outline'">{{ $template->is_active ? 'Active' : 'Inactive' }}</x-ui.badge>
                                </td>
                                <td class="px-3 py-3 font-mono text-text-secondary">{{ $template->logs_count }}</td>
                                <td class="px-3 py-3 text-right">
                                    <a href="{{ route('school.communications.templates.edit', $template) }}" class="ui-button-secondary">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8">
                                    <x-ui.empty-state
                                        title="No templates yet"
                                        body="Create a template for recurring operational notifications."
                                        :action-href="route('school.communications.templates.create')"
                                        action-label="Create Template"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($templates->hasPages())
                <div class="mt-4">{{ $templates->links() }}</div>
            @endif
        </x-ui.panel>
    </div>
</x-app-layout>
