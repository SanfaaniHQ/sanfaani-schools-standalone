<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">Notification Log</p>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $notificationLog->subject ?: str($notificationLog->event_type)->replace('.', ' ')->title() }}</h2>
                <p class="mt-1 text-sm text-text-secondary">{{ $notificationLog->event_type }} for {{ $school->name }}.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.communications.logs') }}" class="ui-button-secondary">Notification Logs</a>
                <a href="{{ route('school.communications.index') }}" class="ui-button-secondary">Communication Center</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Channel" :value="str($notificationLog->channel)->replace('_', ' ')->title()" meta="Provider delivery is deferred for SMS, WhatsApp, and email-ready channels." tone="info" />
            <x-ui.stat-card label="Status" :value="str($notificationLog->status)->replace('_', ' ')->title()" meta="Logged inside this school workspace." />
            <x-ui.stat-card label="Recipient Type" :value="str($notificationLog->recipient_type)->replace('_', ' ')->title()" :meta="$notificationLog->recipient_name" />
            <x-ui.stat-card label="Logged At" :value="$notificationLog->created_at?->format('d M Y')" :meta="$notificationLog->created_at?->format('H:i')" />
        </section>

        <x-ui.panel title="Safe Message Summary" description="This is a minimized summary, not a raw provider payload.">
            <dl class="grid gap-4 md:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-normal text-text-tertiary">Subject</dt>
                    <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $notificationLog->subject ?: 'No subject stored' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-normal text-text-tertiary">Template</dt>
                    <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $notificationLog->template?->title ?? 'No template used' }}</dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-xs uppercase tracking-normal text-text-tertiary">Summary</dt>
                    <dd class="mt-1 text-sm leading-6 text-text-secondary">{{ $notificationLog->message_summary ?: 'No message summary stored.' }}</dd>
                </div>
                @if ($notificationLog->failure_reason)
                    <div class="md:col-span-2">
                        <dt class="text-xs uppercase tracking-normal text-text-tertiary">Failure Reason</dt>
                        <dd class="mt-1 text-sm leading-6 text-text-secondary">{{ $notificationLog->failure_reason }}</dd>
                    </div>
                @endif
            </dl>
        </x-ui.panel>

        <x-ui.panel title="Recipient Scope" description="Recipient details are minimized and remain within this school.">
            <dl class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <dt class="text-xs uppercase tracking-normal text-text-tertiary">Recipient</dt>
                    <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $notificationLog->recipient_name ?: str($notificationLog->recipient_type)->replace('_', ' ')->title() }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-normal text-text-tertiary">Email</dt>
                    <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $notificationLog->recipient_email ?: 'Not stored' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-normal text-text-tertiary">Phone</dt>
                    <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $notificationLog->recipient_phone ?: 'Not stored' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-normal text-text-tertiary">Related Record</dt>
                    <dd class="mt-1 text-sm font-semibold text-text-primary">
                        {{ $notificationLog->related_model_type ? class_basename($notificationLog->related_model_type).' #'.$notificationLog->related_model_id : 'None' }}
                    </dd>
                </div>
            </dl>
        </x-ui.panel>

        <x-ui.panel title="Safe Metadata" description="Secrets, tokens, passwords, raw provider payloads, and live class meeting passwords are not written here.">
            @if ($notificationLog->metadata)
                <div class="overflow-x-auto rounded-md border border-border-subtle bg-bg-primary p-4">
                    <pre class="whitespace-pre-wrap break-words text-xs leading-5 text-text-secondary">{{ json_encode($notificationLog->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            @else
                <x-ui.empty-state title="No metadata stored" body="This log only stores the standard event, recipient, channel, and status fields." class="p-5" />
            @endif
        </x-ui.panel>
    </div>
</x-app-layout>
