@php
    $standalone = app(\App\Services\Standalone\StandaloneEditionService::class);
    $dashboardBranding = app(\App\Services\Branding\BrandingService::class)->forSchool($school);
    $dashboardBrandName = data_get($dashboardBranding, 'brand_name', $school->name);
    $dashboardLogo = data_get($dashboardBranding, 'logo_url');
    $dashboardInitials = data_get($dashboardBranding, 'initials', $school->initials());
    $dashboardHeading = data_get($dashboardBranding, 'dashboard_heading', 'School Operations Command Center');
    $brandingConfigured = $school->activeBrandingSetting()->exists() || filled($school->logo_path ?: $school->logo);
    $schoolStatusLabel = $standalone->hidesSaasSurfaces() ? 'Access status' : 'Subscription status';
    $attentionItems = collect([
        ['label' => 'Pending scratch card payments', 'value' => $pendingScratchCardRequests, 'tone' => 'warning', 'href' => route('school.scratch-cards.index')],
        ['label' => 'Outstanding fee invoices', 'value' => $financeSummary['outstanding_invoices'] ?? 0, 'tone' => 'warning', 'href' => route('school.finance.invoices.index')],
        ['label' => 'Draft result records', 'value' => $draftResults, 'tone' => 'warning', 'href' => route('school.results.manual.index')],
        ['label' => 'Reviewed results waiting publish', 'value' => $reviewedResults, 'tone' => 'success', 'href' => route('school.results.publishing.index')],
        ['label' => 'Setup gaps', 'value' => ($activeSession ? 0 : 1) + ($activeTerm ? 0 : 1) + ($totalClasses > 0 ? 0 : 1) + ($totalSubjects > 0 ? 0 : 1), 'tone' => 'danger', 'href' => route('school.profile.edit')],
    ])->filter(fn ($item) => (int) $item['value'] > 0);

    $moduleCards = [
        ['title' => 'Reports Center', 'body' => 'Consolidated school-scoped report summaries with safe links into existing detailed reports and exports.', 'href' => route('school.reports.index'), 'feature' => 'reports.view'],
        ['title' => 'Students', 'body' => 'Enrollment records and Student 360 profiles.', 'href' => route('school.students.index'), 'feature' => 'students.view'],
        ['title' => 'Classes', 'body' => 'Class arms and academic grouping.', 'href' => route('school.classes.index'), 'feature' => null],
        ['title' => 'Subjects', 'body' => 'Subject catalog and class assignments.', 'href' => route('school.subjects.index'), 'feature' => null],
        ['title' => 'Sessions', 'body' => 'Academic years, current session, and archives.', 'href' => route('school.sessions.index'), 'feature' => null],
        ['title' => 'Terms', 'body' => 'Operational terms attached to sessions.', 'href' => route('school.terms.index'), 'feature' => null],
        ['title' => 'Admissions', 'body' => 'Applications, settings, channels, and conversion workflow.', 'href' => route('admin.admissions.index'), 'feature' => null],
        ['title' => 'Attendance', 'body' => 'Daily registers, filtered reports, status counts, and student history.', 'href' => route('school.attendance.index'), 'feature' => 'attendance.view'],
        ['title' => 'Learning Materials', 'body' => 'Class and subject LMS classrooms, topics, drafts, private resources, publishing, and CBT activity links.', 'href' => route('school.lms.index'), 'feature' => 'lms.view'],
        ['title' => 'Live Classes', 'body' => 'Schedule manual internet meeting links with manual provider support, recordings, and LMS context for class sessions.', 'href' => route('school.live-classes.index'), 'feature' => 'live_classes.view'],
        ['title' => 'Fees & Finance', 'body' => 'Fee setup, invoices, manual payments, reports, audit review, and balances.', 'href' => route('school.finance.index'), 'feature' => 'finance.view'],
        ['title' => 'Import / Export', 'body' => 'Safe CSV templates, previews, and selected operational exports.', 'href' => route('school.import-export.index'), 'feature' => null],
        ['title' => 'Results', 'body' => 'Manual entry, upload, review, and publishing.', 'href' => route('school.result-system.index'), 'feature' => 'results.manual_entry'],
        ['title' => 'CBT', 'body' => 'Question banks, exams, marking, result publishing, and the assessment engine behind LMS links.', 'href' => route('school.cbt.dashboard'), 'feature' => 'cbt.manage'],
        ['title' => 'Scratch Cards', 'body' => 'Batches, card inventory, and result access.', 'href' => route('school.scratch-cards.index'), 'feature' => null],
        ['title' => 'Communication Center', 'body' => 'Operational notification logs, templates, provider-ready channels, and safety boundaries.', 'href' => route('school.communications.index'), 'feature' => 'communication.logs.view'],
        ['title' => 'Bulk Communication', 'body' => 'Send school-scoped operational messages.', 'href' => route('school.communications.bulk'), 'feature' => 'communication.bulk'],
        ['title' => 'Branding / White Label', 'body' => 'School display name, logo, colors, portal wording, report footer, and powered-by boundary.', 'href' => route('school.branding.edit'), 'feature' => null],
        ['title' => 'Promotions', 'body' => 'Move students across sessions without losing history.', 'href' => route('school.student-promotions.index'), 'feature' => 'student.promote'],
        ['title' => 'User Management', 'body' => 'Staff accounts, roles, and feature access.', 'href' => route('school.staff.index'), 'feature' => null],
    ];
