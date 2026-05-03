<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Report Card Preview</h2>
                <p class="mt-1 text-sm text-gray-500">Sample report card using current school settings.</p>
            </div>

            <a href="{{ route('school.report-card-settings.edit') }}" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Back to Settings</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @include('school.report-card-settings.partials.preview-card', ['reportCard' => $reportCard])
        </div>
    </div>
</x-app-layout>
