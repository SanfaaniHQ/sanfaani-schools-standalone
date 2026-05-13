<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Teacher Results</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $school->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.dashboard') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Dashboard</a>
                @if ($canCreateResults ?? false)
                    <a href="{{ route('school.teacher-results.create') }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">Enter Results</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            <form method="GET" class="flex flex-wrap gap-3 rounded-xl bg-white p-4 shadow-sm">
                <select name="status" class="rounded-lg border-gray-300 text-sm">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">Filter</button>
                <a href="{{ route('school.teacher-results.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Clear filter</a>
            </form>

            <div class="rounded-xl bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Class / Subject</th>
                                <th class="px-5 py-3">Session / Term</th>
                                <th class="px-5 py-3">Teacher</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($submissions as $submission)
                                <tr>
                                    <td class="px-5 py-3">{{ $submission->schoolClass?->name }}<br><span class="text-xs text-gray-500">{{ $submission->subject?->name }}</span></td>
                                    <td class="px-5 py-3">{{ $submission->academicSession?->name }} / {{ $submission->term?->name }}</td>
                                    <td class="px-5 py-3">{{ $submission->teacher?->name }}</td>
                                    <td class="px-5 py-3"><x-status-badge :status="$submission->status" /></td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('school.teacher-results.show', $submission) }}" class="text-sm font-medium text-gray-900">Open</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">No teacher result submissions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-5">{{ $submissions->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
