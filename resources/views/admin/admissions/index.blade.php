<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div><h1 class="text-xl font-semibold text-gray-900">Admissions</h1><p class="mt-1 text-sm text-gray-500">Private admission operations for {{ $school->name }}.</p></div>
            <div class="flex gap-2"><a href="{{ route('admin.admissions.applications.index') }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Applications</a><a href="{{ route('admin.admissions.settings') }}" class="rounded-lg border px-4 py-2 text-sm font-semibold">Settings</a></div>
        </div>
    </x-slot>
    <div class="grid gap-4 md:grid-cols-4">
        @foreach([['Applications', $totalApplications], ['New submissions', $submittedApplications], ['Accepted/admitted', $acceptedApplications], ['Documents pending', $pendingDocuments]] as [$label, $value])
            <div class="rounded-2xl border bg-white p-5 shadow-sm"><p class="text-sm text-gray-500">{{ $label }}</p><p class="mt-2 text-3xl font-bold text-gray-900">{{ $value }}</p></div>
        @endforeach
    </div>
    <div class="mt-6 rounded-2xl border bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold">Current cycle</h2>
        @if($cycle)<p class="mt-2 text-gray-600">{{ $cycle->name }} is accepting applications.</p>@else<p class="mt-2 text-gray-600">No cycle is currently open. Configure one before publishing the application link.</p>@endif
        <div class="mt-4 flex flex-wrap gap-3"><a href="{{ route('admissions.index') }}" target="_blank" class="text-sm font-semibold text-emerald-700">Open public admission page</a><a href="{{ route('admissions.embed') }}" target="_blank" class="text-sm font-semibold text-emerald-700">Preview embed form</a></div>
    </div>
</x-app-layout>
