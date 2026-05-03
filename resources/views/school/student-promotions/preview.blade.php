<x-app-layout>
    @php
        $counts = [
            'promote' => $defaultAction === 'promote' ? ($selectAll ? $students->count() : 0) : 0,
            'repeat' => $defaultAction === 'repeat' ? ($selectAll ? $students->count() : 0) : 0,
            'graduate' => $defaultAction === 'graduate' ? ($selectAll ? $students->count() : 0) : 0,
            'transfer' => $defaultAction === 'transfer' ? ($selectAll ? $students->count() : 0) : 0,
            'withdraw' => 0,
            'skip' => 0,
        ];
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Preview Promotion</h2>
            <p class="mt-1 text-sm text-gray-500">Review students and choose an action per row.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                @foreach ($counts as $label => $value)
                    <div class="rounded-2xl bg-white p-4 shadow-sm">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ ucfirst($label) }}</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900" data-promotion-count="{{ $label }}">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mb-6 rounded-2xl border border-amber-100 bg-amber-50 p-5 text-sm text-amber-800">
                Published results and historical records will not be deleted. Selected students only are processed.
            </div>

            <form method="POST"
                  action="{{ route('school.student-promotions.store') }}"
                  data-confirm="Are you sure you want to process this promotion? This will update student class placement for the selected session."
                  data-loading-text="Processing promotion..."
                  class="overflow-hidden rounded-2xl bg-white shadow-sm">
                @csrf

                @foreach ($context as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Select</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Admission No.</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Current Class</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Target Class</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($students as $student)
                                <tr>
                                    <td class="px-4 py-3">
                                        <input type="checkbox" name="students[{{ $student->id }}][selected]" value="1" @checked($selectAll) data-promotion-selected class="rounded border-gray-300 text-gray-900">
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $student->admission_number }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $student->fullName() }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $student->schoolClass->name ?? 'No class' }} {{ $student->schoolClass->section ?? '' }}</td>
                                    <td class="px-4 py-3">
                                        <select name="students[{{ $student->id }}][action]" data-promotion-action class="w-36 rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                            @foreach ($actions as $action)
                                                <option value="{{ $action }}" @selected($defaultAction === $action)>{{ ucfirst($action) }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">
                                        <select name="students[{{ $student->id }}][to_school_class_id]" class="w-44 rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                            <option value="">Use batch target</option>
                                            @foreach ($classes as $class)
                                                <option value="{{ $class->id }}" @selected((int) ($context['to_school_class_id'] ?? 0) === (int) $class->id)>{{ $class->name }} {{ $class->section }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="students[{{ $student->id }}][notes]" class="w-56 rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" placeholder="Optional note">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 px-6 py-4">
                    <a href="{{ route('school.student-promotions.create') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Back</a>
                    <button type="submit" data-loading-text="Processing promotion..." class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Confirm Promotion</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const counters = document.querySelectorAll('[data-promotion-count]');
            const rows = document.querySelectorAll('[data-promotion-selected]');
            const actions = document.querySelectorAll('[data-promotion-action]');
            const labels = ['promote', 'repeat', 'graduate', 'transfer', 'withdraw', 'skip'];

            function refreshCounts() {
                const counts = Object.fromEntries(labels.map((label) => [label, 0]));

                rows.forEach((checkbox) => {
                    if (! checkbox.checked) {
                        return;
                    }

                    const row = checkbox.closest('tr');
                    const action = row?.querySelector('[data-promotion-action]')?.value || 'skip';

                    if (counts[action] !== undefined) {
                        counts[action]++;
                    }
                });

                counters.forEach((counter) => {
                    counter.textContent = counts[counter.dataset.promotionCount] || 0;
                });
            }

            rows.forEach((checkbox) => checkbox.addEventListener('change', refreshCounts));
            actions.forEach((select) => select.addEventListener('change', refreshCounts));
            refreshCounts();
        });
    </script>
</x-app-layout>
