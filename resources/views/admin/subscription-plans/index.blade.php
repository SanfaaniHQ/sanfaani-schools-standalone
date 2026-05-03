<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Subscription Plans</h2>
                <p class="mt-1 text-sm text-gray-500">Manage pilot plans and feature availability.</p>
            </div>
            <a href="{{ route('admin.subscription-plans.create') }}"
               class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Add Plan
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
            @endif

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Features</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($plans as $plan)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $plan->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $plan->slug }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $plan->currency }} {{ number_format($plan->price, 2) }} / {{ $plan->billing_cycle }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $plan->features_count }}</td>
                                <td class="px-6 py-4"><x-status-badge :status="$plan->status" /></td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('admin.subscription-plans.edit', $plan) }}"
                                           class="text-sm font-medium text-gray-900 hover:text-gray-600">Edit</a>
                                        @if ($plan->status === 'archived')
                                            <form method="POST" action="{{ route('admin.subscription-plans.activate', $plan) }}" data-loading-text="Activating...">
                                                @csrf
                                                <button class="text-sm font-medium text-green-700">Activate</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.subscription-plans.archive', $plan) }}" data-confirm="Archive this plan?" data-loading-text="Archiving...">
                                                @csrf
                                                <button class="text-sm font-medium text-red-700">Archive</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No plans yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $plans->links() }}</div>
        </div>
    </div>
</x-app-layout>
