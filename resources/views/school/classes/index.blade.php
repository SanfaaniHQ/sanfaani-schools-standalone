<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Classes</h2>
                <p class="mt-1 text-sm text-gray-500">Manage classes for {{ $school->name }}.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.classes.upload.index') }}"
                   class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Upload CSV
                </a>
                <a href="{{ route('school.classes.create') }}"
                   class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Add Class
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
            @endif

            @include('school.partials.next-actions')

            <form method="GET" action="{{ route('school.classes.index') }}" class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm md:grid-cols-4">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, code, or section"
                       class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                <select name="status" class="rounded-xl border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">All statuses</option>
                    @foreach (['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700">
                    <input type="checkbox" name="include_archived" value="1" @checked((bool) ($filters['include_archived'] ?? false)) class="rounded border-gray-300">
                    Include archived
                </label>
                <div class="flex gap-2">
                    <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Search</button>
                    <a href="{{ route('school.classes.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Clear</a>
                </div>
            </form>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Class List</h3>
                    <p class="mt-1 text-sm text-gray-500">Total classes: {{ $classes->total() }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Section</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($classes as $schoolClass)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $schoolClass->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $schoolClass->code ?? 'No code' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $schoolClass->section ?? 'No section' }}</td>
                                    <td class="px-6 py-4"><x-status-badge :status="$schoolClass->trashed() ? 'archived' : $schoolClass->status" /></td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-3">
                                            @if (! $schoolClass->trashed())
                                                <a href="{{ route('school.classes.edit', $schoolClass) }}" class="text-sm font-medium text-gray-900 hover:text-gray-600">Edit</a>
                                                <form method="POST" action="{{ route('school.classes.destroy', $schoolClass) }}" data-confirm="Delete this class? Linked records will be archived safely." data-loading-text="Checking...">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-500">Delete</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('school.classes.restore', $schoolClass->id) }}" data-confirm="Restore this class?" data-loading-text="Restoring...">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-medium text-green-700 hover:text-green-600">Restore</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No classes found.</p>
                                        <p class="mt-1 text-sm text-gray-500">Create a class or clear the filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">{{ $classes->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
