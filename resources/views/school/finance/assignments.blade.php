<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Fee Assignments</h2>
                <p class="mt-1 text-sm text-gray-500">Assign fee items to a class, session, term, selected student, or school-wide context.</p>
            </div>
            <a href="{{ route('school.finance.invoices.index') }}" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Generate Invoices</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
            <x-ui.panel>
                <h3 class="text-base font-semibold text-text-primary">Create Assignment</h3>
                <form method="POST" action="{{ route('school.finance.assignments.store') }}" class="mt-4 grid gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-text-primary">Fee Item</label>
                        <select name="fee_item_id" class="mt-1 w-full rounded-xl border-gray-300 text-sm" required>
                            <option value="">Choose fee item</option>
                            @foreach ($feeItems as $item)
                                <option value="{{ $item->id }}" @selected(old('fee_item_id') == $item->id)>{{ $item->name }} - NGN {{ number_format($item->default_amount, 2) }}</option>
                            @endforeach
                        </select>
                        @error('fee_item_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-text-primary">Session</label>
                            <select name="academic_session_id" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                                <option value="">Any session</option>
                                @foreach ($academicSessions as $session)
                                    <option value="{{ $session->id }}" @selected(old('academic_session_id') == $session->id)>{{ $session->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-primary">Term</label>
                            <select name="term_id" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                                <option value="">Any term</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected(old('term_id') == $term->id)>{{ $term->name }} @if($term->academicSession) ({{ $term->academicSession->name }}) @endif</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-text-primary">Class</label>
                            <select name="school_class_id" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                                <option value="">School-wide or student-specific</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>{{ $class->name }} {{ $class->section }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-primary">Student</label>
                            <select name="student_id" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                                <option value="">Class/school assignment</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->fullName() }} - {{ $student->admission_number }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-text-primary">Amount</label>
                            <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" class="mt-1 w-full rounded-xl border-gray-300 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-primary">Due Date</label>
                            <input type="date" name="due_date" value="{{ old('due_date') }}" class="mt-1 w-full rounded-xl border-gray-300 text-sm">
                        </div>
                    </div>
                    @error('fee_assignment')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    <label class="flex items-center gap-2 text-sm text-text-secondary">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300">
                        Active
                    </label>
                    <button class="w-full rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Create Assignment</button>
                </form>
            </x-ui.panel>

            <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Current Assignments</h3>
                    <p class="mt-1 text-sm text-gray-500">Assignments feed invoice generation; deleting or cleanup actions are intentionally not part of this stage.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Fee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Target</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Context</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($assignments as $assignment)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $assignment->feeItem?->name ?? 'Fee item' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        @if ($assignment->student)
                                            {{ $assignment->student->fullName() }}
                                        @elseif ($assignment->schoolClass)
                                            {{ $assignment->schoolClass->name }} {{ $assignment->schoolClass->section }}
                                        @else
                                            School-wide
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $assignment->academicSession?->name ?? 'Any session' }} / {{ $assignment->term?->name ?? 'Any term' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">NGN {{ number_format($assignment->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">No fee assignments have been created yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 px-6 py-4">{{ $assignments->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
