<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Student Finance History</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $student->fullName() }} - {{ $student->admission_number }}</p>
            </div>
            <a href="{{ route('school.finance.invoices.index', ['search' => $student->admission_number]) }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">View Invoices</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-3">
                <x-ui.stat-card label="Total Billed" :value="'NGN ' . number_format($summary['total_billed'], 2)" />
                <x-ui.stat-card label="Total Paid" :value="'NGN ' . number_format($summary['total_paid'], 2)" tone="success" />
                <x-ui.stat-card label="Outstanding" :value="'NGN ' . number_format($summary['total_balance'], 2)" tone="warning" />
            </section>

            <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Invoices and Payments</h3>
                    <p class="mt-1 text-sm text-gray-500">Authorized school finance history for this student only.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Invoice</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Context</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Amounts</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($invoices as $invoice)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $invoice->invoice_number }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $invoice->schoolClass?->name ?? 'No class' }}<br>{{ $invoice->academicSession?->name ?? 'Any session' }} / {{ $invoice->term?->name ?? 'Any term' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Billed NGN {{ number_format($invoice->total_amount, 2) }}<br>Paid NGN {{ number_format($invoice->paid_amount, 2) }}<br>Balance NGN {{ number_format($invoice->balance_amount, 2) }}</td>
                                    <td class="px-6 py-4"><x-status-badge :status="$invoice->status" /></td>
                                    <td class="px-6 py-4 text-right"><a href="{{ route('school.finance.invoices.show', $invoice) }}" class="text-sm font-medium text-gray-900 hover:text-gray-600">Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No fee history found for this student.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 px-6 py-4">{{ $invoices->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
