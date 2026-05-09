<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Lead Requests</h2>
            <p class="mt-1 text-sm text-gray-500">Demo and contact requests submitted from public pages.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success')) <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div> @endif

            <form method="GET" class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm sm:grid-cols-3">
                <select name="type" class="rounded-xl border-gray-300">
                    <option value="">All types</option>
                    <option value="demo" @selected($type === 'demo')>Demo</option>
                    <option value="contact" @selected($type === 'contact')>Contact</option>
                </select>
                <select name="status" class="rounded-xl border-gray-300">
                    <option value="">All statuses</option>
                    @foreach (['new', 'contacted', 'demo_scheduled', 'trial_started', 'converted', 'closed'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Filter</button>
            </form>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr><th class="px-6 py-3 text-left">Requester</th><th class="px-6 py-3 text-left">Type</th><th class="px-6 py-3 text-left">School</th><th class="px-6 py-3 text-left">Contact</th><th class="px-6 py-3 text-left">Status</th><th class="px-6 py-3 text-right">Action</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($leads as $lead)
                            <tr>
                                <td class="px-6 py-4"><div class="font-medium text-gray-900">{{ $lead->name }}</div><div class="text-sm text-gray-500">{{ $lead->created_at->format('d M Y H:i') }}</div></td>
                                <td class="px-6 py-4 text-sm">{{ ucfirst($lead->type) }}</td>
                                <td class="px-6 py-4 text-sm">{{ $lead->school_name ?: 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm">{{ $lead->phone ?: 'N/A' }}<br>{{ $lead->email ?: 'N/A' }}</td>
                                <td class="px-6 py-4"><x-status-badge :status="$lead->status" /></td>
                                <td class="px-6 py-4 text-right"><a href="{{ route('admin.lead-requests.show', $lead) }}" class="text-sm font-medium text-gray-900">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No lead requests yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $leads->links() }}</div>
        </div>
    </div>
</x-app-layout>
