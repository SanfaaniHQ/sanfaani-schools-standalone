<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">
                    Academic Sessions
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manage academic sessions for {{ $school->name }}.
                </p>
            </div>

            <a href="{{ route('school.sessions.create') }}"
               class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Add Session
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
                        Session List
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Total sessions: {{ $sessions->total() }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Session</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Dates</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Current</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Linked Workflow</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($sessions as $academicSession)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">
                                            {{ $academicSession->name }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $academicSession->starts_at?->format('M d, Y') ?? 'No start date' }}
                                        —
                                        {{ $academicSession->ends_at?->format('M d, Y') ?? 'No end date' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        @if ($academicSession->is_active)
                                            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
                                                Current
                                            </span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                                No
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $academicSession->terms_count }} terms<br>
                                        {{ $academicSession->student_class_enrollments_count }} enrollments<br>
                                        {{ $academicSession->teacher_result_submissions_count }} submissions
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                            {{ ucfirst($academicSession->status) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('school.sessions.edit', $academicSession) }}"
                                               class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                Edit
                                            </a>

                                            @unless ($academicSession->is_active)
                                                <form method="POST" action="{{ route('school.sessions.activate', $academicSession) }}" data-loading-text="Activating...">
                                                    @csrf
                                                    <button type="submit" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100">
                                                        Activate
                                                    </button>
                                                </form>
                                            @endunless

                                            @if ($academicSession->status !== 'archived')
                                                <form method="POST" action="{{ route('school.sessions.archive', $academicSession) }}" data-confirm="Archive this session? Historical enrollments, results, promotions, and report cards remain preserved." data-loading-text="Archiving...">
                                                    @csrf
                                                    <button type="submit" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-100">
                                                        Archive
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('school.sessions.restore', $academicSession) }}" data-loading-text="Restoring...">
                                                    @csrf
                                                    <button type="submit" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                        Restore
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-900">No academic sessions yet.</p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Create the first academic session for this school.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $sessions->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
