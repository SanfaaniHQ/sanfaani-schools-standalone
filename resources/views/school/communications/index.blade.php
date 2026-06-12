<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">School / Communications</p>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Communication Center</h2>
                <p class="mt-1 text-sm text-text-secondary">Operational notifications, templates, bulk batches, and provider-ready communication boundaries for {{ $school->name }}.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.communications.logs') }}" class="ui-button-secondary">Notification Logs</a>
                <a href="{{ route('school.communications.templates') }}" class="ui-button-secondary">Templates</a>
                <a href="{{ route('school.communications.bulk') }}" class="ui-button-primary">Bulk Communication</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success" :body="session('success')" />
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Logged" :value="$statusCounts['logged'] ?? 0" meta="Safe operational entries" tone="info" :href="route('school.communications.logs', ['status' => 'logged'])" />
            <x-ui.stat-card label="Deferred" :value="$statusCounts['deferred'] ?? 0" meta="Provider-ready, not sent" tone="warning" :href="route('school.communications.logs', ['status' => 'deferred'])" />
            <x-ui.stat-card label="Templates" :value="$activeTemplateCount . ' / ' . $templateCount" meta="Active / total templates" :href="route('school.communications.templates')" />
            <x-ui.stat-card label="Bulk Batches" :value="$bulkBatchCount" meta="Existing bulk communication batches" :href="route('school.communications.bulk')" />
        </section>

        <x-ui.panel tone="info" title="Stage 18 Safety Boundary">
            <p class="text-sm leading-6 text-text-secondary">
                This center logs school-scoped operational notifications and manages reusable templates. SMS and WhatsApp are marked provider-ready only. Provider APIs, credentials, webhooks, push notifications, and public marketing campaigns are not active here. Live class meeting passwords, provider secrets, OAuth tokens, CBT answers, admission documents, and raw external payloads are not stored in notification logs.
            </p>
        </x-ui.panel>

        <div class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
            <x-ui.panel title="Recent Operational Notifications" description="School-scoped notification log summaries. Sensitive payloads are intentionally omitted.">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border-subtle text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-normal text-text-tertiary">
                                <th class="px-3 py-2">Event</th>
                                <th class="px-3 py-2">Recipient</th>
                                <th class="px-3 py-2">Channel</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-subtle">
                            @forelse ($recentLogs as $log)
                                <tr>
                                    <td class="px-3 py-3">
                                        <span class="block font-semibold text-text-primary">{{ str($log->event_type)->replace('.', ' ')->replace('_', ' ')->title() }}</span>
                                        <span class="mt-1 block max-w-xl text-xs text-text-secondary">{{ $log->message_summary ?: $log->subject }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-text-secondary">
                                        {{ $log->recipient_name ?: str($log->recipient_type)->replace('_', ' ')->title() }}
                                        <span class="block text-xs text-text-tertiary">{{ str($log->recipient_type)->replace('_', ' ')->title() }}</span>
                                    </td>
                                    <td class="px-3 py-3"><x-ui.badge tone="outline">{{ str($log->channel)->replace('_', ' ')->title() }}</x-ui.badge></td>
                                    <td class="px-3 py-3"><x-ui.badge :status="$log->status" /></td>
                                    <td class="px-3 py-3 text-right">
                                        <a href="{{ route('school.communications.logs.show', $log) }}" class="ui-button-secondary">Open</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-8">
                                        <x-ui.empty-state title="No notification logs yet" body="Operational hooks such as live class schedule changes will appear here after they are logged." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.panel>

            <x-ui.panel title="Notification Templates" description="Reusable school-scoped text for operational notices.">
                <div class="space-y-3">
                    @forelse ($recentTemplates as $template)
                        <a href="{{ route('school.communications.templates.edit', $template) }}" class="block rounded-md border border-border-subtle bg-bg-primary p-3 transition hover:border-border-hover hover:bg-bg-tertiary">
                            <span class="flex items-start justify-between gap-3">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-semibold text-text-primary">{{ $template->title }}</span>
                                    <span class="mt-1 block truncate font-mono text-xs text-text-tertiary">{{ $template->template_key }}</span>
                                </span>
                                <x-ui.badge :tone="$template->is_active ? 'success' : 'outline'">{{ $template->is_active ? 'Active' : 'Inactive' }}</x-ui.badge>
                            </span>
                        </a>
                    @empty
                        <x-ui.empty-state
                            title="No templates yet"
                            body="Create the first school-scoped template for repeatable operational messages."
                            :action-href="route('school.communications.templates.create')"
                            action-label="Create Template"
                            class="p-5"
                        />
                    @endforelse
                </div>
            </x-ui.panel>
        </div>
    </div>
</x-app-layout>
