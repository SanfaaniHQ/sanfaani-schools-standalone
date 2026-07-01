<x-app-layout>
    <x-slot name="header">
        @php
            $liveClassBranding = app(\App\Services\Branding\BrandingService::class)->forSchool($school);
            $liveClassBrandName = data_get($liveClassBranding, 'brand_name', $school->name);
            $liveClassLogo = data_get($liveClassBranding, 'logo_url');
            $liveClassInitials = data_get($liveClassBranding, 'initials', $school->initials());
        @endphp
        <x-ui.page-header title="Live Classes" :description="'Provider-ready manual internet-based class sessions for '.$liveClassBrandName.'.'">
            @if ($canManage)
                <x-slot name="actions">
                    <a href="{{ route('school.live-classes.create') }}" class="ui-button-primary">Schedule Live Class</a>
                </x-slot>
            @endif
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success" :body="session('success')" />
        @endif

        @if ($errors->any())
            <x-ui.alert tone="danger" body="Review the live class fields and try again." />
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-ui.stat-card label="Total" :value="$stats['total']" meta="Visible sessions" />
            <x-ui.stat-card label="Scheduled" :value="$stats['scheduled']" meta="Waiting to start" tone="info" />
            <x-ui.stat-card label="Live" :value="$stats['live']" meta="Marked live" tone="success" />
            <x-ui.stat-card label="Completed" :value="$stats['completed']" meta="Finished sessions" />
            <x-ui.stat-card label="Upcoming" :value="$stats['upcoming']" meta="Scheduled from now" tone="warning" />
        </section>

        <x-ui.panel title="Live Class Schedule" description="Teachers only see assigned class and subject scopes. School admins see all scheduled sessions.">
            <form method="GET" action="{{ route('school.live-classes.index') }}" class="mb-5 grid gap-3 md:grid-cols-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-text-primary">Status</label>
                    <select id="status" name="status" class="ui-input mt-1">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ str($status)->title() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date-from" class="block text-sm font-medium text-text-primary">From</label>
                    <input id="date-from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="ui-input mt-1">
                </div>
                <div>
                    <label for="date-to" class="block text-sm font-medium text-text-primary">To</label>
                    <input id="date-to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="ui-input mt-1">
                </div>
                <div class="flex items-end gap-2">
                    <button class="ui-button-secondary">Filter</button>
                    <a href="{{ route('school.live-classes.index') }}" class="ui-button-secondary">Clear</a>
                </div>
            </form>

            <div class="safe-scroll-x hidden rounded-none border-0 shadow-none sm:block">
                <table class="enterprise-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Class / Subject</th>
                            <th>Starts</th>
                            <th>Teacher</th>
                            <th>Audience</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($liveClasses as $liveClass)
                            <tr>
                                <td>
                                    <span class="block font-semibold text-text-primary">{{ $liveClass->title }}</span>
                                    <span class="mt-1 block text-xs text-text-tertiary">{{ $providerLabels[$liveClass->provider] ?? str($liveClass->provider)->title() }}</span>
                                </td>
                                <td class="text-text-secondary">
                                    {{ $liveClass->schoolClass?->name }} {{ $liveClass->schoolClass?->section }}
                                    <span class="block text-xs text-text-tertiary">{{ $liveClass->subject?->name ?? 'No subject selected' }}</span>
                                </td>
                                <td class="text-text-secondary">
                                    {{ $liveClass->starts_at?->format('d M Y H:i') }}
                                    <span class="block text-xs text-text-tertiary">{{ $liveClass->timezone ?: config('app.timezone') }}</span>
                                </td>
                                <td class="text-text-secondary">{{ $liveClass->teacher?->name ?? 'Not assigned' }}</td>
                                <td class="text-text-secondary">
                                    {{ number_format((int) ($liveClass->active_participants_count ?? 0)) }}
                                    <span class="block text-xs text-text-tertiary">participants</span>
                                </td>
                                <td><x-ui.badge :status="$liveClass->status" /></td>
                                <td class="text-right">
                                    <a href="{{ route('school.live-classes.show', $liveClass) }}" class="ui-button-secondary">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-8">
                                    <x-ui.empty-state
                                        title="No live classes yet"
                                        body="Schedule the first manual meeting link for an existing class, subject, session, or LMS context."
                                        :action-href="$canManage ? route('school.live-classes.create') : null"
                                        action-label="Schedule Live Class"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mobile-card-list sm:hidden">
                @forelse ($liveClasses as $liveClass)
                    <article class="enterprise-mobile-card mobile-table-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-semibold text-text-primary">{{ $liveClass->title }}</h3>
                                <p class="mt-1 text-xs text-text-tertiary">{{ $providerLabels[$liveClass->provider] ?? str($liveClass->provider)->title() }}</p>
                            </div>
                            <x-ui.badge :status="$liveClass->status" />
                        </div>
                        <dl class="mt-4 grid gap-3 text-sm">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Class / Subject</dt>
                                <dd class="mt-1 text-text-primary">
                                    {{ $liveClass->schoolClass?->name }} {{ $liveClass->schoolClass?->section }}
                                    <span class="block text-text-secondary">{{ $liveClass->subject?->name ?? 'No subject selected' }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Starts</dt>
                                <dd class="mt-1 text-text-primary">
                                    {{ $liveClass->starts_at?->format('d M Y H:i') }}
                                    <span class="block text-text-secondary">{{ $liveClass->timezone ?: config('app.timezone') }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Teacher / Audience</dt>
                                <dd class="mt-1 text-text-primary">
                                    {{ $liveClass->teacher?->name ?? 'Not assigned' }}
                                    <span class="block text-text-secondary">{{ number_format((int) ($liveClass->active_participants_count ?? 0)) }} participants</span>
                                </dd>
                            </div>
                        </dl>
                        <a href="{{ route('school.live-classes.show', $liveClass) }}" class="ui-button-secondary mt-4">Open</a>
                    </article>
                @empty
                    <x-ui.empty-state
                        title="No live classes yet"
                        body="Schedule the first manual meeting link for an existing class, subject, session, or LMS context."
                        :action-href="$canManage ? route('school.live-classes.create') : null"
                        action-label="Schedule Live Class"
                    />
                @endforelse
            </div>
            <div class="mt-4">{{ $liveClasses->links() }}</div>
        </x-ui.panel>

        <x-ui.panel tone="info" title="Stage 17 Provider Boundary">
            <p class="text-sm leading-6 text-text-secondary">
                Live classes require internet. Manual meeting links are the active provider flow today. Google Meet, Zoom, and Microsoft Teams API automation remain disabled until provider credentials, generated meeting rooms, webhooks, attendance capture, chat, analytics, video hosting, transcoding, and offline class delivery are safely enabled.
            </p>
            @if ($futureProviders !== [])
                <p class="mt-2 text-xs leading-5 text-text-tertiary">
                    Future provider metadata: {{ collect($futureProviders)->pluck('label')->implode(', ') }}. These providers are disabled for API automation and do not store secrets.
                </p>
            @endif
            <p class="mt-2 text-xs leading-5 text-text-tertiary">
                {{ $studentPortalBoundary }} {{ $parentPortalBoundary }}
            </p>
        </x-ui.panel>
    </div>
</x-app-layout>
