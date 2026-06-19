@php
    $isEdit = $method === 'PATCH';
    $activeParticipants = $liveClass->relationLoaded('participants')
        ? $liveClass->participants->whereIn('status', \App\Models\LiveClassParticipant::ACTIVE_STATUSES)
        : collect();
    $currentAudienceType = old('audience_type', $activeParticipants->first()?->audience_type ?? \App\Models\LiveClassParticipant::AUDIENCE_CLASS);
    $selectedAudienceUserIds = collect(old('selected_user_ids', $activeParticipants->pluck('user_id')->all()))
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $isEdit ? 'Edit Live Class' : 'Schedule Live Class' }}</h2>
                <p class="mt-1 text-sm text-text-secondary">Manual provider links remain active. Provider automation is not active yet.</p>
            </div>
            <a href="{{ $isEdit ? route('school.live-classes.show', $liveClass) : route('school.live-classes.index') }}" class="ui-button-secondary">Cancel</a>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1fr_24rem]">
        <form method="POST" action="{{ $action }}" class="space-y-6">
            @csrf
            @if ($isEdit)
                @method('PATCH')
            @endif

            <x-ui.panel title="Session Details">
                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-text-primary">Title</label>
                        <input id="title" name="title" value="{{ old('title', $liveClass->title) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                        @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-text-primary">Description</label>
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('description', $liveClass->description) }}</textarea>
                        @error('description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="school-class" class="block text-sm font-medium text-text-primary">Class</label>
                            <select id="school-class" name="school_class_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                                <option value="">Select class</option>
                                @foreach ($schoolClasses as $class)
                                    <option value="{{ $class->id }}" @selected((int) old('school_class_id', $liveClass->school_class_id) === (int) $class->id)>{{ $class->name }} {{ $class->section }}</option>
                                @endforeach
                            </select>
                            @error('school_class_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-text-primary">Subject</label>
                            <select id="subject" name="subject_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">No subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected((int) old('subject_id', $liveClass->subject_id) === (int) $subject->id)>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="academic-session" class="block text-sm font-medium text-text-primary">Session</label>
                            <select id="academic-session" name="academic_session_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Any session</option>
                                @foreach ($academicSessions as $session)
                                    <option value="{{ $session->id }}" @selected((int) old('academic_session_id', $liveClass->academic_session_id) === (int) $session->id)>{{ $session->name }}</option>
                                @endforeach
                            </select>
                            @error('academic_session_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="term" class="block text-sm font-medium text-text-primary">Term</label>
                            <select id="term" name="term_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Any term</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected((int) old('term_id', $liveClass->term_id) === (int) $term->id)>{{ $term->name }}</option>
                                @endforeach
                            </select>
                            @error('term_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </x-ui.panel>

            <x-ui.panel title="Audience" description="Resolved users receive in-app invitations and reminder rows.">
                <div class="space-y-4">
                    <div>
                        <label for="audience-type" class="block text-sm font-medium text-text-primary">Audience</label>
                        <select id="audience-type" name="audience_type" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @foreach ($audienceTypes as $key => $label)
                                <option value="{{ $key }}" @selected($currentAudienceType === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('audience_type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="selected-user-ids" class="block text-sm font-medium text-text-primary">Selected Users</label>
                        <select id="selected-user-ids" name="selected_user_ids[]" multiple size="8" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @foreach ($eligibleUsers as $eligibleUser)
                                <option value="{{ $eligibleUser->id }}" @selected(in_array((int) $eligibleUser->id, $selectedAudienceUserIds, true))>
                                    {{ $eligibleUser->name }} @if($eligibleUser->email) / {{ $eligibleUser->email }} @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-text-tertiary">Only required when the audience is Selected users. Class and role audiences are resolved automatically.</p>
                        @error('selected_user_ids') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        @error('selected_user_ids.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </x-ui.panel>

            <x-ui.panel title="Provider And Meeting Link">
                <div class="space-y-4">
                    <div>
                        <label for="provider" class="block text-sm font-medium text-text-primary">Provider</label>
                        <select id="provider" name="provider" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                            @foreach ($providerOptions as $option)
                                <option value="{{ $option['key'] }}" @selected(old('provider', $liveClass->provider ?: $activeProvider['key']) === $option['key'])>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-text-tertiary">Provider automation is not active yet. Paste the meeting link manually.</p>
                        @error('provider') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="meeting-url" class="block text-sm font-medium text-text-primary">Manual Meeting URL</label>
                        <input id="meeting-url" name="meeting_url" type="url" value="{{ old('meeting_url', $liveClass->meeting_url) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" placeholder="https://meet.example.com/class-room" required>
                        @error('meeting_url') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="meeting-password" class="block text-sm font-medium text-text-primary">Meeting Password</label>
                        <input id="meeting-password" name="meeting_password" value="{{ old('meeting_password', $liveClass->meeting_password) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('meeting_password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="starts-at" class="block text-sm font-medium text-text-primary">Starts At</label>
                            <input id="starts-at" name="starts_at" type="datetime-local" value="{{ old('starts_at', optional($liveClass->starts_at)->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                            @error('starts_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="ends-at" class="block text-sm font-medium text-text-primary">Ends At</label>
                            <input id="ends-at" name="ends_at" type="datetime-local" value="{{ old('ends_at', optional($liveClass->ends_at)->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('ends_at') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="timezone" class="block text-sm font-medium text-text-primary">Timezone</label>
                            <input id="timezone" name="timezone" value="{{ old('timezone', $liveClass->timezone ?: config('app.timezone')) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('timezone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="reminder-minutes" class="block text-sm font-medium text-text-primary">Reminder Minutes</label>
                            <input id="reminder-minutes" name="reminder_minutes" type="number" min="0" max="10080" value="{{ old('reminder_minutes', data_get($liveClass->metadata, 'reminder_minutes')) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @error('reminder_minutes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </x-ui.panel>

            <x-ui.panel title="LMS And Recording Links" description="LMS links must stay inside the same school and academic scope.">
                <div class="space-y-4">
                    <div>
                        <label for="lms-classroom" class="block text-sm font-medium text-text-primary">LMS Classroom</label>
                        <select id="lms-classroom" name="lms_classroom_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">No LMS classroom</option>
                            @foreach ($lmsClassrooms as $classroom)
                                <option value="{{ $classroom->id }}" @selected((int) old('lms_classroom_id', $liveClass->lms_classroom_id) === (int) $classroom->id)>{{ $classroom->title }} / {{ $classroom->schoolClass?->name }} / {{ $classroom->subject?->name }}</option>
                            @endforeach
                        </select>
                        @error('lms_classroom_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="lms-material" class="block text-sm font-medium text-text-primary">LMS Material</label>
                        <select id="lms-material" name="lms_material_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">No LMS material</option>
                            @foreach ($lmsMaterials as $material)
                                <option value="{{ $material->id }}" @selected((int) old('lms_material_id', $liveClass->lms_material_id) === (int) $material->id)>{{ $material->title }} / {{ $material->classroom?->title }}</option>
                            @endforeach
                        </select>
                        @error('lms_material_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="teacher-user" class="block text-sm font-medium text-text-primary">Teacher</label>
                        <select id="teacher-user" name="teacher_user_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">No specific teacher</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected((int) old('teacher_user_id', $liveClass->teacher_user_id) === (int) $teacher->id)>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                        @error('teacher_user_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="recording-url" class="block text-sm font-medium text-text-primary">Recording URL</label>
                        <input id="recording-url" name="recording_url" type="url" value="{{ old('recording_url', $liveClass->recording_url) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('recording_url') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </x-ui.panel>

            <div class="flex justify-end">
                <button class="ui-button-primary">{{ $isEdit ? 'Save Live Class' : 'Schedule Live Class' }}</button>
            </div>
        </form>

        <aside class="space-y-6">
            <x-ui.panel tone="info" title="Provider Abstraction">
                <p class="text-sm leading-6 text-text-secondary">
                    {{ $activeProvider['label'] }} is the only active provider. This stage does not generate rooms, store provider credentials, call OAuth, or connect to Zoom, Google Meet, or Microsoft Teams APIs.
                </p>
                @if ($futureProviders !== [])
                    <div class="mt-3 space-y-2">
                        @foreach ($futureProviders as $futureProvider)
                            <p class="rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-xs text-text-secondary">
                                {{ $futureProvider['label'] }}: future provider metadata only; API automation is disabled.
                            </p>
                        @endforeach
                    </div>
                @endif
            </x-ui.panel>
            <x-ui.panel tone="warning" title="Online Session Boundary">
                <p class="text-sm leading-6 text-text-secondary">
                    Live classes require internet. Offline live class, video hosting, transcoding, live chat, attendance capture, analytics, and payment-gated access are deferred.
                </p>
            </x-ui.panel>
        </aside>
    </div>
</x-app-layout>
