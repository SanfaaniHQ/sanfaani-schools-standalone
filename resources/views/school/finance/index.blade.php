<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Fees & Finance</h2>
                <p class="mt-1 text-sm text-gray-500">Online fee setup, student invoices, manual payments, and balances for {{ $school->name }}.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.finance.reports') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Reports</a>
                <a href="{{ route('school.finance.audit') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Audit Review</a>
                <a href="{{ route('school.finance.fee-items.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Fee Items</a>
                <a href="{{ route('school.finance.assignments.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Assignments</a>
                <a href="{{ route('school.finance.invoices.index') }}" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Invoices</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
            @endif

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-ui.stat-card label="Fee Items" :value="$summary['fee_items']" :meta="$summary['active_fee_items'] . ' active'" />
                <x-ui.stat-card label="Assignments" :value="$summary['assignments']" :meta="$summary['active_assignments'] . ' active'" />
                <x-ui.stat-card label="Invoices" :value="$summary['invoices']" :meta="$summary['outstanding_invoices'] . ' outstanding'" tone="info" />
                <x-ui.stat-card label="Balance" :value="'NGN ' . number_format($summary['total_balance'], 2)" :meta="'Paid: NGN ' . number_format($summary['total_paid'], 2)" tone="warning" />
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <x-ui.panel>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-text-primary">Recent Invoices</h3>
                            <p class="mt-1 text-sm text-text-secondary">Student fee bills generated in this school scope.</p>
                        </div>
                        <a href="{{ route('school.finance.invoices.index') }}" class="ui-button-secondary">Open</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($recentInvoices as $invoice)
                            <a href="{{ route('school.finance.invoices.show', $invoice) }}" class="block rounded-md border border-border-subtle bg-bg-primary p-3 text-sm hover:bg-bg-tertiary">
                                <span class="font-semibold text-text-primary">{{ $invoice->invoice_number }}</span>
                                <span class="ms-2 text-text-secondary">{{ $invoice->student?->fullName() ?? 'Student' }}</span>
                                <span class="float-right font-mono text-brand-primary">NGN {{ number_format($invoice->balance_amount, 2) }}</span>
                            </a>
                        @empty
                            <p class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm text-text-secondary">No student fee invoices have been generated yet.</p>
                        @endforelse
                    </div>
                </x-ui.panel>

                <x-ui.panel>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-text-primary">Recent Payments</h3>
                            <p class="mt-1 text-sm text-text-secondary">Manual payments recorded by authorized finance users.</p>
                        </div>
                        <a href="{{ route('school.finance.invoices.index') }}" class="ui-button-secondary">Record</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($recentPayments as $payment)
                            <div class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm">
                                <span class="font-semibold text-text-primary">NGN {{ number_format($payment->amount, 2) }}</span>
                                <span class="ms-2 text-text-secondary">{{ $payment->student?->fullName() ?? 'Student' }}</span>
                                <span class="float-right text-text-tertiary">{{ $payment->payment_date?->format('d M Y') }}</span>
                            </div>
                        @empty
                            <p class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm text-text-secondary">No fee payments have been recorded yet.</p>
                        @endforelse
                    </div>
                </x-ui.panel>
            </section>

            <x-ui.panel tone="info">
                <p class="text-sm leading-6 text-text-secondary">
                    This finance module is online only. Finance reports, finance CSV export, and audit review are available. Payment gateway automation, parent/student finance portals, and offline fee capture require separate setup.
                </p>
            </x-ui.panel>
        </div>
    </div>
</x-app-layout>
