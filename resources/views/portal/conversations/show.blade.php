<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">{{ $conversation->subject }}</h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ $conversation->typeLabel() }} conversation.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="rounded-2xl border bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Participants</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($conversation->participants as $participant)
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                            {{ $participant->user?->name ?? 'User' }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border bg-white shadow-sm">
                <div class="divide-y">
                    @foreach ($conversation->messages as $message)
                        <div class="p-5">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-semibold text-gray-900">{{ $message->sender?->name ?? 'User' }}</p>
                                <p class="text-xs text-gray-400">{{ $message->created_at?->format('M d, Y h:i A') }}</p>
                            </div>
                            <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $message->body }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border bg-white p-5 shadow-sm">
                <form method="POST" action="{{ route('portal.conversations.messages.store', ['conversationId' => $conversation->id]) }}" class="space-y-4">
                    @csrf

                    <label class="block text-sm">
                        <span class="mb-1 block font-medium text-gray-700">Reply</span>
                        <textarea name="body" rows="4" class="w-full rounded-lg border-gray-300 text-sm" required></textarea>
                    </label>

                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                        Send Reply
                    </button>
                </form>
            </div>

            <a href="{{ route('portal.conversations.index') }}" class="text-sm font-semibold text-gray-700 hover:text-gray-900">
                Back to messages
            </a>
        </div>
    </div>
</x-app-layout>
