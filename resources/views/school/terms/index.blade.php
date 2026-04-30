<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Terms
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manage academic terms for {{ $school->name }}.
                </p>
            </div>

            <a href="{{ route('school.terms.create') }}"
               class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Add Term
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Term List
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Total terms: {{ $terms->total() }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Term</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Session</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Dates</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Current</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($terms as $term)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $term->name }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $term->academicSession->name }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $term->starts_at?->format('M d, Y') ?? 'No start date' }}
                                        —
                                        {{ $term->ends_at?->format('M d, Y') ?? 'No end date' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        @if ($term->is_active)
                                            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
                                                Current
                                            </span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                                No
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                            {{ ucfirst($term->status) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('school.terms.edit', $term) }}"
                                           class="text-sm font-medium text-gray-900 hover:text-gray-600">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No terms yet.</p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Create the first term for this school.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $terms->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>