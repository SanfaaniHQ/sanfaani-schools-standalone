<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Result Reviews</h2>
                <p class="mt-1 text-sm text-gray-500">Review, return, approve, and publish teacher-submitted results.</p>
            </div>
            <a href="{{ route('school.dashboard') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Dashboard</a>
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

            <form method="GET" class="ui-filter-bar grid gap-3 md:grid-cols-[minmax(0,1fr)_auto_auto]">
                <select name="status" class="ui-input">
                    <option value="">Pending review</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="ui-button-primary">Filter</button>
                <a href="{{ route('school.result-reviews.index') }}" class="ui-button-secondary">Clear filter</a>
            </form>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="safe-scroll-x hidden rounded-none border-0 shadow-none md:block" role="region" aria-label="Result review submissions" tabindex="0">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Teacher</th>
                                <th class="px-5 py-3">Class / Subject</th>
                                <th class="px-5 py-3">Session / Term</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($submissions as $submission)
                                <tr>
                                    <td class="px-5 py-3">{{ $submission->teacher?->name }}</td>
                                    <td class="px-5 py-3">{{ $submission->schoolClass?->name }}<br><span class="text-xs text-gray-500">{{ $submission->subject?->name }}</span></td>
                                    <td class="px-5 py-3">{{ $submission->academicSession?->name }} / {{ $submission->term?->name }}</td>
                                    <td class="px-5 py-3"><x-status-badge :status="$submission->status" /></td>
                                    <td class="px-5 py-3 text-right"><a href="{{ route('school.result-reviews.show', $submission) }}" class="text-sm font-medium text-gray-900">Review</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">No teacher results are waiting for review.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mobile-card-list p-4 md:hidden">
                    @forelse ($submissions as $submission)
                        <article class="mobile-table-card">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-text-primary">{{ $submission->teacher?->name ?: 'Teacher not assigned' }}</h3>
                                    <p class="mt-1 text-sm text-text-secondary">{{ $submission->schoolClass?->name ?: 'No class' }}</p>
                                </div>
                                <x-status-badge :status="$submission->status" />
                            </div>
                            <dl class="mt-4 grid gap-3 text-sm">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Subject</dt>
                                    <dd class="mt-1 text-text-primary">{{ $submission->subject?->name ?: 'No subject' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Session / Term</dt>
                                    <dd class="mt-1 text-text-primary">{{ $submission->academicSession?->name ?: 'No session' }} / {{ $submission->term?->name ?: 'No term' }}</dd>
                                </div>
                            </dl>
                            <a href="{{ route('school.result-reviews.show', $submission) }}" class="ui-button-primary mt-4 w-full">Review submission</a>
                        </article>
                    @empty
                        <x-ui.empty-state title="No results awaiting review" body="No teacher results match the selected review status." />
                    @endforelse
                </div>
                <div class="p-5">{{ $submissions->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
