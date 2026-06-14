<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-semibold">Admission applications</h1><p class="mt-1 text-sm text-gray-500">Review submitted applications and update each applicant's status.</p></div></x-slot>
    <form method="GET" data-loading-text="Filtering..." class="mb-5 grid gap-3 rounded-lg border bg-white p-4 md:grid-cols-5">
        <input name="search" value="{{ request('search') }}" placeholder="Name or application number" class="rounded-lg border-gray-300">
        <select name="status" class="rounded-lg border-gray-300"><option value="">All statuses</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>@endforeach</select>
        <select name="requested_class_id" class="rounded-lg border-gray-300"><option value="">All classes</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected((string) request('requested_class_id') === (string) $class->id)>{{ $class->name }}</option>@endforeach</select>
        <select name="payment_status" class="rounded-lg border-gray-300"><option value="">All payments</option>@foreach($paymentStatuses as $status)<option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>@endforeach</select>
        <button type="submit" data-loading-text="Filtering..." class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
    </form>
    <div class="overflow-x-auto rounded-lg border bg-white shadow-sm">
        <table class="min-w-full divide-y">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-5 py-3">Application</th><th class="px-5 py-3">Applicant</th><th class="px-5 py-3">Class</th><th class="px-5 py-3">Source</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr></thead>
            <tbody class="divide-y">
                @forelse($applications as $application)
                    <tr><td class="px-5 py-4 font-mono text-sm">{{ $application->application_number }}</td><td class="px-5 py-4">{{ $application->fullName() }}</td><td class="px-5 py-4 text-sm text-gray-600">{{ $application->requestedClass?->name ?? 'Not selected' }}</td><td class="px-5 py-4 text-sm text-gray-600">{{ $application->source_channel }}</td><td class="px-5 py-4 text-sm">{{ str($application->status)->replace('_', ' ')->title() }}</td><td class="px-5 py-4 text-right"><a class="font-semibold text-emerald-700" href="{{ route('admin.admissions.applications.show', $application) }}">Review</a></td></tr>
                @empty<tr><td colspan="6" class="px-5 py-12 text-center text-gray-500"><span class="block font-semibold text-gray-900">No applications found.</span><span class="mt-1 block">Adjust the filters or share the public admission form with parents.</span><a href="{{ route('admin.admissions.index') }}" class="mt-4 inline-flex rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700">Open admission sharing tools</a></td></tr>@endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $applications->links() }}</div>
</x-app-layout>
