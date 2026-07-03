<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Demo Session #{{ $demoSession->id }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $demoSession->school?->name ?? 'Unassigned demo school' }}</p>
            </div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.demo.credentials', $demoSession) }}">
                    @csrf
                    <button class="rounded-md border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Show credentials once</button>
                </form>
                @if ($demoSession->status !== \App\Models\DemoSession::STATUS_EXPIRED)
                    <form method="POST" action="{{ route('admin.demo.expire', $demoSession) }}">
                        @csrf
                        <button class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Expire demo</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif

            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Status</p>
                    <p class="mt-2 text-lg font-semibold text-gray-950">{{ str($demoSession->status)->replace('_', ' ')->title() }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Starts</p>
                    <p class="mt-2 text-lg font-semibold text-gray-950">{{ $demoSession->starts_at?->toFormattedDateString() ?? 'Pending' }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Expires</p>
                    <p class="mt-2 text-lg font-semibold text-gray-950">{{ $demoSession->expires_at?->toFormattedDateString() ?? 'No expiry' }}</p>
                </div>
            </div>

            @include('admin.demo.partials.credentials', [
                'demoSession' => $demoSession,
                'revealedCredentials' => $revealedCredentials,
            ])

            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Activity</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($demoSession->activities->sortByDesc('created_at') as $activity)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-medium text-gray-900">{{ $activity->event }}</p>
                                <p class="text-xs text-gray-500">{{ $activity->created_at->toDayDateTimeString() }}</p>
                            </div>
                            @if ($activity->description)
                                <p class="mt-1 text-sm text-gray-600">{{ $activity->description }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-sm text-gray-500">No demo activity has been recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
