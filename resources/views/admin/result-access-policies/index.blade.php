<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Result Access Policies</h2>
                <p class="mt-1 text-sm text-gray-500">Control scratch-card, school-paid, parent-paid, and hybrid access.</p>
            </div>
            <a href="{{ route('admin.result-access-policies.create') }}" class="rounded-xl bg-gray-900 px-4 py-2 text-sm text-white">Add Policy</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success')) <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div> @endif
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs uppercase text-gray-500">Policy</th><th class="px-6 py-3 text-left text-xs uppercase text-gray-500">Mode</th><th class="px-6 py-3 text-left text-xs uppercase text-gray-500">Rules</th><th class="px-6 py-3 text-left text-xs uppercase text-gray-500">Status</th><th class="px-6 py-3 text-right text-xs uppercase text-gray-500">Action</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($policies as $policy)
                            <tr>
                                <td class="px-6 py-4"><div class="font-medium">{{ $policy->name }}</div><div class="text-sm text-gray-500">{{ $policy->school->name ?? 'N/A' }}</div></td>
                                <td class="px-6 py-4 text-sm">{{ ucfirst(str_replace('_', ' ', $policy->access_mode)) }}</td>
                                <td class="px-6 py-4 text-sm">{{ $policy->rules->count() }}</td>
                                <td class="px-6 py-4"><x-status-badge :status="$policy->status" /></td>
                                <td class="px-6 py-4 text-right"><a class="text-sm font-medium text-gray-900" href="{{ route('admin.result-access-policies.edit', $policy) }}">Edit</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No policies yet. Default is scratch-card access.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $policies->links() }}</div>
        </div>
    </div>
</x-app-layout>
