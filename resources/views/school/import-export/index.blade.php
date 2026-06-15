<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Import / Export</h2>
            <p class="mt-1 text-sm text-gray-500">Controlled CSV tools for safe school-scoped data movement in {{ $school->name }}.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
            @endif
            @if (session('warning'))
                <x-ui.alert tone="warning">{{ session('warning') }}</x-ui.alert>
            @endif

            <x-ui.panel tone="warning">
                <p class="text-sm leading-6 text-text-secondary">
                    These tools export selected operational CSV data only. Full database backup/export remains in the backup system. Password import, payment gateway import, offline attendance import, PDF exports, Excel exports, and unrestricted database dumps are handled outside this screen.
                </p>
            </x-ui.panel>

            <section class="grid gap-4 lg:grid-cols-3">
                @if ($canManageStudentImports)
                    <x-ui.panel>
                        <h3 class="text-base font-semibold text-text-primary">Students</h3>
                        <p class="mt-1 text-sm text-text-secondary">Export safe student fields, download a template, or preview a CSV before importing.</p>

                        <form method="GET" action="{{ route('school.import-export.students.export') }}" class="mt-4 grid gap-3">
                            <select name="school_class_id" class="rounded-xl border-gray-300 text-sm">
                                <option value="">All classes</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }} {{ $class->section }}</option>
                                @endforeach
                            </select>
                            <select name="status" class="rounded-xl border-gray-300 text-sm">
                                <option value="">All statuses</option>
                                @foreach ($studentStatuses as $status)
                                    <option value="{{ $status }}">{{ str($status)->title() }}</option>
                                @endforeach
                            </select>
                            <input name="search" placeholder="Search name or admission number" class="rounded-xl border-gray-300 text-sm">
                            <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Export Students CSV</button>
                        </form>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('school.import-export.students.template') }}" class="ui-button-secondary">Download Template</a>
                        </div>
                    </x-ui.panel>
                @endif

                @if ($canExportAttendance)
                    <x-ui.panel>
                        <h3 class="text-base font-semibold text-text-primary">Attendance Summary</h3>
                        <p class="mt-1 text-sm text-text-secondary">Export class/date status counts. Notes and raw offline payloads are excluded.</p>

                        <form method="GET" action="{{ route('school.import-export.attendance.export') }}" class="mt-4 grid gap-3">
                            <input type="date" name="date" class="rounded-xl border-gray-300 text-sm" aria-label="Single attendance date">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <input type="date" name="date_from" class="rounded-xl border-gray-300 text-sm" aria-label="Attendance from date">
                                <input type="date" name="date_to" class="rounded-xl border-gray-300 text-sm" aria-label="Attendance to date">
                            </div>
                            <select name="school_class_id" class="rounded-xl border-gray-300 text-sm">
                                <option value="">All classes</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }} {{ $class->section }}</option>
                                @endforeach
                            </select>
                            <select name="status" class="rounded-xl border-gray-300 text-sm">
                                <option value="">All statuses</option>
                                @foreach ($attendanceStatuses as $status)
                                    <option value="{{ $status }}">{{ str($status)->title() }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Export Attendance CSV</button>
                        </form>
                    </x-ui.panel>
                @endif

                @if ($canExportFinance)
                    <x-ui.panel>
                        <h3 class="text-base font-semibold text-text-primary">Finance Summary</h3>
                        <p class="mt-1 text-sm text-text-secondary">Export invoice and payment summaries. References, notes, metadata, and secrets are excluded.</p>

                        <form method="GET" action="{{ route('school.import-export.finance.export') }}" class="mt-4 grid gap-3">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <input type="date" name="date_from" class="rounded-xl border-gray-300 text-sm" aria-label="Finance from date">
                                <input type="date" name="date_to" class="rounded-xl border-gray-300 text-sm" aria-label="Finance to date">
                            </div>
                            <select name="school_class_id" class="rounded-xl border-gray-300 text-sm">
                                <option value="">All classes</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }} {{ $class->section }}</option>
                                @endforeach
                            </select>
                            <select name="invoice_status" class="rounded-xl border-gray-300 text-sm">
                                <option value="">All invoice statuses</option>
                                @foreach ($invoiceStatuses as $status)
                                    <option value="{{ $status }}">{{ str($status)->replace('_', ' ')->title() }}</option>
                                @endforeach
                            </select>
                            <select name="payment_method" class="rounded-xl border-gray-300 text-sm">
                                <option value="">All payment methods</option>
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method }}">{{ str($method)->replace('_', ' ')->title() }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Export Finance CSV</button>
                        </form>
                    </x-ui.panel>
                @endif
            </section>

            @if ($canManageStudentImports)
                <section class="grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
                    <x-ui.panel>
                        <h3 class="text-base font-semibold text-text-primary">Student Import Preview</h3>
                        <p class="mt-1 text-sm text-text-secondary">Upload a CSV to validate rows before anything is written.</p>

                        <form method="POST" action="{{ route('school.import-export.students.preview') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                            @csrf
                            <input type="file" name="student_file" accept=".csv,.txt" class="block w-full rounded-xl border border-gray-300 p-3 text-sm">
                            @error('student_file')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Preview Student CSV</button>
                        </form>

                        <div class="mt-5 rounded-md border border-border-subtle bg-bg-primary p-3 text-xs leading-5 text-text-secondary">
                            Required columns: {{ implode(', ', \App\Services\ImportExport\SchoolImportExportService::STUDENT_IMPORT_REQUIRED_HEADERS) }}. Maximum rows per import: 200.
                        </div>
                    </x-ui.panel>

                    <x-ui.panel>
                        <h3 class="text-base font-semibold text-text-primary">Preview Results</h3>

                        @if ($studentImportPreview)
                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                <x-ui.stat-card label="Rows" :value="$studentImportPreview['row_count'] ?? 0" class="p-4" />
                                <x-ui.stat-card label="Valid" :value="$studentImportPreview['valid_count'] ?? 0" tone="success" class="p-4" />
                                <x-ui.stat-card label="Errors" :value="$studentImportPreview['error_count'] ?? 0" tone="warning" class="p-4" />
                            </div>

                            @if (($studentImportPreview['errors'] ?? []) !== [])
                                <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                                    <p class="font-semibold">Validation issues</p>
                                    <ul class="mt-2 list-disc space-y-1 ps-5">
                                        @foreach (array_slice($studentImportPreview['errors'], 0, 20) as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @elseif (($studentImportPreview['rows'] ?? []) !== [])
                                <div class="mt-4 overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Admission</th>
                                                <th class="px-3 py-2 text-left">Student</th>
                                                <th class="px-3 py-2 text-left">Class</th>
                                                <th class="px-3 py-2 text-left">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach (array_slice($studentImportPreview['rows'], 0, 10) as $row)
                                                <tr>
                                                    <td class="px-3 py-2 font-mono">{{ $row['admission_number'] }}</td>
                                                    <td class="px-3 py-2">{{ $row['first_name'] }} {{ $row['middle_name'] }} {{ $row['last_name'] }}</td>
                                                    <td class="px-3 py-2">{{ $row['class'] }}</td>
                                                    <td class="px-3 py-2">{{ str($row['status'])->title() }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($pendingStudentImport)
                                    <form method="POST" action="{{ route('school.import-export.students.import') }}" class="mt-4">
                                        @csrf
                                        <input type="hidden" name="token" value="{{ $pendingStudentImport['token'] }}">
                                        <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Confirm Student Import</button>
                                    </form>
                                @endif
                            @endif
                        @else
                            <p class="mt-4 rounded-md border border-border-subtle bg-bg-primary p-3 text-sm text-text-secondary">No student import preview is pending.</p>
                        @endif
                    </x-ui.panel>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
