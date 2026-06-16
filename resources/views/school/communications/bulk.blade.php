<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">School / Communication</p>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ __('ui.bulk_communication') }}</h2>
                <p class="mt-1 text-sm text-text-secondary">Prepare school messages for guardians and staff using the existing email delivery flow.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.communications.index') }}" class="ui-button-secondary">{{ __('ui.communication_center') }}</a>
                <a href="{{ route('school.communications.logs') }}" class="ui-button-secondary">{{ __('ui.notification_logs') }}</a>
            </div>
        </div>
    </x-slot>

    @php
        $selectedChannels = old('channels', ['email']);
        $selectedStudents = collect(old('student_ids', []))->map(fn ($id) => (string) $id)->all();
        $recentBatchTone = [
            'completed' => 'success',
            'completed_with_failures' => 'warning',
            'failed' => 'danger',
            'paused' => 'warning',
            'processing' => 'info',
            'pending' => 'outline',
        ];
    @endphp

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success" :body="session('success')" />
        @endif

        @if (session('warning'))
            <x-ui.alert tone="warning" :body="session('warning')" />
        @endif

        @if (session('error'))
            <x-ui.alert tone="danger" :body="session('error')" />
        @endif

        @if ($errors->any())
            <x-ui.alert tone="danger" body="{{ $errors->first() }}" />
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-ui.stat-card label="Guardian Contacts" :value="$recipientSummary['visible_students']" meta="Selectable student email records" tone="success" />
            <x-ui.stat-card label="Classes" :value="$recipientSummary['classes']" meta="Available class groups" />
            <x-ui.stat-card label="Arms" :value="$recipientSummary['arms']" meta="Available sections" />
            <x-ui.stat-card label="Teachers" :value="$recipientSummary['teacher_contacts']" meta="Active email contacts" tone="info" />
            <x-ui.stat-card label="Result Officers" :value="$recipientSummary['result_officer_contacts']" meta="Active email contacts" tone="info" />
        </section>

        @if (($recipientSummary['visible_students'] + $recipientSummary['teacher_contacts'] + $recipientSummary['result_officer_contacts']) === 0)
            <x-ui.alert tone="warning" body="{{ __('ui.bulk_communication_no_recipients') }}" />
        @endif

        <x-ui.panel title="Compose Message" description="Choose the audience, confirm the channel, and review the preview before creating a batch.">
            <form
                method="POST"
                action="{{ route('school.communications.bulk.send') }}"
                class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]"
                data-loading-text="{{ __('ui.sending_message') }}"
                x-data="{
                    audience: @js(old('audience', 'class')),
                    subject: @js(old('subject', '')),
                    message: @js(old('message', '')),
                    type: @js(old('type', 'custom_message')),
                    applyTemplate(event) {
                        const selected = event.target.selectedOptions[0];
                        if (! selected) return;
                        this.subject = selected.dataset.subject || this.subject;
                        this.message = selected.dataset.body || this.message;
                    }
                }"
            >
                @csrf

                <div class="space-y-6">
                    <section class="space-y-4">
                        <div>
                            <h3 class="text-sm font-semibold text-text-primary">Audience</h3>
                            <p class="mt-1 text-sm text-text-secondary">Select who should receive this batch.</p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label for="audience" class="block text-sm font-medium text-text-primary">Audience group</label>
                                <select id="audience" name="audience" x-model="audience" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="class" @selected(old('audience') === 'class')>Class</option>
                                    <option value="arm" @selected(old('audience') === 'arm')>Arm</option>
                                    <option value="session" @selected(old('audience') === 'session')>Session</option>
                                    <option value="selected_students" @selected(old('audience') === 'selected_students')>Selected Students</option>
                                    @if ($canMessageStaff)
                                        <option value="teachers" @selected(old('audience') === 'teachers')>Teachers</option>
                                        <option value="result_officers" @selected(old('audience') === 'result_officers')>Result Officers</option>
                                    @endif
                                </select>
                                @error('audience') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="school_class_id" class="block text-sm font-medium text-text-primary">Class</label>
                                <select id="school_class_id" name="school_class_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="">Any class</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}" @selected((string) old('school_class_id') === (string) $class->id)>{{ $class->name }} {{ $class->section }}</option>
                                    @endforeach
                                </select>
                                @error('school_class_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="arm_section" class="block text-sm font-medium text-text-primary">Arm</label>
                                <select id="arm_section" name="arm_section" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="">Any arm</option>
                                    @foreach ($arms as $arm)
                                        <option value="{{ $arm }}" @selected(old('arm_section') === $arm)>{{ $arm }}</option>
                                    @endforeach
                                </select>
                                @error('arm_section') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-4">
                            <div>
                                <label for="academic_session_id" class="block text-sm font-medium text-text-primary">Session</label>
                                <select id="academic_session_id" name="academic_session_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="">Any session</option>
                                    @foreach ($sessions as $session)
                                        <option value="{{ $session->id }}" @selected((string) old('academic_session_id') === (string) $session->id)>{{ $session->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="term_id" class="block text-sm font-medium text-text-primary">Term</label>
                                <select id="term_id" name="term_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="">Any term</option>
                                    @foreach ($terms as $term)
                                        <option value="{{ $term->id }}" @selected((string) old('term_id') === (string) $term->id)>{{ $term->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="enrollment_status" class="block text-sm font-medium text-text-primary">Enrollment status</label>
                                <select id="enrollment_status" name="enrollment_status" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="">Any enrollment</option>
                                    @foreach (['active' => 'Active', 'repeating' => 'Repeating', 'completed' => 'Completed', 'graduated' => 'Graduated', 'transferred' => 'Transferred', 'withdrawn' => 'Withdrawn'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('enrollment_status') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="student_status" class="block text-sm font-medium text-text-primary">Student status</label>
                                <select id="student_status" name="student_status" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="">Any student</option>
                                    @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'graduated' => 'Graduated', 'transferred' => 'Transferred', 'withdrawn' => 'Withdrawn', 'archived' => 'Archived'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('student_status') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label for="published_result_status" class="block text-sm font-medium text-text-primary">Result state</label>
                                <select id="published_result_status" name="published_result_status" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="">Any result state</option>
                                    <option value="published" @selected(old('published_result_status') === 'published')>Published only</option>
                                    <option value="not_published" @selected(old('published_result_status') === 'not_published')>Not published only</option>
                                </select>
                            </div>

                            <div>
                                <label for="user_status" class="block text-sm font-medium text-text-primary">Staff status</label>
                                <select id="user_status" name="user_status" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="active" @selected(old('user_status', 'active') === 'active')>Active users</option>
                                    <option value="inactive" @selected(old('user_status') === 'inactive')>Inactive users</option>
                                    <option value="any" @selected(old('user_status') === 'any')>Any users</option>
                                </select>
                            </div>

                            <div>
                                <label for="chunk_size" class="block text-sm font-medium text-text-primary">Send chunk size</label>
                                <select id="chunk_size" name="chunk_size" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    @foreach ([10, 25, 50, 100] as $size)
                                        <option value="{{ $size }}" @selected((int) old('chunk_size', 25) === $size)>{{ $size }} recipients</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div x-show="audience === 'selected_students'" x-cloak>
                            <label for="student_ids" class="block text-sm font-medium text-text-primary">Selected students</label>
                            <select id="student_ids" name="student_ids[]" multiple size="7" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}" @selected(in_array((string) $student->id, $selectedStudents, true))>
                                        {{ $student->fullName() }} - {{ $student->admission_number }} - {{ $student->guardian_email }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-sm text-text-secondary">Hold Ctrl or Cmd to choose more than one student.</p>
                        </div>
                    </section>

                    <section class="space-y-4 border-t border-border-subtle pt-6">
                        <div>
                            <h3 class="text-sm font-semibold text-text-primary">Message</h3>
                            <p class="mt-1 text-sm text-text-secondary">Email is active now. SMS and in-app selections are recorded for audit visibility and skipped until those channels are configured.</p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="template_id" class="block text-sm font-medium text-text-primary">Template</label>
                                <select id="template_id" name="template_id" @change="applyTemplate($event)" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="">No template</option>
                                    @foreach ($templates as $template)
                                        <option value="{{ $template->id }}" data-subject="{{ $template->subject ?: $template->title }}" data-body="{{ $template->body }}" @selected((string) old('template_id') === (string) $template->id)>
                                            {{ $template->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-medium text-text-primary">Message type</label>
                                <select id="type" name="type" x-model="type" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                    <option value="result_notification" @selected(old('type') === 'result_notification')>Result Notification</option>
                                    <option value="report_card" @selected(old('type') === 'report_card')>Report Card</option>
                                    <option value="scratch_card" @selected(old('type') === 'scratch_card')>Scratch Card</option>
                                    <option value="payment_reminder" @selected(old('type') === 'payment_reminder')>Payment Reminder</option>
                                    <option value="attendance_warning" @selected(old('type') === 'attendance_warning')>Attendance Warning</option>
                                    <option value="custom_message" @selected(old('type', 'custom_message') === 'custom_message')>Custom Message</option>
                                </select>
                                @error('type') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-text-primary">Channels</label>
                            <div class="mt-2 grid gap-2 sm:grid-cols-3">
                                <label class="flex items-center gap-3 rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-sm text-text-primary">
                                    <input type="checkbox" name="channels[]" value="email" class="rounded border-gray-300" @checked(in_array('email', $selectedChannels, true))>
                                    <span>Email</span>
                                </label>
                                <label class="flex items-center gap-3 rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-sm text-text-primary">
                                    <input type="checkbox" name="channels[]" value="sms" class="rounded border-gray-300" @checked(in_array('sms', $selectedChannels, true))>
                                    <span>SMS</span>
                                </label>
                                <label class="flex items-center gap-3 rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-sm text-text-primary">
                                    <input type="checkbox" name="channels[]" value="in_app" class="rounded border-gray-300" @checked(in_array('in_app', $selectedChannels, true))>
                                    <span>In-app</span>
                                </label>
                            </div>
                            @error('channels') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-text-primary">Subject</label>
                            <input id="subject" name="subject" x-model="subject" value="{{ old('subject') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('subject') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-text-primary">Message body</label>
                            <textarea id="message" name="message" x-model="message" rows="7" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('message') }}</textarea>
                            @error('message') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </section>
                </div>

                <aside class="space-y-4 xl:sticky xl:top-24 xl:self-start">
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                        <h3 class="text-sm font-semibold text-text-primary">Recipient Summary</h3>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-text-secondary">Student email contacts</dt>
                                <dd class="font-semibold text-text-primary">{{ $recipientSummary['visible_students'] }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-text-secondary">Teacher email contacts</dt>
                                <dd class="font-semibold text-text-primary">{{ $recipientSummary['teacher_contacts'] }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-text-secondary">Result officer contacts</dt>
                                <dd class="font-semibold text-text-primary">{{ $recipientSummary['result_officer_contacts'] }}</dd>
                            </div>
                        </dl>
                        <p class="mt-4 text-xs leading-5 text-text-secondary">Final recipients are resolved when the batch is created, using the filters in this form.</p>
                    </div>

                    <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                        <h3 class="text-sm font-semibold text-text-primary">Preview</h3>
                        <div class="mt-4 space-y-3 text-sm">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-text-tertiary">Type</p>
                                <p class="mt-1 font-semibold text-text-primary" x-text="type.replaceAll('_', ' ')"></p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-text-tertiary">Subject</p>
                                <p class="mt-1 font-semibold text-text-primary" x-text="subject || 'Subject will appear here'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-text-tertiary">Message</p>
                                <p class="mt-1 whitespace-pre-line text-text-secondary" x-text="message || 'Message preview will appear here'"></p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="ui-button-primary w-full" data-loading-text="{{ __('ui.sending_message') }}">
                        Create And Process Batch
                    </button>
                </aside>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Recent Bulk Batches" description="Continue paused batches or retry failed recipients from the latest batch history.">
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full divide-y divide-border-subtle text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-normal text-text-tertiary">
                            <th class="px-3 py-2">Batch</th>
                            <th class="px-3 py-2">Audience</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Counts</th>
                            <th class="px-3 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse ($recentBatches as $batch)
                            <tr>
                                <td class="px-3 py-3">
                                    <span class="block font-semibold text-text-primary">{{ $batch->subject }}</span>
                                    <span class="mt-1 block font-mono text-xs text-text-tertiary">{{ $batch->batch_uuid }}</span>
                                </td>
                                <td class="px-3 py-3 text-text-secondary">{{ str_replace('_', ' ', $batch->audience) }}</td>
                                <td class="px-3 py-3"><x-ui.badge :tone="$recentBatchTone[$batch->status] ?? 'outline'">{{ str($batch->status)->replace('_', ' ')->title() }}</x-ui.badge></td>
                                <td class="px-3 py-3 text-text-secondary">
                                    Sent {{ $batch->sent_count }} / Failed {{ $batch->failed_count }} / Skipped {{ $batch->skipped_count }} / Pending {{ $batch->pendingRecipientCount() }}
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if ($batch->isProcessable() && $batch->pendingRecipientCount() > 0)
                                            <form method="POST" action="{{ route('school.communications.bulk.process', $batch) }}">
                                                @csrf
                                                <button class="ui-button-secondary" data-loading-text="Continuing...">Continue</button>
                                            </form>
                                        @endif
                                        @if ($batch->isRetryable())
                                            <form method="POST" action="{{ route('school.communications.bulk.retry-failed', $batch) }}">
                                                @csrf
                                                <button class="ui-button-secondary" data-loading-text="Retrying...">Retry Failed</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-8">
                                    <x-ui.empty-state title="No bulk batches yet" body="Create the first batch when a school-wide or class message is ready." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="grid gap-3 md:hidden">
                @forelse ($recentBatches as $batch)
                    <article class="rounded-md border border-border-subtle bg-bg-primary p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-text-primary">{{ $batch->subject }}</p>
                                <p class="mt-1 font-mono text-xs text-text-tertiary">{{ $batch->batch_uuid }}</p>
                            </div>
                            <x-ui.badge :tone="$recentBatchTone[$batch->status] ?? 'outline'">{{ str($batch->status)->replace('_', ' ')->title() }}</x-ui.badge>
                        </div>
                        <p class="mt-3 text-sm text-text-secondary">{{ str_replace('_', ' ', $batch->audience) }}</p>
                        <p class="mt-2 text-sm text-text-secondary">Sent {{ $batch->sent_count }} / Failed {{ $batch->failed_count }} / Skipped {{ $batch->skipped_count }} / Pending {{ $batch->pendingRecipientCount() }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if ($batch->isProcessable() && $batch->pendingRecipientCount() > 0)
                                <form method="POST" action="{{ route('school.communications.bulk.process', $batch) }}">
                                    @csrf
                                    <button class="ui-button-secondary" data-loading-text="Continuing...">Continue</button>
                                </form>
                            @endif
                            @if ($batch->isRetryable())
                                <form method="POST" action="{{ route('school.communications.bulk.retry-failed', $batch) }}">
                                    @csrf
                                    <button class="ui-button-secondary" data-loading-text="Retrying...">Retry Failed</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <x-ui.empty-state title="No bulk batches yet" body="Create the first batch when a school-wide or class message is ready." />
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
