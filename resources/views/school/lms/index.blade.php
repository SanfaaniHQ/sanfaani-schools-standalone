<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Learning Materials</h2>
                <p class="mt-1 text-sm text-text-secondary">Online class and subject material foundation for {{ $school->name }}.</p>
            </div>
            <a href="#create-lms-classroom" class="ui-button-primary">New Classroom</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success" :body="session('success')" />
        @endif

        @if ($errors->any())
            <x-ui.alert tone="danger" body="Review the highlighted LMS fields and try again." />
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Classrooms" :value="$stats['classrooms']" meta="Visible to your role" />
            <x-ui.stat-card label="Materials" :value="$stats['materials']" meta="Drafts, published, and archived" tone="info" />
            <x-ui.stat-card label="Published" :value="$stats['published']" meta="Visible after authorization" tone="success" />
            <x-ui.stat-card label="Resources" :value="$stats['resources']" meta="Private local files" tone="warning" />
            <x-ui.stat-card label="CBT Activities" :value="$stats['cbtActivities']" meta="Linked existing CBT items" tone="info" />
        </section>

        <section class="grid gap-4 lg:grid-cols-[1fr_24rem]">
            <x-ui.panel title="LMS Classrooms" description="Classrooms are scoped to existing classes, subjects, sessions, and terms. Teachers only see assigned scopes.">
                <div class="space-y-3">
                    @forelse ($classrooms as $classroom)
                        <a href="{{ route('school.lms.classrooms.show', $classroom) }}" class="block rounded-md border border-border-subtle bg-bg-primary p-4 transition hover:border-border-hover hover:bg-bg-tertiary">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-semibold text-text-primary">{{ $classroom->title }}</h3>
                                        <x-ui.badge :status="$classroom->status" />
                                    </div>
                                    <p class="mt-1 text-sm text-text-secondary">
                                        {{ $classroom->schoolClass?->name }} {{ $classroom->schoolClass?->section }} / {{ $classroom->subject?->name }}
                                    </p>
                                    <p class="mt-1 text-xs text-text-tertiary">
                                        {{ $classroom->academicSession?->name ?? 'Any session' }} / {{ $classroom->term?->name ?? 'Any term' }}
                                    </p>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-right text-xs text-text-tertiary">
                                    <span><strong class="block text-base text-brand-primary">{{ $classroom->topics_count }}</strong>Topics</span>
                                    <span><strong class="block text-base text-brand-primary">{{ $classroom->materials_count }}</strong>Materials</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <x-ui.empty-state
                            title="No LMS classrooms yet"
                            body="Create a class and subject classroom to start posting lessons, notes, resources, or assignment material without submissions."
                        />
                    @endforelse
                </div>
            </x-ui.panel>

            <x-ui.panel id="create-lms-classroom" title="Create Classroom" description="Duplicate class, subject, session, and term combinations are blocked.">
                <form method="POST" action="{{ route('school.lms.classrooms.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="lms-title" class="block text-sm font-medium text-text-primary">Title</label>
                        <input id="lms-title" name="title" value="{{ old('title') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                        @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="lms-class" class="block text-sm font-medium text-text-primary">Class</label>
                        <select id="lms-class" name="school_class_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                            <option value="">Select class</option>
                            @foreach ($schoolClasses as $class)
                                <option value="{{ $class->id }}" @selected((int) old('school_class_id') === (int) $class->id)>{{ $class->name }} {{ $class->section }}</option>
                            @endforeach
                        </select>
                        @error('school_class_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="lms-subject" class="block text-sm font-medium text-text-primary">Subject</label>
                        <select id="lms-subject" name="subject_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                            <option value="">Select subject</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected((int) old('subject_id') === (int) $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="lms-session" class="block text-sm font-medium text-text-primary">Session</label>
                            <select id="lms-session" name="academic_session_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Any</option>
                                @foreach ($academicSessions as $session)
                                    <option value="{{ $session->id }}" @selected((int) old('academic_session_id') === (int) $session->id)>{{ $session->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="lms-term" class="block text-sm font-medium text-text-primary">Term</label>
                            <select id="lms-term" name="term_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                                <option value="">Any</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected((int) old('term_id') === (int) $term->id)>{{ $term->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="lms-description" class="block text-sm font-medium text-text-primary">Description</label>
                        <textarea id="lms-description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('description') }}</textarea>
                    </div>
                    <button class="ui-button-primary w-full">Create Classroom</button>
                </form>
            </x-ui.panel>
        </section>

        <x-ui.panel tone="info" title="Stage 15 Boundary">
            <p class="text-sm leading-6 text-text-secondary">
                LMS files use private local storage and authorized downloads. Existing CBT items can now be linked to LMS classrooms or materials while CBT remains the assessment engine. Student LMS viewing is deferred until safe student identity resolution exists. Live classes, offline LMS, submissions/grading, forums, analytics, video hosting, and payment-gated content are not implemented in this stage.
            </p>
            <p class="mt-2 text-xs text-text-tertiary">
                Allowed resources: {{ implode(', ', $allowedExtensions) }}. Maximum size: {{ $maxUploadMb }} MB.
            </p>
        </x-ui.panel>
    </div>
</x-app-layout>
