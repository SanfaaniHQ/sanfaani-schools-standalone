<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Student Fee Invoices</h2>
                <p class="mt-1 text-sm text-gray-500">Generate bills from active fee assignments and record manual payments.</p>
            </div>
            <a href="{{ route('school.finance.assignments.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Manage Assignments</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
            @endif

            <x-ui.panel>
                <h3 class="text-base font-semibold text-text-primary">Generate Invoices</h3>
                <p class="mt-1 text-sm text-text-secondary">Choose either a class or one student. Re-running generation preserves matching existing invoices.</p>
                <form method="POST" action="{{ route('school.finance.invoices.generate') }}" class="mt-4 grid gap-4 lg:grid-cols-6">
                    @csrf
                    <select name="school_class_id" class="rounded-xl border-gray-300 text-sm lg:col-span-2">
                        <option value="">Class target</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->name }} {{ $class->section }}</option>
                        @endforeach
                    </select>
                    <select name="student_id" class="rounded-xl border-gray-300 text-sm lg:col-span-2">
                        <option value="">Or one student</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->fullName() }} - {{ $student->admission_number }}</option>
                        @endforeach
                    </select>
                    <select name="academic_session_id" class="rounded-xl border-gray-300 text-sm">
                        <option value="">Any session</option>
                        @foreach ($academicSessions as $session)
                            <option value="{{ $session->id }}" @selected(old('academic_session_id') == $session->id)>{{ $session->name }}</option>
                        @endforeach
                    </select>
                    <select name="term_id" class="rounded-xl border-gray-300 text-sm">
                        <option value="">Any term</option>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" @selected(old('term_id') == $term->id)>{{ $term->name }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="due_date" value="{{ old('due_date') }}" class="rounded-xl border-gray-300 text-sm lg:col-span-2">
                    <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 lg:col-span-2">Generate</button>
                    @error('fee_assignment')<p class="text-xs text-red-600 lg:col-span-6">{{ $message }}</p>@enderror
                </form>
            </x-ui.panel>

            <form method="GET" action="{{ route('school.finance.invoices.index') }}" class="grid gap-3 rounded-2xl bg-white p-4 shadow-sm md:grid-cols-5">
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search invoice or student" class="rounded-xl border-gray-300 text-sm md:col-span-2">
                <select name="status" class="rounded-xl border-gray-300 text-sm">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
                <select name="school_class_id" class="rounded-xl border-gray-300 text-sm">
                    <option value="">All classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected(($filters['school_class_id'] ?? '') == $class->id)>{{ $class->name }} {{ $class->section }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Filter</button>
                    <a href="{{ route('school.finance.invoices.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Clear</a>
                </div>
            </form>

            <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Invoices</h3>
                    <p class="mt-1 text-sm text-gray-500">Totals are recalculated after each payment.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Invoice</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Student</th>
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
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $invoice->student?->fullName() ?? 'Student' }}<br><span class="text-xs text-gray-400">{{ $invoice->student?->admission_number }}</span></td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $invoice->schoolClass?->name ?? 'No class' }}<br>{{ $invoice->academicSession?->name ?? 'Any session' }} / {{ $invoice->term?->name ?? 'Any term' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Billed NGN {{ number_format($invoice->total_amount, 2) }}<br>Balance NGN {{ number_format($invoice->balance_amount, 2) }}</td>
                                    <td class="px-6 py-4"><x-status-badge :status="$invoice->status" /></td>
                                    <td class="px-6 py-4 text-right"><a href="{{ route('school.finance.invoices.show', $invoice) }}" class="text-sm font-medium text-gray-900 hover:text-gray-600">Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No student fee invoices found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 px-6 py-4">{{ $invoices->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
