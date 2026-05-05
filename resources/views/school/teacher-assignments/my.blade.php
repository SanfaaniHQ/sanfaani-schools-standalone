<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">My Assigned Classes and Subjects</h2>
                <p class="mt-1 text-sm text-gray-500">Only active assignments for your current school workspace are shown.</p>
            </div>
            <a href="{{ route('school.teacher-results.create') }}" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Enter Results</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Class Assignments</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($classAssignments as $assignment)
                        <div class="rounded-xl border border-gray-100 p-4">
                            <p class="text-sm font-semibold text-gray-900">{{ $assignment->schoolClass?->name ?? 'Class not found' }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                Session: {{ $assignment->academicSession?->name ?? 'All sessions' }} |
                                Term: {{ $assignment->term?->name ?? 'All terms' }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">No active class assignments yet.</p>
                    @endforelse
                </div>
                <div class="mt-4">{{ $classAssignments->links() }}</div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Subject Assignments</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($subjectAssignments as $assignment)
                        <div class="rounded-xl border border-gray-100 p-4">
                            <p class="text-sm font-semibold text-gray-900">{{ $assignment->subject?->name ?? 'Subject not found' }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                Class: {{ $assignment->schoolClass?->name ?? 'All classes' }} |
                                Session: {{ $assignment->academicSession?->name ?? 'All sessions' }} |
                                Term: {{ $assignment->term?->name ?? 'All terms' }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">No active subject assignments yet.</p>
                    @endforelse
                </div>
                <div class="mt-4">{{ $subjectAssignments->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
