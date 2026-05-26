@php
    $attentionItems = collect([
        ['label' => 'Pending scratch card payments', 'value' => $pendingScratchCardRequests, 'tone' => 'warning', 'href' => route('school.scratch-cards.index')],
        ['label' => 'Draft result records', 'value' => $draftResults, 'tone' => 'warning', 'href' => route('school.results.manual.index')],
        ['label' => 'Reviewed results waiting publish', 'value' => $reviewedResults, 'tone' => 'success', 'href' => route('school.results.publishing.index')],
        ['label' => 'Setup gaps', 'value' => ($activeSession ? 0 : 1) + ($activeTerm ? 0 : 1) + ($totalClasses > 0 ? 0 : 1) + ($totalSubjects > 0 ? 0 : 1), 'tone' => 'danger', 'href' => route('school.profile.edit')],
    ])->filter(fn ($item) => (int) $item['value'] > 0);

    $moduleCards = [
        ['title' => 'Students', 'body' => 'Enrollment records and Student 360 profiles.', 'href' => route('school.students.index'), 'feature' => 'students.view'],
        ['title' => 'Classes', 'body' => 'Class arms and academic grouping.', 'href' => route('school.classes.index'), 'feature' => null],
        ['title' => 'Subjects', 'body' => 'Subject catalog and class assignments.', 'href' => route('school.subjects.index'), 'feature' => null],
        ['title' => 'Sessions', 'body' => 'Academic years, current session, and archives.', 'href' => route('school.sessions.index'), 'feature' => null],
        ['title' => 'Terms', 'body' => 'Operational terms attached to sessions.', 'href' => route('school.terms.index'), 'feature' => null],
        ['title' => 'Results', 'body' => 'Manual entry, upload, review, and publishing.', 'href' => route('school.result-system.index'), 'feature' => 'results.manual_entry'],
        ['title' => 'Scratch Cards', 'body' => 'Batches, card inventory, and result access.', 'href' => route('school.scratch-cards.index'), 'feature' => null],
        ['title' => 'Bulk Communication', 'body' => 'Send school-scoped operational messages.', 'href' => route('school.communications.bulk'), 'feature' => 'communication.bulk'],
        ['title' => 'Promotions', 'body' => 'Move students across sessions without losing history.', 'href' => route('school.student-promotions.index'), 'feature' => 'student.promote'],
        ['title' => 'User Management', 'body' => 'Staff accounts, roles, and feature access.', 'href' => route('school.staff.index'), 'feature' => null],
    ];
@endphp

<div class="space-y-6">
    <h2 class="sr-only">School Admin Dashboard</h2>

    <section class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
        <x-ui.panel class="min-w-0">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">Operational status</p>
                    <h3 class="mt-2 text-2xl font-semibold text-text-primary">{{ $school->name }}</h3>
                    <p class="mt-2 text-sm leading-6 text-text-secondary">
                        Session {{ $activeSession?->name ?? 'not set' }} and term {{ $activeTerm?->name ?? 'not set' }} are driving the current dashboard.
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-3 rounded-lg border border-border-subtle bg-bg-primary px-4 py-3">
                    @if ($school->logoUrl())
                        <img src="{{ $school->logoUrl() }}" alt="{{ $school->name }} logo" class="h-11 w-11 rounded-md bg-white object-contain p-1">
                    @else
                        <span class="flex h-11 w-11 items-center justify-center rounded-md bg-brand-primary text-sm font-bold text-white">{{ $school->initials() }}</span>
                    @endif
                    <div>
                        <p class="text-sm font-semibold text-text-primary">{{ ucfirst($school->status) }}</p>
                        <p class="text-xs text-text-tertiary">{{ ucfirst($school->subscription_status) }} subscription</p>
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
                        title="No critical blockers"
                        body="No critical operational blockers are visible right now."
                        class="p-4 sm:p-5"
                    />
                @endforelse
            </div>
        </x-ui.panel>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Students" :value="$totalStudents" :meta="$totalSchoolUsers . ' users in school scope'" />
        <x-ui.stat-card label="Results" :value="$totalResults" :meta="$publishedResults . ' published, ' . $draftResults . ' draft'" tone="info" />
        <x-ui.stat-card label="Scratch Cards" :value="$totalScratchCardRequests" :meta="$unusedScratchCards . ' unused / ' . $usedScratchCards . ' used'" />
        <x-ui.stat-card label="Academic Setup" :value="$totalClasses . ' / ' . $totalSubjects" :meta="$totalSessions . ' sessions, ' . $totalTerms . ' terms'" />
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
            <h3 class="text-lg font-semibold text-text-primary">Operational modules</h3>
            <p class="text-sm text-text-secondary">Only modules available to this school role are shown.</p>
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
