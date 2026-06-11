<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Attendance Reports</h2>
                <p class="mt-1 text-sm text-gray-500">Daily class attendance counts and summaries.</p>
            </div>
            <a href="{{ route('school.attendance.index', ['date' => $date]) }}"
               class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Attendance Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('school.attendance.reports') }}" class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm md:grid-cols-4">
                <input type="date" name="date" value="{{ $date }}"
                       class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                <select name="school_class_id" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900 md:col-span-2">
                    <option value="">All visible classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected((int) $selectedClassId === (int) $class->id)>
                            {{ $class->name }} {{ $class->section }}
                        </option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Run report</button>
            </form>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Class Daily Summary</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Class</th>
                                @foreach ($statuses as $status)
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">{{ str($status)->title() }}</th>
                                @endforeach
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($summaries as $summary)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        <a href="{{ route('school.attendance.classes.show', ['class' => $summary['class'], 'date' => $date]) }}" class="hover:text-gray-600">
                                            {{ $summary['class']->name }} {{ $summary['class']->section }}
                                        </a>
                                    </td>
                                    @foreach ($statuses as $status)
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $summary['counts'][$status] ?? 0 }}</td>
                                    @endforeach
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $summary['total'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($statuses) + 2 }}" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No report data for this filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
