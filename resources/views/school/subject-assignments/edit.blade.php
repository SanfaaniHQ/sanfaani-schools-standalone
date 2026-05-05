<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Edit Subject Assignment</h2>
            <p class="mt-1 text-sm text-gray-500">Update class scope, assignment type, or elective behavior.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('school.subject-assignments.update', $assignment) }}" class="space-y-6 rounded-2xl bg-white p-6 shadow-sm">
                @csrf
                @method('PATCH')
                @include('school.subject-assignments.partials.form', ['assignment' => $assignment])
                <div class="flex justify-end gap-3">
                    <a href="{{ route('school.subject-assignments.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Cancel</a>
                    <button type="submit" data-loading-text="Saving..." class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Update Assignment</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
