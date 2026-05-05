<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    School Management
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Add, view, and manage schools using Sanfaani Schools.
                </p>
            </div>

            <a href="{{ route('admin.schools.create') }}"
               class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Add School
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
                        Schools
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Total schools: {{ $schools->total() }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">School</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Subscription</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($schools as $school)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $school->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $school->slug }}</div>
                                        <div class="mt-1 text-xs font-medium text-gray-500">
                                            Code: {{ $school->school_code ?? 'Not set' }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $school->email ?? 'No email' }}</div>
                                        <div class="text-sm text-gray-500">{{ $school->phone ?? 'No phone' }}</div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                            <x-status-badge :status="$school->trashed() ? 'archived' : $school->status" />
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <x-status-badge :status="$school->subscription_status" />
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex justify-end gap-3">
                                            @if (! $school->trashed())
                                                <a href="{{ route('admin.schools.edit', $school) }}"
                                                   class="text-sm font-medium text-gray-900 hover:text-gray-600">
                                                    Edit
                                                </a>

                                                <form method="POST"
                                                      action="{{ route('admin.schools.support-access.start', $school) }}"
                                                      data-confirm="Start Super Admin support access for this school?"
                                                      data-loading-text="Opening...">
                                                    @csrf
                                                    <div class="flex items-center gap-2">
                                                        <select name="role_context" class="rounded-lg border-gray-300 py-1 text-xs">
                                                            <option value="school_admin">School Admin</option>
                                                            <option value="result_officer">Result Officer</option>
                                                            <option value="teacher">Teacher</option>
                                                        </select>
                                                        <button type="submit" class="text-sm font-medium text-emerald-700 hover:text-emerald-600">
                                                            Support Access
                                                        </button>
                                                    </div>
                                                </form>

                                                <form method="POST"
                                                      action="{{ route('admin.schools.archive', $school) }}"
                                                      data-confirm="Archive this school? School users will lose access."
                                                      data-loading-text="Archiving...">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-500">
                                                        Archive
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST"
                                                      action="{{ route('admin.schools.restore', $school->id) }}"
                                                      data-confirm="Restore this school?"
                                                      data-loading-text="Restoring...">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-medium text-green-700 hover:text-green-500">
                                                        Restore
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No schools yet.</p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Create your first school to start onboarding.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $schools->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
