<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">School Subscriptions</h2>
                <p class="mt-1 text-sm text-gray-500">Assign plans to schools for pilot billing and access control.</p>
            </div>
            <a href="{{ route('admin.school-subscriptions.create') }}" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Assign Plan</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success')) <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div> @endif
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">School</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($subscriptions as $subscription)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $subscription->school->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $subscription->subscriptionPlan->name ?? $subscription->plan_name_snapshot }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $subscription->starts_at?->format('d M Y') ?? 'N/A' }} - {{ $subscription->ends_at?->format('d M Y') ?? 'Open' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $subscription->currency }} {{ number_format($subscription->amount_paid, 2) }} / {{ number_format($subscription->amount_due, 2) }}<br><x-status-badge :status="$subscription->payment_status" /></td>
                                <td class="px-6 py-4"><x-status-badge :status="$subscription->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No subscriptions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $subscriptions->links() }}</div>
        </div>
    </div>
</x-app-layout>
