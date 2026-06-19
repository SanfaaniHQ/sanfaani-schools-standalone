<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-semibold text-gray-900">{{ $liveClass->title }}</h2>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase text-gray-700">{{ str($liveClass->status)->title() }}</span>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $liveClass->schoolClass?->name }} {{ $liveClass->schoolClass?->section }}
                    @if ($liveClass->subject)
                        / {{ $liveClass->subject->name }}
                    @endif
                </p>
            </div>
            <a href="{{ route('portal.live-classes.index') }}" class="rounded-lg border px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Live Classes</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_24rem] lg:px-8">
            <div class="space-y-6">
                <div class="rounded-2xl border bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Session Details</h3>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-gray-500">Starts</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $liveClass->starts_at?->format('d M Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-gray-500">Ends</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $liveClass->ends_at?->format('d M Y H:i') ?? 'Not set' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-gray-500">Timezone</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $liveClass->timezone ?: config('app.timezone') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-gray-500">Teacher</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $liveClass->teacher?->name ?? 'Not assigned' }}</dd>
                        </div>
                    </dl>

                    @if ($liveClass->description)
                        <p class="mt-4 text-sm leading-6 text-gray-600">{{ $liveClass->description }}</p>
                    @endif
                </div>
            </div>

            <aside class="space-y-6">
                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Join Session</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $provider['label'] }} / manual meeting link</p>
                    <form method="POST" action="{{ route('portal.live-classes.join', $liveClass) }}" class="mt-4">
                        @csrf
                        <button class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Join Live Class</button>
                    </form>

                    @if ($liveClass->meeting_password)
                        <div class="mt-4 rounded-xl border bg-gray-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Meeting Password</p>
                            <p class="mt-1 break-words font-mono text-sm font-semibold text-gray-900">{{ $liveClass->meeting_password }}</p>
                        </div>
                    @endif

                    <div class="mt-4 rounded-xl border bg-gray-50 p-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Reminder</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">
                            @if ($participant?->reminder_sent_at)
                                Sent {{ $participant->reminder_sent_at->diffForHumans() }}
                            @elseif ($participant?->reminder_due_at)
                                Due {{ $participant->reminder_due_at->diffForHumans() }}
                            @else
                                Not scheduled
                            @endif
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
