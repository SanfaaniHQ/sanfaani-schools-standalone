<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Assign Teacher</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $school->name }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('school.teacher-assignments.store') }}" class="space-y-6 rounded-xl bg-white p-6 shadow-sm">
                @csrf
                @include('school.teacher-assignments.form', ['assignment' => null, 'assignmentType' => old('assignment_scope', 'subject')])
                <div class="flex flex-wrap gap-2">
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">Save assignment</button>
                    <a href="{{ route('school.teacher-assignments.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Back</a>
                    <a href="{{ route('school.dashboard') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
