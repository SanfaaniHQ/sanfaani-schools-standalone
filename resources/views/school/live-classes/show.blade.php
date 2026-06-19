<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $liveClass->title }}</h2>
                    <x-ui.badge :status="$liveClass->status" />
                    <x-ui.badge tone="outline">{{ $provider['label'] }}</x-ui.badge>
                </div>
                <p class="mt-1 text-sm text-text-secondary">
                    {{ $liveClass->schoolClass?->name }} {{ $liveClass->schoolClass?->section }} / {{ $liveClass->subject?->name ?? 'No subject selected' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.live-classes.index') }}" class="ui-button-secondary">Live Classes</a>
                <a href="{{ $liveClass->meeting_url }}" target="_blank" rel="noopener noreferrer" class="ui-button-primary">Join Session</a>
                @if ($canManage)
                    <a href="{{ route('school.live-classes.edit', $liveClass) }}" class="ui-button-secondary">Edit</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success" :body="session('success')" />
        @endif

        @if ($errors->any())
            <x-ui.alert tone="danger" body="Review the live class action and try again." />
        @endif

        <section class="grid gap-4 lg:grid-cols-[1fr_24rem]">
            <x-ui.panel title="Live Class Details">
                <dl class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <dt class="text-xs uppercase tracking-normal text-text-tertiary">Class</dt>
                        <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $liveClass->schoolClass?->name }} {{ $liveClass->schoolClass?->section }}</dd>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <dt class="text-xs uppercase tracking-normal text-text-tertiary">Subject</dt>
                        <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $liveClass->subject?->name ?? 'No subject selected' }}</dd>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <dt class="text-xs uppercase tracking-normal text-text-tertiary">Session</dt>
                        <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $liveClass->academicSession?->name ?? 'Any session' }}</dd>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <dt class="text-xs uppercase tracking-normal text-text-tertiary">Term</dt>
                        <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $liveClass->term?->name ?? 'Any term' }}</dd>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <dt class="text-xs uppercase tracking-normal text-text-tertiary">Starts</dt>
                        <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $liveClass->starts_at?->format('d M Y H:i') }}</dd>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <dt class="text-xs uppercase tracking-normal text-text-tertiary">Ends</dt>
                        <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $liveClass->ends_at?->format('d M Y H:i') ?? 'Not set' }}</dd>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <dt class="text-xs uppercase tracking-normal text-text-tertiary">Timezone</dt>
                        <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $liveClass->timezone ?: config('app.timezone') }}</dd>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <dt class="text-xs uppercase tracking-normal text-text-tertiary">Teacher</dt>
                        <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $liveClass->teacher?->name ?? 'Not assigned' }}</dd>
                    </div>
                </dl>

                @if ($liveClass->description)
                    <p class="mt-4 text-sm leading-6 text-text-secondary">{{ $liveClass->description }}</p>
                @endif
            </x-ui.panel>

            <x-ui.panel title="Join Details" description="Visible only to authorized school users.">
                <div class="space-y-3 text-sm">
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <p class="text-xs uppercase tracking-normal text-text-tertiary">Provider</p>
                        <p class="mt-1 font-semibold text-text-primary">{{ $provider['label'] }}</p>
                        <p class="mt-1 text-xs leading-5 text-text-secondary">{{ $provider['description'] }}</p>
                    </div>
                    <a href="{{ $liveClass->meeting_url }}" target="_blank" rel="noopener noreferrer" class="ui-button-primary w-full justify-center">Start / Join Session</a>
                    @if ($liveClass->meeting_password)
                        <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                            <p class="text-xs uppercase tracking-normal text-text-tertiary">Meeting Password</p>
                            <p class="mt-1 break-words font-mono text-sm font-semibold text-text-primary">{{ $liveClass->meeting_password }}</p>
                        </div>
                    @endif
                    @if ($liveClass->recording_url)
                        <a href="{{ $liveClass->recording_url }}" target="_blank" rel="noopener noreferrer" class="ui-button-secondary w-full justify-center">Open Recording</a>
                    @endif
                </div>
            </x-ui.panel>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <x-ui.panel title="LMS Context" description="Optional links back to the learning hub.">
                <div class="space-y-3">
                    @if ($liveClass->lmsClassroom)
                        <a href="{{ route('school.lms.classrooms.show', $liveClass->lmsClassroom) }}" class="block rounded-md border border-border-subtle bg-bg-primary p-3 text-sm hover:bg-bg-tertiary">
                            <span class="font-semibold text-text-primary">{{ $liveClass->lmsClassroom->title }}</span>
                            <span class="mt-1 block text-xs text-text-secondary">Linked LMS classroom</span>
                        </a>
                    @endif
                    @if ($liveClass->lmsMaterial)
                        <a href="{{ route('school.lms.materials.show', $liveClass->lmsMaterial) }}" class="block rounded-md border border-border-subtle bg-bg-primary p-3 text-sm hover:bg-bg-tertiary">
                            <span class="font-semibold text-text-primary">{{ $liveClass->lmsMaterial->title }}</span>
                            <span class="mt-1 block text-xs text-text-secondary">Linked LMS material</span>
                        </a>
                    @endif
                    @if (! $liveClass->lmsClassroom && ! $liveClass->lmsMaterial)
                        <p class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm text-text-secondary">No LMS classroom or material linked.</p>
                    @endif
                </div>
            </x-ui.panel>

            @if ($canManage)
                <x-ui.panel title="Resolved Audience" description="Participants receive in-app invitations and scheduled reminders.">
                    <div class="max-h-80 space-y-2 overflow-y-auto pr-1">
                        @forelse ($liveClass->participants->whereIn('status', \App\Models\LiveClassParticipant::ACTIVE_STATUSES) as $participant)
                            <div class="flex items-center justify-between gap-3 rounded-md border border-border-subtle bg-bg-primary p-3 text-sm">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-text-primary">{{ $participant->user?->name ?? 'Unknown user' }}</p>
                                    <p class="mt-1 text-xs text-text-tertiary">{{ str($participant->role_context ?: $participant->audience_type)->replace('_', ' ')->title() }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <x-ui.badge :status="$participant->status" />
                                    <p class="mt-1 text-xs text-text-tertiary">
                                        {{ $participant->reminder_sent_at ? 'Reminder sent' : ($participant->reminder_due_at ? 'Reminder pending' : 'No reminder') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm text-text-secondary">No participants have been resolved for this live class yet.</p>
                        @endforelse
                    </div>
                </x-ui.panel>
            @endif

            @if ($canManage)
                <x-ui.panel title="Status Workflow" description="Provider automation is deferred; these controls only update the local workflow state.">
                    <div class="flex flex-wrap gap-2">
                        @if ($liveClass->status === \App\Models\LiveClass::STATUS_SCHEDULED)
                            <form method="POST" action="{{ route('school.live-classes.start', $liveClass) }}">
                                @csrf
                                <button class="ui-button-primary">Mark Live</button>
                            </form>
                            <form method="POST" action="{{ route('school.live-classes.cancel', $liveClass) }}">
                                @csrf
                                <button class="ui-button-danger">Cancel</button>
                            </form>
                        @endif
                        @if ($liveClass->status === \App\Models\LiveClass::STATUS_LIVE)
                            <form method="POST" action="{{ route('school.live-classes.complete', $liveClass) }}">
                                @csrf
                                <button class="ui-button-primary">Complete</button>
                            </form>
                            <form method="POST" action="{{ route('school.live-classes.cancel', $liveClass) }}">
                                @csrf
                                <button class="ui-button-danger">Cancel</button>
                            </form>
                        @endif
                        @if (in_array($liveClass->status, [\App\Models\LiveClass::STATUS_COMPLETED, \App\Models\LiveClass::STATUS_CANCELLED], true))
                            <p class="text-sm text-text-secondary">This live class has reached a final local status.</p>
                        @endif
                    </div>
                </x-ui.panel>
            @endif
        </section>

        <x-ui.panel tone="info" title="Stage 17 Provider Boundary">
            <p class="text-sm leading-6 text-text-secondary">
                This version stores manual meeting and recording links only. It does not create provider rooms, call provider APIs, store OAuth credentials, host video, transcode recordings, collect live-class attendance, enable chat, or make live classes work offline.
            </p>
            <div class="mt-3 space-y-1 text-xs leading-5 text-text-tertiary">
                @foreach ($provider['boundary_notes'] as $note)
                    <p>{{ $note }}</p>
                @endforeach
            </div>
            <p class="mt-2 text-xs leading-5 text-text-tertiary">
                {{ $studentPortalBoundary }} {{ $parentPortalBoundary }}
            </p>
        </x-ui.panel>
    </div>
</x-app-layout>
