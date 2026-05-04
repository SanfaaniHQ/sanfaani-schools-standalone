<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-900">Assign School Plan</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.school-subscriptions.store') }}" class="space-y-6 rounded-2xl bg-white p-6 shadow-sm">
                @csrf
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">School</label>
                        <select name="school_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="">Select school</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Plan</label>
                        <select name="subscription_plan_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="">Select plan</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(old('subscription_plan_id') == $plan->id)>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid gap-6 md:grid-cols-4">
                    <div><label class="block text-sm font-medium text-gray-700">Status</label><select name="status" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"><option value="trial">Trial</option><option value="active">Active</option><option value="grace">Grace</option><option value="expired">Expired</option><option value="cancelled">Cancelled</option></select></div>
                    <div><label class="block text-sm font-medium text-gray-700">Starts At</label><input type="date" name="starts_at" value="{{ old('starts_at') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700">Ends At</label><input type="date" name="ends_at" value="{{ old('ends_at') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700">Trial Ends</label><input type="date" name="trial_ends_at" value="{{ old('trial_ends_at') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></div>
                </div>
                <div class="grid gap-6 md:grid-cols-4">
                    <div><label class="block text-sm font-medium text-gray-700">Grace Ends</label><input type="date" name="grace_ends_at" value="{{ old('grace_ends_at') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700">Amount Due</label><input type="number" step="0.01" name="amount_due" value="{{ old('amount_due', 0) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700">Amount Paid</label><input type="number" step="0.01" name="amount_paid" value="{{ old('amount_paid', 0) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"></div>
                    <div><label class="block text-sm font-medium text-gray-700">Payment Status</label><select name="payment_status" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm"><option value="pending">Pending</option><option value="manual_pending">Manual Pending</option><option value="paid">Paid</option><option value="failed">Failed</option><option value="cancelled">Cancelled</option></select></div>
                </div>
                @if ($errors->any()) <div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">Please fix the highlighted fields.</div> @endif
                <div class="flex justify-end gap-3"><a href="{{ route('admin.school-subscriptions.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm">Cancel</a><button class="rounded-xl bg-gray-900 px-4 py-2 text-sm text-white">Assign Plan</button></div>
            </form>
        </div>
    </div>
</x-app-layout>
