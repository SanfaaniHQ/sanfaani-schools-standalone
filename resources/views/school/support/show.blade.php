<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$thread->subject" :description="'Status: '.ucfirst(str_replace('_', ' ', $thread->status))" />
    </x-slot>

    <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                @if (session('success'))
                    <x-ui.alert tone="success" :body="session('success')" />
                @endif

                <div class="rounded-lg border border-border-subtle bg-bg-secondary p-6 shadow-sm">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <x-ui.badge :status="$thread->status" />
                        <x-ui.badge tone="warning">{{ ucfirst($thread->priority) }}</x-ui.badge>
                        <x-ui.badge tone="info">{{ $thread->routeLabel() }}</x-ui.badge>
                        @if ($thread->isEscalated())
                            <x-ui.badge tone="danger">Escalated</x-ui.badge>
                        @endif
                    </div>
                    <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div><dt class="text-xs uppercase tracking-normal text-text-tertiary">Created By</dt><dd class="mt-1 font-medium text-text-primary">{{ $thread->creator?->name ?? 'Unknown' }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-normal text-text-tertiary">Assigned To</dt><dd class="mt-1 font-medium text-text-primary">{{ $thread->assignedUser?->name ?? 'Not assigned yet' }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-normal text-text-tertiary">Creator Role</dt><dd class="mt-1 font-medium text-text-primary">{{ ucwords(str_replace('_', ' ', $thread->creator_role ?: 'legacy')) }}</dd></div>
                        <div><dt class="text-xs uppercase tracking-normal text-text-tertiary">Escalated At</dt><dd class="mt-1 font-medium text-text-primary">{{ $thread->escalated_at?->format('d M Y H:i') ?: 'N/A' }}</dd></div>
                    </dl>
                </div>

                <div class="space-y-3 rounded-lg border border-border-subtle bg-bg-secondary p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-normal text-text-tertiary">Conversation</h3>
                    @forelse ($messages as $message)
                        <div class="rounded-lg border border-border-subtle bg-bg-primary p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-xs text-text-tertiary">{{ $message->sender?->name ?? 'Unknown' }} - {{ ucwords(str_replace('_', ' ', $message->sender_role ?: 'school')) }} - {{ $message->created_at?->diffForHumans() }}</p>
                                @if ($message->is_internal_note)
                                    <x-ui.badge tone="outline">Internal</x-ui.badge>
                                @endif
                            </div>
                            <p class="mt-2 text-sm text-text-primary">{{ $message->message }}</p>
                            @if ($message->attachments->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($message->attachments as $attachment)
                                        <a href="{{ route('school.support-attachments.download', $attachment) }}" class="rounded-lg border border-border-subtle px-3 py-1.5 text-xs font-medium text-text-secondary hover:bg-bg-tertiary hover:text-text-primary">
                                            {{ $attachment->original_name }} ({{ number_format($attachment->size / 1024, 1) }} KB)
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-text-secondary">No messages yet.</p>
                    @endforelse
                </div>

                @if ($thread->status !== 'closed')
                    <form method="POST" action="{{ route('school.support.reply', $thread) }}" enctype="multipart/form-data" class="rounded-lg border border-border-subtle bg-bg-secondary p-6 shadow-sm">
                        @csrf
                        <label class="block text-sm font-medium text-text-primary">Reply</label>
                        <textarea name="message" rows="4" class="ui-input mt-1">{{ old('message') }}</textarea>
                        <label class="mt-3 block text-sm font-medium text-text-primary">Attachments</label>
                        <input type="file" name="attachments[]" multiple class="ui-input mt-1">
                        @if ($role === 'school_admin')
                            <label class="mt-3 flex items-center gap-2 text-sm text-text-secondary">
                                <input type="checkbox" name="is_internal_note" value="1" class="rounded border-border-subtle text-brand-primary">
                                Internal school note
                            </label>
                        @endif
                        <button class="ui-button-primary mt-4">Send Reply</button>
                    </form>
                @endif

                <div class="rounded-lg border border-border-subtle bg-bg-secondary p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-normal text-text-tertiary">Timeline</h3>
                    <div class="mt-4 space-y-4">
                        @forelse ($thread->events->sortByDesc('occurred_at') as $event)
                            <div class="border-l-2 border-border-subtle pl-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-medium text-text-primary">{{ $event->title }}</p>
                                    <span class="text-xs text-text-tertiary">{{ $event->occurred_at?->format('d M Y H:i') ?: $event->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <p class="mt-1 text-sm text-text-secondary">{{ $event->body ?: ucwords(str_replace('_', ' ', $event->event_type)) }}</p>
                                <p class="mt-1 text-xs text-text-tertiary">{{ $event->actor?->name ?? 'System' }} - {{ ucwords(str_replace('_', ' ', $event->actor_role ?: 'system')) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-text-secondary">No timeline events recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                @if ($canAssign)
                    <form method="POST" action="{{ route('school.support.assign', $thread) }}" class="rounded-lg border border-border-subtle bg-bg-secondary p-6 shadow-sm">
                        @csrf
                        @method('PATCH')
                        <label class="block text-sm font-medium text-text-primary">Assign To</label>
                        <select name="assigned_to" class="ui-input mt-1">
                            <option value="">Unassigned</option>
                            @foreach ($assignees as $assignee)
                                <option value="{{ $assignee->id }}" @selected((int) $thread->assigned_to === (int) $assignee->id)>{{ $assignee->name }}</option>
                            @endforeach
                        </select>
                        <button class="ui-button-secondary mt-4 w-full">Update Assignment</button>
                    </form>
                @endif

                @if ($canEscalate)
                    <form method="POST" action="{{ route('school.support.escalate', $thread) }}" class="rounded-lg border border-border-subtle bg-bg-secondary p-6 shadow-sm">
                        @csrf
                        <label class="block text-sm font-medium text-text-primary">Escalation Reason</label>
                        <textarea name="reason" rows="4" class="ui-input mt-1">{{ old('reason') }}</textarea>
                        <button class="ui-button-danger mt-4 w-full">Escalate to Installation Admin</button>
                    </form>
                @endif

                <div class="rounded-lg border border-border-subtle bg-bg-secondary p-6 shadow-sm">
                    <p class="text-sm text-text-secondary">Priority</p>
                    <p class="mt-1 font-semibold text-text-primary">{{ ucfirst($thread->priority) }}</p>
                    <p class="mt-3 text-sm text-text-secondary">Route</p>
                    <p class="mt-1 font-semibold text-text-primary">{{ $thread->routeLabel() }}</p>
                </div>

                @if ($thread->status !== 'closed')
                    <form method="POST" action="{{ route('school.support.close', $thread) }}" class="rounded-lg border border-border-subtle bg-bg-secondary p-6 shadow-sm">
                        @csrf
                        @method('PATCH')
                        <button class="ui-button-secondary w-full">Close Thread</button>
                    </form>
                @endif
            </div>
    </div>
</x-app-layout>
