<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">School / Communication / Bulk</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Bulk Communication</h2>
                <p class="mt-1 text-sm text-gray-500">Send chunked communication by class, arm, session, result status, and staff cohort.</p>
            </div>
            <a href="{{ route('school.communications.history') }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">History</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('school.communications.bulk.send') }}" class="space-y-5 rounded-2xl bg-white p-6 shadow-sm">
                @csrf

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Audience</label>
                        <select name="audience" class="mt-1 block w-full rounded-xl border-gray-300">
                            <option value="class" @selected(old('audience') === 'class')>Class</option>
                            <option value="arm" @selected(old('audience') === 'arm')>Arm</option>
                            <option value="session" @selected(old('audience') === 'session')>Session</option>
                            <option value="selected_students" @selected(old('audience') === 'selected_students')>Selected Students</option>
                            @if ($canMessageStaff)
                                <option value="teachers" @selected(old('audience') === 'teachers')>Teachers</option>
                                <option value="result_officers" @selected(old('audience') === 'result_officers')>Result Officers</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Class</label>
                        <select name="school_class_id" class="mt-1 block w-full rounded-xl border-gray-300">
                            <option value="">Any class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected((string) old('school_class_id') === (string) $class->id)>{{ $class->name }} {{ $class->section }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Arm</label>
                        <select name="arm_section" class="mt-1 block w-full rounded-xl border-gray-300">
                            <option value="">Any arm</option>
                            @foreach ($arms as $arm)
                                <option value="{{ $arm }}" @selected(old('arm_section') === $arm)>{{ $arm }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Session</label>
                        <select name="academic_session_id" class="mt-1 block w-full rounded-xl border-gray-300">
                            <option value="">Any session</option>
                            @foreach ($sessions as $session)
                                <option value="{{ $session->id }}" @selected((string) old('academic_session_id') === (string) $session->id)>{{ $session->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Term</label>
                        <select name="term_id" class="mt-1 block w-full rounded-xl border-gray-300">
                            <option value="">Any term</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}" @selected((string) old('term_id') === (string) $term->id)>{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Enrollment Status</label>
                        <select name="enrollment_status" class="mt-1 block w-full rounded-xl border-gray-300">
                            <option value="">Any enrollment</option>
                            @foreach (['active' => 'Active', 'repeating' => 'Repeating', 'completed' => 'Completed', 'graduated' => 'Graduated', 'transferred' => 'Transferred', 'withdrawn' => 'Withdrawn'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('enrollment_status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Student Status</label>
                        <select name="student_status" class="mt-1 block w-full rounded-xl border-gray-300">
                            <option value="">Any student</option>
                            @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'graduated' => 'Graduated', 'transferred' => 'Transferred', 'withdrawn' => 'Withdrawn'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('student_status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Published Results</label>
                        <select name="published_result_status" class="mt-1 block w-full rounded-xl border-gray-300">
                            <option value="">Any result state</option>
                            <option value="published" @selected(old('published_result_status') === 'published')>Published only</option>
                            <option value="not_published" @selected(old('published_result_status') === 'not_published')>Not published only</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Staff Status</label>
                        <select name="user_status" class="mt-1 block w-full rounded-xl border-gray-300">
                            <option value="active" @selected(old('user_status', 'active') === 'active')>Active users</option>
                            <option value="inactive" @selected(old('user_status') === 'inactive')>Inactive users</option>
                            <option value="any" @selected(old('user_status') === 'any')>Any users</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Chunk Size</label>
                        <select name="chunk_size" class="mt-1 block w-full rounded-xl border-gray-300">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected((int) old('chunk_size', 25) === $size)>{{ $size }} recipients</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Selected Students</label>
                    <select name="student_ids[]" multiple size="6" class="mt-1 block w-full rounded-xl border-gray-300">
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected(in_array((string) $student->id, old('student_ids', []), true))>
                                {{ $student->fullName() }} - {{ $student->admission_number }} - {{ $student->guardian_email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Channels</label>
                        @php($selectedChannels = old('channels', ['email']))
                        <div class="mt-2 flex flex-wrap gap-3">
                            <label class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                <input type="checkbox" name="channels[]" value="email" class="rounded border-gray-300" @checked(in_array('email', $selectedChannels, true))>
                                Email
                            </label>
                            <label class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                <input type="checkbox" name="channels[]" value="sms" class="rounded border-gray-300" @checked(in_array('sms', $selectedChannels, true))>
                                SMS-ready
                            </label>
                            <label class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700">
                                <input type="checkbox" name="channels[]" value="in_app" class="rounded border-gray-300" @checked(in_array('in_app', $selectedChannels, true))>
                                In-app-ready
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select name="type" class="mt-1 block w-full rounded-xl border-gray-300">
                            <option value="result_notification" @selected(old('type') === 'result_notification')>Result Notification</option>
                            <option value="report_card" @selected(old('type') === 'report_card')>Report Card</option>
                            <option value="scratch_card" @selected(old('type') === 'scratch_card')>Scratch Card</option>
                            <option value="payment_reminder" @selected(old('type') === 'payment_reminder')>Payment Reminder</option>
                            <option value="attendance_warning" @selected(old('type') === 'attendance_warning')>Attendance Warning</option>
                            <option value="custom_message" @selected(old('type') === 'custom_message')>Custom Message</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Subject</label>
                    <input name="subject" value="{{ old('subject') }}" class="mt-1 block w-full rounded-xl border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea name="message" rows="7" class="mt-1 block w-full rounded-xl border-gray-300">{{ old('message') }}</textarea>
                </div>
                <div class="flex justify-end">
                    <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Create And Process Batch</button>
                </div>
            </form>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Recent Bulk Batches</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Batch</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Audience</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Counts</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($recentBatches as $batch)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    <div class="font-medium text-gray-900">{{ $batch->subject }}</div>
                                    <div class="text-xs text-gray-500">{{ $batch->batch_uuid }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ str_replace('_', ' ', $batch->audience) }}</td>
                                <td class="px-4 py-3 text-sm"><x-status-badge :status="$batch->status" /></td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    Sent {{ $batch->sent_count }} / Failed {{ $batch->failed_count }} / Skipped {{ $batch->skipped_count }} / Pending {{ $batch->pendingRecipientCount() }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if ($batch->isProcessable() && $batch->pendingRecipientCount() > 0)
                                            <form method="POST" action="{{ route('school.communications.bulk.process', $batch) }}">
                                                @csrf
                                                <button class="text-sm font-medium text-indigo-700 hover:text-indigo-500">Continue</button>
                                            </form>
                                        @endif
                                        @if ($batch->isRetryable())
                                            <form method="POST" action="{{ route('school.communications.bulk.retry-failed', $batch) }}">
                                                @csrf
                                                <button class="text-sm font-medium text-amber-700 hover:text-amber-500">Retry Failed</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No bulk batches yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
