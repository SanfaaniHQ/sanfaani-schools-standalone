<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Support Thread Details</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $thread->subject }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="space-y-4 lg:col-span-2">
                @if (session('success'))
                    <div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
                @endif

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">{{ ucfirst(str_replace('_', ' ', $thread->status)) }}</span>
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800">{{ ucfirst($thread->priority) }}</span>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700">{{ $thread->routeLabel() }}</span>
                        @if ($thread->isEscalated())
                            <span class="rounded-full bg-red-50 px-3 py-1 text-red-700">Escalated</span>
                        @endif
                    </div>
                    <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div><dt class="text-xs uppercase text-gray-500">School</dt><dd class="mt-1 font-medium text-gray-900">{{ $thread->school?->name ?? 'Platform' }}</dd></div>
                        <div><dt class="text-xs uppercase text-gray-500">Created By</dt><dd class="mt-1 font-medium text-gray-900">{{ $thread->creator?->name ?? 'Unknown' }}</dd></div>
                        <div><dt class="text-xs uppercase text-gray-500">Creator Role</dt><dd class="mt-1 font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $thread->creator_role ?: 'legacy')) }}</dd></div>
                        <div><dt class="text-xs uppercase text-gray-500">Assigned To</dt><dd class="mt-1 font-medium text-gray-900">{{ $thread->assignedUser?->name ?? 'Unassigned' }}</dd></div>
                        <div><dt class="text-xs uppercase text-gray-500">Escalated At</dt><dd class="mt-1 font-medium text-gray-900">{{ $thread->escalated_at?->format('d M Y H:i') ?: 'N/A' }}</dd></div>
                        <div><dt class="text-xs uppercase text-gray-500">Escalated By</dt><dd class="mt-1 font-medium text-gray-900">{{ $thread->escalatedBy?->name ?? 'N/A' }}</dd></div>
                    </dl>
                </div>

                <div class="space-y-3 rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Conversation</h3>
                    @forelse ($messages as $message)
                        <div class="rounded-xl border border-gray-100 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-xs text-gray-500">{{ $message->sender?->name ?? 'Unknown' }} - {{ ucwords(str_replace('_', ' ', $message->sender_role ?: 'support')) }} - {{ $message->created_at?->diffForHumans() }}</p>
                                @if ($message->is_internal_note)
                                    <span class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600">Internal</span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm text-gray-800">{{ $message->message }}</p>
                            @if ($message->attachments->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($message->attachments as $attachment)
                                        <a href="{{ route('admin.support-attachments.download', $attachment) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                            {{ $attachment->original_name }} ({{ number_format($attachment->size / 1024, 1) }} KB)
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">No messages yet.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('admin.support-threads.reply', $thread) }}" enctype="multipart/form-data" class="rounded-2xl bg-white p-6 shadow-sm">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700">Reply</label>
                    <textarea name="message" rows="4" class="mt-1 block w-full rounded-xl border-gray-300">{{ old('message') }}</textarea>
                    <label class="mt-3 block text-sm font-medium text-gray-700">Attachments</label>
                    <input type="file" name="attachments[]" multiple class="mt-1 block w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-700">
                    <label class="mt-3 flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_internal_note" value="1" class="rounded border-gray-300">
                        Internal note
                    </label>
                    <button class="mt-4 rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Send Reply</button>
                </form>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Timeline</h3>
                    <div class="mt-4 space-y-4">
                        @forelse ($thread->events->sortByDesc('occurred_at') as $event)
                            <div class="border-l-2 border-gray-200 pl-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-medium text-gray-900">{{ $event->title }}</p>
                                    <span class="text-xs text-gray-500">{{ $event->occurred_at?->format('d M Y H:i') ?: $event->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-600">{{ $event->body ?: ucwords(str_replace('_', ' ', $event->event_type)) }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ $event->actor?->name ?? 'System' }} - {{ ucwords(str_replace('_', ' ', $event->actor_role ?: 'system')) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No timeline events recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <form method="POST" action="{{ route('admin.support-threads.status', $thread) }}" class="rounded-2xl bg-white p-6 shadow-sm">
                    @csrf
                    @method('PATCH')
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="mt-1 block w-full rounded-xl border-gray-300">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($thread->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                    <label class="mt-3 block text-sm font-medium text-gray-700">Priority</label>
                    <select name="priority" class="mt-1 block w-full rounded-xl border-gray-300">
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority }}" @selected($thread->priority === $priority)>{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                    <button class="mt-4 rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Update Status</button>
                </form>

                <form method="POST" action="{{ route('admin.support-threads.assign', $thread) }}" class="rounded-2xl bg-white p-6 shadow-sm">
                    @csrf
                    @method('PATCH')
                    <label class="block text-sm font-medium text-gray-700">Assign To</label>
                    <select name="assigned_to" class="mt-1 block w-full rounded-xl border-gray-300">
                        <option value="">Unassigned</option>
                        @foreach ($assignees as $assignee)
                            <option value="{{ $assignee->id }}" @selected((int) $thread->assigned_to === (int) $assignee->id)>{{ $assignee->name }}</option>
                        @endforeach
                    </select>
                    <button class="mt-4 rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Update Assignment</button>
                </form>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Escalation History</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($thread->escalationHistories->sortByDesc('escalated_at') as $history)
                            <div class="rounded-xl bg-gray-50 p-4 text-sm">
                                <p class="font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $history->from_role ?: 'school')) }} to {{ ucwords(str_replace('_', ' ', $history->to_role)) }}</p>
                                <p class="mt-1 text-gray-600">{{ $history->reason ?: 'No reason provided.' }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $history->escalatedBy?->name ?? 'System' }} - {{ $history->escalated_at?->format('d M Y H:i') }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No escalation history yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
