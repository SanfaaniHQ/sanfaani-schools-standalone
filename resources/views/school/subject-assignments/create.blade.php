<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Assign Subject</h2>
            <p class="mt-1 text-sm text-gray-500">Make a subject available generally, for selected classes, or for all active classes.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('school.subject-assignments.store') }}" class="space-y-6 rounded-2xl bg-white p-6 shadow-sm">
                @csrf
                @include('school.subject-assignments.partials.form')
                <div class="flex justify-end gap-3">
                    <a href="{{ route('school.subject-assignments.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Cancel</a>
                    <button type="submit" data-loading-text="Saving..." class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Save Assignment</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
