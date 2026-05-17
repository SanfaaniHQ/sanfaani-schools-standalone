@props([
    'results',
    'showTeacherRemark' => true,
    'showStatus' => false,
    'showPublished' => false,
    'showActions' => false,
    'school' => null,
    'student' => null,
    'studentProfileUrl' => null,
    'notifyUrl' => null,
    'emptyTitle' => 'No results found for this selection.',
    'emptyDescription' => 'Results will appear here after they are entered and published for the selected period.',
])

@php
    $rows = collect($results);
    $formatScore = fn ($value) => is_numeric($value) ? number_format((float) $value, 2) : '0.00';
    $groupedRows = $rows->groupBy(function ($result) {
        return data_get($result, 'subject.group')
            ?: data_get($result, 'subject.category')
            ?: data_get($result, 'subject.type')
            ?: 'Subjects';
    });
    $columnCount = 6
        + ($showTeacherRemark ? 1 : 0)
        + ($showStatus ? 1 : 0)
        + ($showPublished ? 1 : 0)
        + ($showActions ? 1 : 0);
@endphp

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm print:shadow-none']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="sticky top-0 z-10 bg-gray-50 text-xs font-semibold uppercase text-gray-600 print:static">
                <tr>
                    <th scope="col" class="min-w-48 px-4 py-3 text-left">Subject</th>
                    <th scope="col" class="px-4 py-3 text-left">CA</th>
                    <th scope="col" class="px-4 py-3 text-left">Exam</th>
                    <th scope="col" class="px-4 py-3 text-left">Total</th>
                    <th scope="col" class="px-4 py-3 text-left">Grade</th>
                    <th scope="col" class="min-w-40 px-4 py-3 text-left">Remark</th>
                    @if ($showTeacherRemark)
                        <th scope="col" class="min-w-48 px-4 py-3 text-left">Teacher Remark</th>
                    @endif
                    @if ($showStatus)
                        <th scope="col" class="px-4 py-3 text-left">Status</th>
                    @endif
                    @if ($showPublished)
                        <th scope="col" class="min-w-36 px-4 py-3 text-left">Published</th>
                    @endif
                    @if ($showActions)
                        <th scope="col" class="min-w-56 px-4 py-3 text-left">Actions</th>
                    @endif
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($groupedRows as $groupName => $groupResults)
                    @if ($groupedRows->count() > 1)
                        <tr class="bg-gray-50/80">
                            <td colspan="{{ $columnCount }}" class="px-4 py-2 text-xs font-semibold uppercase text-gray-500">
                                {{ str($groupName)->replace('_', ' ')->title() }}
                            </td>
                        </tr>
                    @endif

                    @foreach ($groupResults as $result)
                        @php
                            $isPublished = $result->status === \App\Enums\ResultWorkflowStatus::Published->value
                                && filled($result->published_at)
                                && blank($result->unpublished_at);
                        @endphp
                        <tr class="transition hover:bg-gray-50/80 print:hover:bg-white" data-result-row-id="{{ $result->id }}" data-result-status="{{ $result->status }}">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $result->subject->name ?? 'Unknown subject' }}</div>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $result->academicSession->name ?? 'No session' }} / {{ $result->term->name ?? 'No term' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $formatScore($result->ca_score) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $formatScore($result->exam_score) }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-950">{{ $formatScore($result->total_score) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-800">
                                    {{ $result->grade ?: 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $result->remark ?: 'N/A' }}</td>
                            @if ($showTeacherRemark)
                                <td class="px-4 py-3 text-gray-700">{{ $result->teacher_remark ?: 'N/A' }}</td>
                            @endif
                            @if ($showStatus)
                                <td class="px-4 py-3" data-result-status-label>
                                    <x-status-badge :status="$result->status" data-result-status-badge />
                                </td>
                            @endif
                            @if ($showPublished)
                                <td class="px-4 py-3 text-gray-600" data-result-published-label>
                                    @if ($isPublished)
                                        <span class="block font-semibold text-emerald-700 dark:text-emerald-300">Published</span>
                                        <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">{{ $result->published_at?->format('d M Y, h:i A') }}</span>
                                    @else
                                        Not published
                                    @endif
                                </td>
                            @endif
                            @if ($showActions)
                                <td class="px-4 py-3">
                                    <x-results.actions
                                        :result="$result"
                                        :school="$school"
                                        :student="$student"
                                        :student-profile-url="$studentProfileUrl"
                                        :notify-url="$notifyUrl"
                                    />
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="{{ $columnCount }}" class="px-6 py-12 text-center">
                            <p class="text-sm font-semibold text-gray-900">{{ $emptyTitle }}</p>
                            <p class="mt-1 text-sm text-gray-500">{{ $emptyDescription }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
