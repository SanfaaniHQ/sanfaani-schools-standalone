<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Attendance</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Online attendance foundation for {{ $school->name }}.
                </p>
            </div>
            <a href="{{ route('school.attendance.reports', ['date' => $date]) }}"
               class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Reports
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
            @endif

            <div class="mb-6 grid gap-4 lg:grid-cols-[1fr_24rem]">
                <x-ui.panel>
                    <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">Current attendance context</p>
                    <h3 class="mt-2 text-2xl font-semibold text-text-primary">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-text-secondary">
                        Session {{ $activeSession?->name ?? 'not set' }} and term {{ $activeTerm?->name ?? 'not set' }} will be attached to new records when available.
                    </p>
                </x-ui.panel>

                <x-ui.panel>
                    <form method="GET" action="{{ route('school.attendance.index') }}" class="space-y-3">
                        <label class="block text-sm font-medium text-text-primary" for="attendance-date">Attendance date</label>
                        <input id="attendance-date" type="date" name="date" value="{{ $date }}"
                               class="block w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        <button class="w-full rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            View date
                        </button>
                    </form>
                </x-ui.panel>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($statuses as $status)
                    @php($value = $summaries->sum(fn ($summary) => $summary['counts'][$status] ?? 0))
                    <x-ui.stat-card :label="str($status)->title()" :value="$value" />
                @endforeach
                <x-ui.stat-card label="Recorded" :value="$summaries->sum('total')" />
                <x-ui.stat-card label="Unmarked" :value="$summaries->sum(fn ($summary) => $summary['missing'] ?? 0)" />
            </div>

            <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Class Attendance</h3>
                    <p class="mt-1 text-sm text-gray-500">Open a class to record or review daily attendance.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Class</th>
                                @foreach ($statuses as $status)
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">{{ str($status)->title() }}</th>
                                @endforeach
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Recorded</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Unmarked</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Attendance %</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($summaries as $summary)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ $summary['class']->name }} {{ $summary['class']->section }}
                                    </td>
                                    @foreach ($statuses as $status)
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $summary['counts'][$status] ?? 0 }}</td>
                                    @endforeach
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $summary['total'] }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $summary['missing'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($summary['attendance_percentage'] ?? 0, 1) }}%</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('school.attendance.classes.show', ['class' => $summary['class'], 'date' => $date]) }}"
                                           class="text-sm font-medium text-gray-900 hover:text-gray-600">
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($statuses) + 5 }}" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No attendance classes available.</p>
                                        <p class="mt-1 text-sm text-gray-500">School admins see active classes; teachers see assigned classes only.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <x-ui.panel class="mt-6" tone="info">
                <p class="text-sm text-text-secondary">
                    Browser offline attendance capture is not implemented in this stage. This screen records online attendance directly against the school database.
                </p>
            </x-ui.panel>
        </div>
    </div>
</x-app-layout>