@endphp

<div class="space-y-6">
    <h2 class="sr-only">School Admin Dashboard</h2>

    @if ($standaloneSummary)
        <x-standalone-dashboard-summary
            :summary="$standaloneSummary"
            title="Standalone school readiness"
        />
    @endif

    <section class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
        <x-ui.panel class="min-w-0">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">School setup overview</p>
                    <h3 class="mt-2 text-2xl font-semibold text-text-primary">{{ $dashboardBrandName }}</h3>
                    <p class="mt-2 text-sm leading-6 text-text-secondary">
                        {{ $dashboardHeading }}. Session {{ $activeSession?->name ?? 'not set' }} and term {{ $activeTerm?->name ?? 'not set' }} are used for the current school work.
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-3 rounded-lg border border-border-subtle bg-bg-primary px-4 py-3">
                    @if ($dashboardLogo)
                        <img src="{{ $dashboardLogo }}" alt="{{ $dashboardBrandName }} logo" class="h-11 w-11 rounded-md bg-white object-contain p-1">
                    @else
                        <span class="flex h-11 w-11 items-center justify-center rounded-md bg-brand-primary text-sm font-bold text-white">{{ $dashboardInitials }}</span>
                    @endif
                    <div>
                        <p class="text-sm font-semibold text-text-primary">{{ ucfirst($school->status) }}</p>
                        <p class="text-xs text-text-tertiary">{{ $schoolStatusLabel }}: {{ ucfirst($school->subscription_status) }}</p>
                    </div>
                </div>
            </div>
        </x-ui.panel>

        <x-ui.panel>
            <p class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Attention queue</p>
            <div class="mt-4 space-y-3">
                @forelse ($attentionItems as $item)
                    <a href="{{ $item['href'] }}" class="flex items-center justify-between gap-3 rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-sm transition hover:border-border-hover hover:bg-bg-tertiary">
                        <span class="font-medium text-text-primary">{{ $item['label'] }}</span>
                        <span class="font-mono text-base font-semibold text-brand-primary">{{ $item['value'] }}</span>
                    </a>
                @empty
                    <x-ui.empty-state
                        title="No urgent school tasks"
                        body="When setup or result items need action, they will appear here. If this is a new school, start with profile, session, term, classes, subjects, staff, and students."
                        :action-href="route('onboarding.index')"
                        action-label="Open setup guide"
                        class="p-4 sm:p-5"
                    />
                @endforelse
            </div>
        </x-ui.panel>
    </section>

    @if (! $standaloneSummary && (! $activeSession || ! $activeTerm || $totalClasses === 0 || $totalSubjects === 0 || $totalStudents === 0))
        <x-ui.panel>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-text-primary">First setup steps</h3>
                    <p class="mt-1 text-sm leading-6 text-text-secondary">Add the basics first so teachers, result officers, students, and parents see the right information.</p>
                </div>
                <a href="{{ route('onboarding.index') }}" class="ui-button-primary">Open setup guide</a>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <a href="{{ route('school.profile.edit') }}" class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm font-semibold text-text-primary hover:bg-bg-tertiary">School profile</a>
                <a href="{{ route('school.sessions.index') }}" class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm font-semibold text-text-primary hover:bg-bg-tertiary">Session</a>
                <a href="{{ route('school.terms.index') }}" class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm font-semibold text-text-primary hover:bg-bg-tertiary">Term</a>
                <a href="{{ route('school.classes.index') }}" class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm font-semibold text-text-primary hover:bg-bg-tertiary">Classes</a>
                <a href="{{ route('school.students.index') }}" class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm font-semibold text-text-primary hover:bg-bg-tertiary">Students</a>
            </div>
        </x-ui.panel>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Students" :value="$totalStudents" :meta="$totalSchoolUsers . ' users in school scope'" />
        <x-ui.stat-card label="Results" :value="$totalResults" :meta="$publishedResults . ' published, ' . $draftResults . ' draft'" tone="info" />
        <x-ui.stat-card label="Finance" :value="$financeSummary['invoices'] ?? 0" :meta="'Balance: NGN ' . number_format($financeSummary['total_balance'] ?? 0, 2)" tone="warning" />
        <x-ui.stat-card label="Scratch Cards" :value="$totalScratchCardRequests" :meta="$unusedScratchCards . ' unused / ' . $usedScratchCards . ' used'" />
        <x-ui.stat-card label="Academic Setup" :value="$totalClasses . ' / ' . $totalSubjects" :meta="$totalSessions . ' sessions, ' . $totalTerms . ' terms'" />
        <x-ui.stat-card label="LMS" value="Online" meta="Materials plus CBT activity links" tone="info" :href="route('school.lms.index')" />
        <x-ui.stat-card label="Live Classes" :value="$upcomingLiveClasses" :meta="$totalLiveClasses . ' total manual provider sessions'" tone="info" :href="route('school.live-classes.index')" />
        <x-ui.stat-card label="Communications" value="Ready" meta="Logs, templates, and deferred provider channels" tone="info" :href="route('school.communications.index')" />
        <x-ui.stat-card label="Branding" :value="$brandingConfigured ? 'Configured' : 'Review'" meta="Logo, colors, reports, and powered-by boundary" tone="info" :href="route('school.branding.edit')" />
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <x-ui.panel>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-text-primary">Result lifecycle</h3>
                    <p class="mt-1 text-sm text-text-secondary">Current score records by workflow state.</p>
                </div>
                @schoolFeature('results.publish')
                    <a href="{{ route('school.results.publishing.index') }}" class="ui-button-secondary">Open</a>
                @endschoolFeature
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <x-ui.stat-card label="Draft" :value="$draftResults" tone="warning" class="p-4" />
                <x-ui.stat-card label="Reviewed" :value="$reviewedResults" tone="info" class="p-4" />
                <x-ui.stat-card label="Published" :value="$publishedResults" tone="success" class="p-4" />
            </div>
        </x-ui.panel>

        <x-ui.panel>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-text-primary">Scratch card inventory</h3>
                    <p class="mt-1 text-sm text-text-secondary">Access cards by request and card status.</p>
                </div>
                <a href="{{ route('school.scratch-cards.index') }}" class="ui-button-secondary">Open</a>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <x-ui.stat-card label="Generated" :value="$generatedScratchCardRequests" :meta="$unusedScratchCards . ' unused'" class="p-4" />
                <x-ui.stat-card label="Used" :value="$usedScratchCards" meta="Cards consumed" class="p-4" />
                <x-ui.stat-card label="Revoked" :value="$revokedScratchCardRequests" :meta="$revokedScratchCards . ' cards'" class="p-4" />
            </div>
        </x-ui.panel>
    </section>

    <section>
        <div class="mb-4 flex flex-col gap-1">
            <h3 class="text-lg font-semibold text-text-primary">School tools</h3>
            <p class="text-sm text-text-secondary">Only tools available to this school role are shown.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($moduleCards as $card)
                @if (! $card['feature'] || app(\App\Services\SchoolAuthorizationService::class)->can(auth()->user(), $school, $card['feature']))
                    <a href="{{ $card['href'] }}" class="ui-card ui-card-hover block p-5 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary">
                        <h4 class="text-base font-semibold text-text-primary">{{ $card['title'] }}</h4>
                        <p class="mt-2 text-sm leading-6 text-text-secondary">{{ $card['body'] }}</p>
                        <p class="mt-4 text-xs font-semibold uppercase tracking-normal text-text-tertiary">Open module</p>
                    </a>
                @endif
            @endforeach
        </div>
    </section>
</div>
