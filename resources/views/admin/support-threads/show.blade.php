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

                <div class="space-y-3 rounded-2xl bg-white p-6 shadow-sm">
                    @forelse ($thread->messages as $message)
                        <div class="rounded-xl border border-gray-100 p-4">
                            <p class="text-xs text-gray-500">{{ $message->sender?->name ?? 'Unknown' }} - {{ $message->created_at?->diffForHumans() }}</p>
                            <p class="mt-2 text-sm text-gray-800">{{ $message->message }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">No messages yet.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('admin.support-threads.reply', $thread) }}" class="rounded-2xl bg-white p-6 shadow-sm">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700">Reply</label>
                    <textarea name="message" rows="4" class="mt-1 block w-full rounded-xl border-gray-300">{{ old('message') }}</textarea>
                    <label class="mt-3 flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_internal_note" value="1" class="rounded border-gray-300">
                        Internal note (not for school-facing message)
                    </label>
                    <button class="mt-4 rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Send Reply</button>
                </form>
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
            </div>
        </div>
    </div>
</x-app-layout>
