<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Invoice {{ $invoice->invoice_number }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $invoice->student?->fullName() ?? 'Student' }} - {{ $invoice->student?->admission_number }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.finance.students.show', $invoice->student) }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Student History</a>
                <a href="{{ route('school.finance.invoices.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">All Invoices</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1.15fr_0.85fr] lg:px-8">
            <div class="space-y-6">
                @if (session('success'))
                    <div class="rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
                @endif

                <section class="grid gap-4 sm:grid-cols-4">
                    <x-ui.stat-card label="Billed" :value="'NGN ' . number_format($invoice->total_amount, 2)" />
                    <x-ui.stat-card label="Discount" :value="'NGN ' . number_format($invoice->discount_amount, 2)" />
                    <x-ui.stat-card label="Paid" :value="'NGN ' . number_format($invoice->paid_amount, 2)" tone="success" />
                    <x-ui.stat-card label="Balance" :value="'NGN ' . number_format($invoice->balance_amount, 2)" tone="warning" />
                </section>

                <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">Invoice Items</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Discount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($invoice->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $item->description }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">NGN {{ number_format($item->amount, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">NGN {{ number_format($item->discount_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-gray-900">Payment History</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Reference</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($invoice->payments as $payment)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $payment->payment_date?->format('d M Y') }}</td>
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">NGN {{ number_format($payment->amount, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ str($payment->method)->replace('_', ' ')->title() }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ $payment->reference ?: 'No reference' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No payments recorded yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <x-ui.panel>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-text-primary">Record Payment</h3>
                        <p class="mt-1 text-sm text-text-secondary">Manual receipt entry only. Payment gateway automation is not part of this stage.</p>
                    </div>
                    <x-status-badge :status="$invoice->status" />
                </div>

                @if ($invoice->balance_amount > 0 && $invoice->status !== \App\Models\StudentFeeInvoice::STATUS_CANCELLED)
                    <form method="POST" action="{{ route('school.finance.invoices.payments.store', $invoice) }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-text-primary">Amount</label>
                            <input type="number" step="0.01" min="0.01" max="{{ $invoice->balance_amount }}" name="amount" value="{{ old('amount') }}" class="mt-1 w-full rounded-xl border-gray-300 text-sm" required>
                            @error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-primary">Payment Date</label>
                            <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" class="mt-1 w-full rounded-xl border-gray-300 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-primary">Method</label>
                            <select name="method" class="mt-1 w-full rounded-xl border-gray-300 text-sm" required>
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method }}" @selected(old('method', 'manual') === $method)>{{ str($method)->replace('_', ' ')->title() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-primary">Receipt / Reference</label>
                            <input name="reference" value="{{ old('reference') }}" class="mt-1 w-full rounded-xl border-gray-300 text-sm" placeholder="Optional manual receipt number">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-primary">Note</label>
                            <textarea name="note" rows="3" class="mt-1 w-full rounded-xl border-gray-300 text-sm">{{ old('note') }}</textarea>
                        </div>
                        <button class="w-full rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Record Payment</button>
                    </form>
                @else
                    <p class="mt-4 rounded-md border border-border-subtle bg-bg-primary p-3 text-sm text-text-secondary">This invoice has no outstanding balance.</p>
                @endif
            </x-ui.panel>
        </div>
    </div>
</x-app-layout>
