<x-app-layout>
    <x-slot name="header">
        @php
            $liveClassBranding = app(\App\Services\Branding\BrandingService::class)->forSchool($school);
            $liveClassBrandName = data_get($liveClassBranding, 'brand_name', $school->name);
            $liveClassLogo = data_get($liveClassBranding, 'logo_url');
            $liveClassInitials = data_get($liveClassBranding, 'initials', $school->initials());
        @endphp
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex min-w-0 gap-3">
                @if ($liveClassLogo)
                    <img src="{{ $liveClassLogo }}" alt="{{ $liveClassBrandName }} logo" class="h-12 w-12 shrink-0 rounded-md border border-border-subtle bg-white object-contain p-1">
                @else
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-brand-primary text-sm font-semibold text-white">{{ $liveClassInitials }}</span>
                @endif
                <div class="min-w-0">
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Live Classes</h2>
                <p class="mt-1 text-sm text-text-secondary">Provider-ready manual internet-based class sessions for {{ $liveClassBrandName }}.</p>
                </div>
            </div>
            @if ($canManage)
                <a href="{{ route('school.live-classes.create') }}" class="ui-button-primary">Schedule Live Class</a>
            @endif
        </div>
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
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ str($status)->title() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date-from" class="block text-sm font-medium text-text-primary">From</label>
                    <input id="date-from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                </div>
                <div>
                    <label for="date-to" class="block text-sm font-medium text-text-primary">To</label>
                    <input id="date-to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                </div>
                <div class="flex items-end gap-2">
                    <button class="ui-button-secondary">Filter</button>
                    <a href="{{ route('school.live-classes.index') }}" class="ui-button-secondary">Clear</a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border-subtle text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-normal text-text-tertiary">
                            <th class="px-3 py-2">Title</th>
                            <th class="px-3 py-2">Class / Subject</th>
                            <th class="px-3 py-2">Starts</th>
                            <th class="px-3 py-2">Teacher</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse ($liveClasses as $liveClass)
                            <tr>
                                <td class="px-3 py-3">
                                    <span class="block font-semibold text-text-primary">{{ $liveClass->title }}</span>
                                    <span class="mt-1 block text-xs text-text-tertiary">{{ $providerLabels[$liveClass->provider] ?? str($liveClass->provider)->title() }}</span>
                                </td>
                                <td class="px-3 py-3 text-text-secondary">
                                    {{ $liveClass->schoolClass?->name }} {{ $liveClass->schoolClass?->section }}
                                    <span class="block text-xs text-text-tertiary">{{ $liveClass->subject?->name ?? 'No subject selected' }}</span>
                                </td>
                                <td class="px-3 py-3 text-text-secondary">
                                    {{ $liveClass->starts_at?->format('d M Y H:i') }}
                                    <span class="block text-xs text-text-tertiary">{{ $liveClass->timezone ?: config('app.timezone') }}</span>
                                </td>
                                <td class="px-3 py-3 text-text-secondary">{{ $liveClass->teacher?->name ?? 'Not assigned' }}</td>
                                <td class="px-3 py-3"><x-ui.badge :status="$liveClass->status" /></td>
                                <td class="px-3 py-3 text-right">
                                    <a href="{{ route('school.live-classes.show', $liveClass) }}" class="ui-button-secondary">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8">
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
            <div class="mt-4">{{ $liveClasses->links() }}</div>
        </x-ui.panel>

        <x-ui.panel tone="info" title="Stage 17 Provider Boundary">
            <p class="text-sm leading-6 text-text-secondary">
                Live classes require internet. Manual meeting links remain the active provider behavior. Provider automation is not active yet. Google Meet API automation is not implemented. Zoom API automation is not implemented. Microsoft Teams API automation is not implemented. OAuth, provider credentials, generated meeting rooms, webhooks, live-class attendance, chat, analytics, video hosting, transcoding, and offline live class are not implemented in this stage.
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
