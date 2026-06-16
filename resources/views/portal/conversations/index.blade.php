<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Messages</h2>
            <p class="mt-1 text-sm text-gray-500">
                Start and manage conversations with the school.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="lg:col-span-1">
                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Start Conversation</h3>

                    @if ($errors->any())
                        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('portal.conversations.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-gray-700">Subject</span>
                            <input type="text" name="subject" value="{{ old('subject') }}" class="w-full rounded-lg border-gray-300 text-sm" required>
                        </label>

                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-gray-700">Category</span>
                            <select name="conversation_type" class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="general">General</option>
                                <option value="academic">Academic</option>
                                <option value="finance">Finance</option>
                                <option value="result">Result</option>
                                <option value="attendance">Attendance</option>
                            </select>
                        </label>

                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-gray-700">Recipient</span>
                            <select name="recipient_user_ids[]" class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="">Auto assign to school team</option>
                                @foreach ($recipients as $recipient)
                                    <option value="{{ $recipient->id }}">{{ $recipient->name }}  {{ $recipient->email }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-gray-700">Message</span>
                            <textarea name="body" rows="5" class="w-full rounded-lg border-gray-300 text-sm" required>{{ old('body') }}</textarea>
                        </label>

                        <button type="submit" class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="rounded-2xl border bg-white shadow-sm">
                    <div class="border-b px-5 py-4">
                        <h3 class="text-base font-semibold text-gray-900">Conversations</h3>
                    </div>

                    @if ($conversations->isEmpty())
                        <div class="p-8 text-center text-sm text-gray-500">
                            No conversation yet.
                        </div>
                    @else
                        <div class="divide-y">
                            @foreach ($conversations as $conversation)
                                <a href="{{ route('portal.conversations.show', ['conversationId' => $conversation->id]) }}" class="block p-5 hover:bg-gray-50">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $conversation->subject }}</h4>
                                            <p class="mt-1 text-sm text-gray-500">
                                                {{ $conversation->typeLabel() }}  {{ $conversation->messages_count }} messages
                                            </p>
                                            <p class="mt-1 text-xs text-gray-400">
                                                Last activity: {{ $conversation->last_message_at?->diffForHumans() ?? 'No activity' }}
                                            </p>
                                        </div>

                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase text-gray-700">
                                            {{ $conversation->statusLabel() }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <div class="border-t px-5 py-3">
                            {{ $conversations->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
