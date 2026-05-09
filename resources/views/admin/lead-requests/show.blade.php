<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Lead Request</h2>
                <p class="mt-1 text-sm text-gray-500">{{ ucfirst($lead->type) }} request from {{ $lead->name }}</p>
            </div>
            <a href="{{ route('admin.lead-requests.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm">Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-6xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @foreach ([
                            'Name' => $lead->name,
                            'School' => $lead->school_name,
                            'Email' => $lead->email,
                            'Phone' => $lead->phone,
                            'Role' => $lead->role,
                            'Students' => $lead->number_of_students,
                            'School Type' => $lead->school_type,
                            'Preferred Demo Time' => $lead->preferred_demo_time,
                            'Source' => $lead->source,
                        ] as $label => $value)
                            <div><dt class="text-xs uppercase text-gray-500">{{ $label }}</dt><dd class="mt-1 font-medium text-gray-900">{{ $value ?: 'N/A' }}</dd></div>
                        @endforeach
                    </dl>
                    @if ($lead->message)
                        <div class="mt-6 rounded-xl bg-gray-50 p-4 text-sm text-gray-700">{{ $lead->message }}</div>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('admin.lead-requests.update', $lead) }}" class="rounded-2xl bg-white p-6 shadow-sm">
                @csrf
                @method('PATCH')
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full rounded-xl border-gray-300">
                    @foreach (['new', 'contacted', 'demo_scheduled', 'trial_started', 'converted', 'closed'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $lead->status) === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <label class="mt-4 block text-sm font-medium text-gray-700">Internal Notes</label>
                <textarea name="notes" rows="8" class="mt-1 block w-full rounded-xl border-gray-300">{{ old('notes', $lead->notes ?: data_get($lead->metadata, 'internal_notes')) }}</textarea>
                <button type="submit" data-loading-text="Saving..." class="mt-4 w-full rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Save</button>
            </form>
        </div>
    </div>
</x-app-layout>
