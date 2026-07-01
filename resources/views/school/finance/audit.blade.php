<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Finance Audit Review</h2>
                <p class="mt-1 text-sm text-gray-500">Readable finance activity for {{ $school->name }} using the existing audit log system.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.finance.reports') }}" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Finance Reports</a>
                <a href="{{ route('school.finance.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Finance Overview</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('school.finance.audit') }}" class="grid gap-3 rounded-2xl bg-white p-4 shadow-sm md:grid-cols-4">
                <select name="action" class="rounded-xl border-gray-300 text-sm shadow-sm">
                    <option value="">All finance actions</option>
                    @foreach ($actions as $action)
                        <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ str($action)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    From
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm">
                </label>
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    To
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm">
                </label>
                <select name="school_class_id" class="rounded-xl border-gray-300 text-sm shadow-sm">
                    <option value="">All classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected((int) ($filters['school_class_id'] ?? 0) === (int) $class->id)>{{ $class->name }} {{ $class->section }}</option>
                    @endforeach
                </select>
                <select name="student_id" class="rounded-xl border-gray-300 text-sm shadow-sm md:col-span-2">
                    <option value="">All students</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected((int) ($filters['student_id'] ?? 0) === (int) $student->id)>
                            {{ $student->fullName() }} @if ($student->admission_number) ({{ $student->admission_number }}) @endif
                        </option>
                    @endforeach
                </select>
                <div class="flex flex-wrap gap-2 md:col-span-2">
                    <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Filter audit</button>
                    <a href="{{ route('school.finance.audit') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Clear</a>
                </div>
                <p class="text-xs text-gray-500 md:col-span-4">
                    This view shows only safe finance identifiers, amounts, dates, methods, and statuses. Raw payloads, notes, references, stack traces, and secrets are not displayed.
                </p>
            </form>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Finance Audit Entries</h3>
                    <p class="mt-1 text-sm text-gray-500">Fee items, assignments, invoice generation, payment recording, and approved finance status actions.</p>
                </div>
                <div class="safe-scroll-x rounded-none border-0 shadow-none" role="region" aria-label="Finance audit entries" tabindex="0">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Action</th>
                                <th class="px-4 py-3 text-left">Actor</th>
                                <th class="px-4 py-3 text-left">Safe Details</th>
                                <th class="px-4 py-3 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900">{{ str($log->action ?? $log->event)->replace('_', ' ')->title() }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $log->category ?? $log->action_tag ?? 'finance' }} - {{ $log->severity ?? 'info' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $log->user?->name ?? $log->actor_type ?? 'System' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($log->safe_finance_metadata)
                                            <div class="flex max-w-3xl flex-wrap gap-2">
                                                @foreach ($log->safe_finance_metadata as $key => $value)
                                                    <span class="rounded-md border border-gray-200 bg-gray-50 px-2 py-1 text-xs text-gray-700">
                                                        <span class="font-semibold">{{ str($key)->replace('_', ' ')->title() }}:</span>
                                                        @if (is_bool($value))
                                                            {{ $value ? 'Yes' : 'No' }}
                                                        @elseif (is_array($value))
                                                            {{ count($value) }}
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-500">No safe finance metadata</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $log->created_at?->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No finance audit entries found.</p>
                                        <p class="mt-1 text-sm text-gray-500">Finance activity will appear here after authorized fee, invoice, and payment workflows run.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 px-6 py-4">{{ $logs->links() }}</div>
            </div>

            <x-ui.panel tone="info">
                <p class="text-sm leading-6 text-text-secondary">
                    Finance audit review reuses the existing audit log system and does not create a duplicate audit store. School admins and accountants with finance access can review these entries inside the active school workspace only.
                </p>
            </x-ui.panel>
        </div>
    </div>
</x-app-layout>
