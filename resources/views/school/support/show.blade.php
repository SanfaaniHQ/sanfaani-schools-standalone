<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">{{ $thread->subject }}</h2>
            <p class="mt-1 text-sm text-gray-500">Status: {{ ucfirst(str_replace('_', ' ', $thread->status)) }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-6xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
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

                @if ($thread->status !== 'closed')
                    <form method="POST" action="{{ route('school.support.reply', $thread) }}" class="rounded-2xl bg-white p-6 shadow-sm">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700">Reply</label>
                        <textarea name="message" rows="4" class="mt-1 block w-full rounded-xl border-gray-300">{{ old('message') }}</textarea>
                        <button class="mt-4 rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Send Reply</button>
                    </form>
                @endif
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-600">Assigned to</p>
                    <p class="mt-1 font-semibold text-gray-900">{{ $thread->assignedUser?->name ?? 'Not assigned yet' }}</p>
                    <p class="mt-3 text-sm text-gray-600">Priority</p>
                    <p class="mt-1 font-semibold text-gray-900">{{ ucfirst($thread->priority) }}</p>
                </div>

                @if ($thread->status !== 'closed')
                    <form method="POST" action="{{ route('school.support.close', $thread) }}" class="rounded-2xl bg-white p-6 shadow-sm">
                        @csrf
                        @method('PATCH')
                        <button class="w-full rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Close Thread</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
