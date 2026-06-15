<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Admissions</h1>
                <p class="mt-1 text-sm text-gray-500">Receive applications, share the public form, and review applicants for {{ $school->name }}.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.admissions.applications.index') }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Review applications</a>
                <a href="{{ route('admin.admissions.settings') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700">Admission settings</a>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-4">
        @foreach ([['Applications', $totalApplications], ['New submissions', $submittedApplications], ['Accepted or admitted', $acceptedApplications], ['Documents to review', $pendingDocuments]] as [$label, $value])
            <div class="rounded-lg border bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 rounded-lg border bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Public admission form</h2>
                @if ($cycle)
                    <p class="mt-2 text-sm text-gray-600">{{ $cycle->name }} is open. Share this link with parents or add it to the school website.</p>
                @else
                    <p class="mt-2 text-sm text-gray-600">No admission cycle is open yet. Configure a cycle before sharing the form with parents.</p>
                @endif
            </div>
            <span class="inline-flex w-fit rounded-full {{ $cycle ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} px-3 py-1 text-xs font-semibold">
                {{ $cycle ? 'Ready to share' : 'Needs attention' }}
            </span>
        </div>

        <div class="mt-5 rounded-md border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
            <span class="block text-xs font-semibold uppercase tracking-normal text-gray-500">Form link</span>
            <span class="mt-1 block break-all font-mono">{{ $publicFormUrl }}</span>
        </div>

        <div class="mt-5 flex flex-wrap gap-3">
            <button type="button" data-copy-text="{{ $publicFormUrl }}" data-copied-label="Form link copied" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white">Copy form link</button>
            <a href="{{ $publicFormUrl }}" target="_blank" rel="noopener" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700">Preview form</a>
            <a href="{{ $publicAdmissionUrl }}" target="_blank" rel="noopener" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700">Open admission page</a>
            <a href="{{ route('admin.admissions.applications.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700">Open applications</a>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-2">
            <div class="rounded-md border border-gray-200 p-4 text-sm text-gray-600">
                <p class="font-semibold text-gray-900">Share with parents</p>
                <p class="mt-1">Copy the form link into WhatsApp, SMS, email, or printed admission instructions.</p>
            </div>
            <div class="rounded-md border border-gray-200 p-4 text-sm text-gray-600">
                <p class="font-semibold text-gray-900">Add to the school website</p>
                <p class="mt-1">Use the form link as an “Apply for admission” button, or open settings for the website embed option.</p>
            </div>
        </div>
    </div>
</x-app-layout>
