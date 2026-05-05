<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">School Public Page</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $school->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('public.school-page.show', $page->slug) }}" target="_blank" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Open page</a>
                <a href="{{ route('admin.schools.edit', $school) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Back to school</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-5 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            <form method="POST" action="{{ route('admin.schools.public-page.update', $school) }}" class="space-y-6 rounded-xl bg-white p-6 shadow-sm">
                @csrf
                @method('PATCH')
                @include('shared.school-public-page-form', ['canManageActivation' => true])
                <div class="flex flex-wrap gap-2">
                    <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">Save settings</button>
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
