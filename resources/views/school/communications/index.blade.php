<x-app-layout>
    <x-slot name="header">
        @php
            $communicationBranding = app(\App\Services\Branding\BrandingService::class)->forSchool($school);
            $communicationBrandName = data_get($communicationBranding, 'brand_name', $school->name);
            $communicationLogo = data_get($communicationBranding, 'logo_url');
            $communicationInitials = data_get($communicationBranding, 'initials', $school->initials());
        @endphp
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex min-w-0 gap-3">
                @if ($communicationLogo)
                    <img src="{{ $communicationLogo }}" alt="{{ $communicationBrandName }} logo" class="h-12 w-12 shrink-0 rounded-md border border-border-subtle bg-white object-contain p-1">
                @else
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-brand-primary text-sm font-semibold text-white">{{ $communicationInitials }}</span>
                @endif
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">School / Communications</p>
                    <h2 class="text-xl font-semibold leading-tight text-text-primary">Communication Command Center</h2>
                    <p class="mt-1 text-sm text-text-secondary">Send guardian updates, review notification outcomes, and manage reusable message templates for {{ $communicationBrandName }}.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.communications.logs') }}" class="ui-button-secondary">{{ __('ui.notification_logs') }}</a>
                <a href="{{ route('school.communications.templates') }}" class="ui-button-secondary">Templates</a>
                <a href="{{ route('school.communications.bulk') }}" class="ui-button-primary">{{ __('ui.bulk_communication') }}</a>
            </div>
        </div>
    </x-slot>

    @php
        $failedCount = $statusCounts['failed'] ?? 0;
        $sentCount = $statusCounts['sent'] ?? 0;
        $loggedCount = $statusCounts['logged'] ?? 0;
        $deferredCount = $statusCounts['deferred'] ?? 0;
    @endphp

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success" :body="session('success')" />
        @endif

        @if (session('warning'))
            <x-ui.alert tone="warning" :body="session('warning')" />
        @endif

        @if (session('error'))
            <x-ui.alert tone="danger" :body="session('error')" />
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-ui.stat-card label="Sent" :value="$sentCount" meta="Confirmed email deliveries" tone="success" :href="route('school.communications.logs', ['status' => 'sent'])" />
            <x-ui.stat-card label="Failed" :value="$failedCount" meta="Needs review or retry" tone="warning" :href="route('school.communications.logs', ['status' => 'failed'])" />
            <x-ui.stat-card label="Logged" :value="$loggedCount" meta="Operational records" tone="info" :href="route('school.communications.logs', ['status' => 'logged'])" />
            <x-ui.stat-card label="Deferred" :value="$deferredCount" meta="Awaiting configured channels" tone="warning" :href="route('school.communications.logs', ['status' => 'deferred'])" />
            <x-ui.stat-card label="Templates" :value="$activeTemplateCount . ' / ' . $templateCount" meta="Active / total" :href="route('school.communications.templates')" />
        </section>

        <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <x-ui.panel title="Quick Actions" description="Common communication workflows for daily school operations.">
                <div class="grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
                    <a href="{{ route('school.communications.bulk') }}" class="rounded-md border border-border-subtle bg-bg-primary p-4 transition hover:border-border-hover hover:bg-bg-tertiary">
                        <span class="block text-sm font-semibold text-text-primary">Compose bulk message</span>
                        <span class="mt-1 block text-sm leading-6 text-text-secondary">Send a class, session, selected-student, or staff email batch.</span>
                    </a>
                    <a href="{{ route('school.communications.logs') }}" class="rounded-md border border-border-subtle bg-bg-primary p-4 transition hover:border-border-hover hover:bg-bg-tertiary">
                        <span class="block text-sm font-semibold text-text-primary">Review delivery logs</span>
                        <span class="mt-1 block text-sm leading-6 text-text-secondary">Filter by event, status, channel, date, and recipient.</span>
                    </a>
                    <a href="{{ route('school.communications.templates') }}" class="rounded-md border border-border-subtle bg-bg-primary p-4 transition hover:border-border-hover hover:bg-bg-tertiary">
                        <span class="block text-sm font-semibold text-text-primary">Manage templates</span>
                        <span class="mt-1 block text-sm leading-6 text-text-secondary">Keep standard school messages consistent and ready to reuse.</span>
                    </a>
                </div>
            </x-ui.panel>

            <x-ui.panel :tone="$failedCount > 0 ? 'warning' : 'success'" title="Delivery Status" description="Email sends through the configured school or system mail settings.">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                        <p class="text-sm font-semibold text-text-primary">Failed</p>
                        <p class="mt-1 text-sm leading-6 text-text-secondary">Open failed logs to see the safe failure summary, confirm mail settings, then resend from the relevant workflow.</p>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                        <p class="text-sm font-semibold text-text-primary">Deferred</p>
                        <p class="mt-1 text-sm leading-6 text-text-secondary">SMS, WhatsApp, and in-app entries stay recorded until those channels are configured.</p>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                        <p class="text-sm font-semibold text-text-primary">Sent</p>
                        <p class="mt-1 text-sm leading-6 text-text-secondary">Sent records confirm the application handed email to the configured mailer.</p>
                    </div>
                </div>
            </x-ui.panel>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
            <x-ui.panel title="Recent Notifications" description="Latest school-scoped communication outcomes.">
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
                                        <span class="block text-xs text-text-tertiary">{{ $log->recipient_email ?: str($log->recipient_type)->replace('_', ' ')->title() }}</span>
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
                                        <x-ui.empty-state title="No notification logs yet" body="Communication and school event outcomes will appear here after the first message or operational event is recorded." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.panel>

            <x-ui.panel title="Notification Templates" description="Reusable school text for repeat messages.">
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
                            body="Create a template for repeatable school communication."
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
