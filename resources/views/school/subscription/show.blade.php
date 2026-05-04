<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Subscription</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $school->name }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                @if ($subscription)
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div><p class="text-xs uppercase text-gray-500">Plan</p><p class="mt-1 font-semibold">{{ $subscription->subscriptionPlan->name ?? $subscription->plan_name_snapshot }}</p></div>
                        <div><p class="text-xs uppercase text-gray-500">Status</p><p class="mt-1"><x-status-badge :status="$subscription->status" /></p></div>
                        <div><p class="text-xs uppercase text-gray-500">Ends</p><p class="mt-1 font-semibold">{{ $subscription->ends_at?->format('d M Y') ?? 'Open' }}</p></div>
                        <div><p class="text-xs uppercase text-gray-500">Payment</p><p class="mt-1"><x-status-badge :status="$subscription->payment_status" /></p></div>
                    </div>
                @else
                    <h3 class="text-base font-semibold text-gray-900">No plan assigned yet.</h3>
                    <p class="mt-2 text-sm text-gray-600">Your active school can continue using core production features unless Super Admin explicitly disables a feature.</p>
                @endif
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Plan Features</h3>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @forelse ($features as $feature)
                        <div class="rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-sm font-medium text-gray-900">{{ $feature->feature_name }}</p>
                                <x-status-badge :status="$feature->is_enabled ? 'active' : 'inactive'" />
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Limit: {{ $feature->limit_value ?? 'No fixed limit' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">Feature details will appear after a plan is assigned.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
