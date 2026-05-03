<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-900">Payments</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success')) <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div> @endif
            <div class="mb-6 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl bg-white p-5 shadow-sm"><h3 class="font-semibold">Paystack Auto Verification</h3><p class="mt-1 text-sm text-gray-500">Coming Soon</p></div>
                <div class="rounded-2xl bg-white p-5 shadow-sm"><h3 class="font-semibold">Flutterwave Auto Verification</h3><p class="mt-1 text-sm text-gray-500">Coming Soon</p></div>
                <div class="rounded-2xl bg-white p-5 shadow-sm"><h3 class="font-semibold">Parent Direct Result Payment</h3><p class="mt-1 text-sm text-gray-500">Coming Soon</p></div>
            </div>
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100">
                    <tbody class="divide-y divide-gray-100">
                        @forelse($payments as $payment)
                            <tr>
                                <td class="px-6 py-4"><div class="font-medium">{{ $payment->school->name ?? 'No school' }}</div><div class="text-sm text-gray-500">{{ class_basename($payment->payable_type) }} #{{ $payment->payable_id }}</div></td>
                                <td class="px-6 py-4 text-sm">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}<br><x-status-badge :status="$payment->status" /></td>
                                <td class="px-6 py-4 text-sm">{{ $payment->payment_method ?? 'N/A' }}<br>{{ $payment->payment_reference ?? 'No reference' }}</td>
                                <td class="px-6 py-4">
                                    @if($payment->status !== 'paid')
                                        <form method="POST" action="{{ route('admin.payments.confirm', $payment) }}" data-confirm="Confirm this manual payment?" data-loading-text="Confirming..." class="grid gap-2 md:grid-cols-5">
                                            @csrf
                                            <input type="number" step="0.01" name="amount" value="{{ $payment->amount }}" class="rounded-lg border-gray-300 text-sm">
                                            <input name="currency" value="{{ $payment->currency }}" class="rounded-lg border-gray-300 text-sm">
                                            <select name="payment_method" class="rounded-lg border-gray-300 text-sm"><option value="manual">Manual</option><option value="bank_transfer">Bank Transfer</option><option value="cash">Cash</option></select>
                                            <input name="payment_reference" value="{{ $payment->payment_reference }}" placeholder="Reference" class="rounded-lg border-gray-300 text-sm">
                                            <button class="rounded-lg bg-gray-900 px-3 py-2 text-sm text-white">Confirm</button>
                                        </form>
                                    @else
                                        <span class="text-sm text-gray-500">Confirmed by {{ $payment->confirmedBy->name ?? 'N/A' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td class="px-6 py-12 text-center text-sm text-gray-500">No payment transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $payments->links() }}</div>
        </div>
    </div>
</x-app-layout>
