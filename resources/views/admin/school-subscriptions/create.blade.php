<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Assign School Subscription</h2>
            <p class="mt-1 text-sm text-gray-500">Use the guided form to review school, plan, billing, payment, and dates before assigning.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <form method="POST"
                  action="{{ route('admin.school-subscriptions.store') }}"
                  data-confirm="Assign this subscription? Existing active, trial, or grace subscriptions will be marked superseded."
                  data-loading-text="Assigning..."
                  class="space-y-6">
                @csrf

                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Step 1: Select School</h3>
                    <select name="school_id" class="mt-4 block w-full rounded-xl border-gray-300 shadow-sm">
                        <option value="">Select school</option>
                        @foreach ($schools as $school)
                            @php $currentSubscription = $school->subscriptions->first(); @endphp
                            <option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>
                                {{ $school->name }} - current: {{ $currentSubscription?->subscriptionPlan?->name ?? 'No assigned plan' }} ({{ $currentSubscription?->status ?? $school->subscription_status }})
                            </option>
                        @endforeach
                    </select>
                    @error('school_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </section>

                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Step 2: Select Plan</h3>
                    <select name="subscription_plan_id" class="mt-4 block w-full rounded-xl border-gray-300 shadow-sm">
                        <option value="">Select plan</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('subscription_plan_id') == $plan->id)>
                                {{ $plan->name }} - {{ $plan->currency }} {{ number_format((float) $plan->price, 2) }} / {{ str_replace('_', ' ', $plan->billing_cycle) }}
                            </option>
                        @endforeach
                    </select>
                    @error('subscription_plan_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @foreach ($plans as $plan)
                            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                                <p class="font-semibold text-gray-900">{{ $plan->name }}</p>
                                <p class="mt-1 text-sm text-gray-600">{{ $plan->description ?: 'Plan features and limits are listed below.' }}</p>
                                <ul class="mt-3 space-y-1 text-sm text-gray-600">
                                    @forelse ($plan->features as $feature)
                                        <li>{{ $feature->feature_name }} - {{ $feature->is_enabled ? 'Enabled' : 'Not enabled' }}{{ $feature->limit_value ? ' / limit: '.$feature->limit_value : '' }}</li>
                                    @empty
                                        <li>No feature rows configured yet.</li>
                                    @endforelse
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Step 3: Billing Period</h3>
                    <div class="mt-4 grid gap-6 md:grid-cols-4">
                        <div><label class="block text-sm font-medium text-gray-700">Starts At</label><input type="date" name="starts_at" value="{{ old('starts_at', now()->toDateString()) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Ends At</label><input type="date" name="ends_at" value="{{ old('ends_at') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Trial Ends</label><input type="date" name="trial_ends_at" value="{{ old('trial_ends_at') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Grace Ends</label><input type="date" name="grace_ends_at" value="{{ old('grace_ends_at') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></div>
                    </div>
                    <p class="mt-3 text-sm text-gray-500">Trial and grace dates are optional. Use them only when the school should have temporary access.</p>
                </section>

                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Step 4: Payment Status</h3>
                    <div class="mt-4 grid gap-6 md:grid-cols-4">
                        <div><label class="block text-sm font-medium text-gray-700">Subscription Status</label><select name="status" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"><option value="trial">Trial</option><option value="active">Active</option><option value="grace">Grace Period</option><option value="expired">Expired</option><option value="cancelled">Cancelled</option><option value="superseded">Superseded</option></select></div>
                        <div><label class="block text-sm font-medium text-gray-700">Amount Due</label><input type="number" step="0.01" name="amount_due" value="{{ old('amount_due', 0) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Amount Paid</label><input type="number" step="0.01" name="amount_paid" value="{{ old('amount_paid', 0) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Payment Status</label><select name="payment_status" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"><option value="pending">Pending</option><option value="manual_pending">Manual Pending</option><option value="paid">Paid</option><option value="failed">Failed</option><option value="cancelled">Cancelled</option></select></div>
                    </div>
                    <p class="mt-3 text-sm text-gray-500">Active grants access immediately. Trial and grace period are temporary. Superseded marks an old subscription replaced by a newer one.</p>
                </section>

                @if ($errors->any())
                    <div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">Please fix the highlighted fields.</div>
                @endif

                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Step 5: Review and Assign</h3>
                    <p class="mt-2 text-sm text-gray-600">Confirm the selected school, plan, dates, amount due, amount paid, and payment status before assigning or changing a subscription.</p>
                    <div class="mt-5 flex justify-end gap-3">
                        <a href="{{ route('admin.school-subscriptions.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Cancel</a>
                        <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Assign Plan</button>
                    </div>
                </section>
            </form>
        </div>
    </div>
</x-app-layout>
