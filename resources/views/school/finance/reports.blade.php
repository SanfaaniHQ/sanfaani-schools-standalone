<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Finance Reports</h2>
                <p class="mt-1 text-sm text-gray-500">School-scoped fee collection, balances, payments, and invoice status summaries for {{ $school->name }}.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.finance.audit') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Audit Review</a>
                <a href="{{ route('school.finance.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Finance Overview</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('school.finance.reports') }}" class="grid gap-3 rounded-2xl bg-white p-4 shadow-sm md:grid-cols-4">
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    From
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm">
                </label>
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    To
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 block w-full rounded-xl border-gray-300 text-sm shadow-sm">
                </label>
                <select name="academic_session_id" class="rounded-xl border-gray-300 text-sm shadow-sm">
                    <option value="">All sessions</option>
                    @foreach ($academicSessions as $academicSession)
                        <option value="{{ $academicSession->id }}" @selected((int) ($filters['academic_session_id'] ?? 0) === (int) $academicSession->id)>{{ $academicSession->name }}</option>
                    @endforeach
                </select>
                <select name="term_id" class="rounded-xl border-gray-300 text-sm shadow-sm">
                    <option value="">All terms</option>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" @selected((int) ($filters['term_id'] ?? 0) === (int) $term->id)>
                            {{ $term->name }} @if ($term->academicSession) - {{ $term->academicSession->name }} @endif
                        </option>
                    @endforeach
                </select>
                <select name="school_class_id" class="rounded-xl border-gray-300 text-sm shadow-sm">
                    <option value="">All classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected((int) ($filters['school_class_id'] ?? 0) === (int) $class->id)>{{ $class->name }} {{ $class->section }}</option>
                    @endforeach
                </select>
                <select name="invoice_status" class="rounded-xl border-gray-300 text-sm shadow-sm">
                    <option value="">All invoice statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['invoice_status'] ?? '') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
                <select name="payment_method" class="rounded-xl border-gray-300 text-sm shadow-sm">
                    <option value="">All payment methods</option>
                    @foreach ($paymentMethods as $method)
                        <option value="{{ $method }}" @selected(($filters['payment_method'] ?? '') === $method)>{{ str($method)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
                <select name="student_id" class="rounded-xl border-gray-300 text-sm shadow-sm">
                    <option value="">All students</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected((int) ($filters['student_id'] ?? 0) === (int) $student->id)>
                            {{ $student->fullName() }} @if ($student->admission_number) ({{ $student->admission_number }}) @endif
                        </option>
                    @endforeach
                </select>
                <div class="flex flex-wrap gap-2 md:col-span-4">
                    <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Run report</button>
                    <a href="{{ route('school.finance.reports') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Clear</a>
                </div>
                <p class="text-xs text-gray-500 md:col-span-4">
                    Date filters apply to invoice issue dates for billed and outstanding summaries, and to payment dates for payment summaries. Import/export remains Stage 12.
                </p>
            </form>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-ui.stat-card label="Total Invoiced" :value="'NGN ' . number_format($report['summary']['total_invoiced'], 2)" :meta="$report['summary']['invoices'] . ' invoice(s)'" />
                <x-ui.stat-card label="Total Paid" :value="'NGN ' . number_format($report['summary']['total_paid'], 2)" :meta="$report['summary']['payments'] . ' payment(s)'" tone="success" />
                <x-ui.stat-card label="Outstanding" :value="'NGN ' . number_format($report['summary']['total_outstanding'], 2)" meta="Open student fee balances" tone="warning" />
                <x-ui.stat-card label="Discount / Waiver" :value="'NGN ' . number_format($report['summary']['total_discount'], 2)" meta="Supported invoice item discounts" tone="info" />
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <x-ui.panel>
                    <h3 class="text-base font-semibold text-text-primary">Invoice Status Breakdown</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Status</th>
                                    <th class="px-4 py-3 text-right">Invoices</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($report['invoice_status_counts'] as $status => $count)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-700">{{ str($status)->replace('_', ' ')->title() }}</td>
                                        <td class="px-4 py-3 text-right font-mono text-gray-900">{{ $count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.panel>

                <x-ui.panel>
                    <h3 class="text-base font-semibold text-text-primary">Payment Method Summary</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Method</th>
                                    <th class="px-4 py-3 text-right">Payments</th>
                                    <th class="px-4 py-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($report['payment_methods'] as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-700">{{ str($row['method'])->replace('_', ' ')->title() }}</td>
                                        <td class="px-4 py-3 text-right font-mono text-gray-900">{{ $row['payments'] }}</td>
                                        <td class="px-4 py-3 text-right font-mono text-gray-900">NGN {{ number_format($row['total'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.panel>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <x-ui.panel>
                    <h3 class="text-base font-semibold text-text-primary">Outstanding By Class</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($report['outstanding_by_class'] as $row)
                            <div class="flex items-center justify-between gap-3 rounded-md border border-border-subtle bg-bg-primary p-3 text-sm">
                                <span>
                                    <span class="block font-semibold text-text-primary">{{ $row['class'] }}</span>
                                    <span class="text-xs text-text-secondary">{{ $row['invoices'] }} invoice(s)</span>
                                </span>
                                <span class="font-mono font-semibold text-brand-primary">NGN {{ number_format($row['balance'], 2) }}</span>
                            </div>
                        @empty
                            <p class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm text-text-secondary">No outstanding class balances match these filters.</p>
                        @endforelse
                    </div>
                </x-ui.panel>

                <x-ui.panel>
                    <h3 class="text-base font-semibold text-text-primary">Payment Totals By Date</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($report['payments_by_date'] as $row)
                            <div class="flex items-center justify-between gap-3 rounded-md border border-border-subtle bg-bg-primary p-3 text-sm">
                                <span class="font-semibold text-text-primary">{{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('d M Y') : 'No date' }}</span>
                                <span class="text-text-secondary">{{ $row['payments'] }} payment(s)</span>
                                <span class="font-mono font-semibold text-brand-primary">NGN {{ number_format($row['total'], 2) }}</span>
                            </div>
                        @empty
                            <p class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm text-text-secondary">No payments match these filters.</p>
                        @endforelse
                    </div>
                </x-ui.panel>
            </section>

            <x-ui.panel>
                <h3 class="text-base font-semibold text-text-primary">Student Balances</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Student</th>
                                <th class="px-4 py-3 text-left">Class</th>
                                <th class="px-4 py-3 text-right">Billed</th>
                                <th class="px-4 py-3 text-right">Paid</th>
                                <th class="px-4 py-3 text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($report['student_balances'] as $row)
                                <tr>
                                    <td class="px-4 py-3 text-gray-700">
                                        <span class="font-semibold text-gray-900">{{ $row['student'] }}</span>
                                        @if ($row['admission_number'])
                                            <span class="block text-xs text-gray-500">{{ $row['admission_number'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row['class'] }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-gray-900">NGN {{ number_format($row['total_invoiced'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-gray-900">NGN {{ number_format($row['total_paid'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-semibold text-brand-primary">NGN {{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No student balances match these filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.panel>

            <x-ui.panel>
                <h3 class="text-base font-semibold text-text-primary">Class / Session / Term Summaries</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Context</th>
                                <th class="px-4 py-3 text-right">Invoices</th>
                                <th class="px-4 py-3 text-right">Billed</th>
                                <th class="px-4 py-3 text-right">Paid</th>
                                <th class="px-4 py-3 text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($report['class_session_term_summaries'] as $row)
                                <tr>
                                    <td class="px-4 py-3 text-gray-700">
                                        <span class="font-semibold text-gray-900">{{ $row['class'] }}</span>
                                        <span class="block text-xs text-gray-500">{{ $row['session'] }} / {{ $row['term'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-gray-900">{{ $row['invoices'] }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-gray-900">NGN {{ number_format($row['total_invoiced'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-gray-900">NGN {{ number_format($row['total_paid'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-semibold text-brand-primary">NGN {{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No class/session/term summaries match these filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.panel>

            <section class="grid gap-4 lg:grid-cols-2">
                <x-ui.panel>
                    <h3 class="text-base font-semibold text-text-primary">Recent Payments</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($report['recent_payments'] as $payment)
                            <div class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-semibold text-text-primary">NGN {{ number_format($payment->amount, 2) }}</span>
                                    <span class="text-text-tertiary">{{ $payment->payment_date?->format('d M Y') }}</span>
                                </div>
                                <p class="mt-1 text-text-secondary">
                                    {{ $payment->student?->fullName() ?? 'Student' }} - {{ $payment->invoice?->invoice_number ?? 'Invoice' }} - {{ str($payment->method)->replace('_', ' ')->title() }}
                                </p>
                            </div>
                        @empty
                            <p class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm text-text-secondary">No recent payments match these filters.</p>
                        @endforelse
                    </div>
                </x-ui.panel>

                <x-ui.panel>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-text-primary">Overdue Invoices</h3>
                            <p class="mt-1 text-sm text-text-secondary">{{ $report['overdue']['invoices'] }} overdue invoice(s), NGN {{ number_format($report['overdue']['balance'], 2) }} outstanding.</p>
                        </div>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($report['overdue']['items'] as $invoice)
                            <a href="{{ route('school.finance.invoices.show', $invoice) }}" class="block rounded-md border border-border-subtle bg-bg-primary p-3 text-sm hover:bg-bg-tertiary">
                                <span class="font-semibold text-text-primary">{{ $invoice->invoice_number }}</span>
                                <span class="ms-2 text-text-secondary">{{ $invoice->student?->fullName() ?? 'Student' }}</span>
                                <span class="float-right font-mono text-brand-primary">NGN {{ number_format($invoice->balance_amount, 2) }}</span>
                                <span class="mt-1 block text-xs text-text-tertiary">Due {{ $invoice->due_date?->format('d M Y') }}</span>
                            </a>
                        @empty
                            <p class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm text-text-secondary">No overdue invoices match these filters.</p>
                        @endforelse
                    </div>
                </x-ui.panel>
            </section>

            <x-ui.panel tone="info">
                <p class="text-sm leading-6 text-text-secondary">
                    Reports are view-only and school-scoped. Import/export is still deferred to Stage 12; online payment gateway automation, offline fee capture, parent/student finance portals, and double-entry accounting are not implemented here.
                </p>
            </x-ui.panel>
        </div>
    </div>
</x-app-layout>
