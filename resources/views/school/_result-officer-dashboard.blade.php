@php
    $attentionItems = collect([
        ['label' => 'Teacher submissions awaiting review', 'value' => $submittedResults, 'href' => route('school.result-reviews.index')],
        ['label' => 'Returned results', 'value' => $returnedResults, 'href' => route('school.result-reviews.index', ['status' => 'returned'])],
        ['label' => 'Draft result records', 'value' => $draftResults, 'href' => route('school.results.manual.index')],
    ])->filter(fn ($item) => (int) $item['value'] > 0);
@endphp

<div class="space-y-6">
    <section class="grid gap-4 lg:grid-cols-[1fr_24rem]">
        <x-ui.panel>
            <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">Result operations</p>
            <h3 class="mt-2 text-2xl font-semibold text-text-primary">Welcome back, {{ auth()->user()->name }}</h3>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-text-secondary">
                This dashboard helps you enter, upload, review, and publish results when the School Admin has enabled those tools.
            </p>
        </x-ui.panel>

        <x-ui.panel>
            <p class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Current period</p>
            <p class="mt-3 text-lg font-semibold text-text-primary">{{ $activeTerm?->name ?? 'No active term' }}</p>
            <p class="mt-1 text-sm text-text-secondary">{{ $activeSession?->name ?? 'No active session' }}</p>
        </x-ui.panel>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Students" :value="$totalStudents" meta="Visible student records" />
        <x-ui.stat-card label="Draft Results" :value="$draftResults" meta="Pending entry" tone="warning" />
        <x-ui.stat-card label="Review Queue" :value="$submittedResults" meta="Awaiting review" tone="info" />
        <x-ui.stat-card label="Published Results" :value="$publishedResults" meta="Live to approved access" tone="success" />
    </section>

    <section class="grid gap-4 lg:grid-cols-3">
        <x-ui.panel>
            <h3 class="text-base font-semibold text-text-primary">Attention queue</h3>
            <div class="mt-4 space-y-3">
                @forelse ($attentionItems as $item)
                    <a href="{{ $item['href'] }}" class="flex items-center justify-between rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-sm transition hover:border-border-hover hover:bg-bg-tertiary">
                        <span class="font-medium text-text-primary">{{ $item['label'] }}</span>
                        <span class="font-mono text-base font-semibold text-brand-primary">{{ $item['value'] }}</span>
                    </a>
                @empty
                    <x-ui.empty-state
                        title="No result exceptions"
                        body="No result exceptions are visible right now. Submitted, returned, or draft result items will appear here when they need review."
                        class="p-4 sm:p-5"
                    />
                @endforelse
            </div>
        </x-ui.panel>

        <x-ui.panel class="lg:col-span-2">
            <h3 class="text-base font-semibold text-text-primary">Result officer tools</h3>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @if(($features['students.view']['enabled'] ?? true))
                    <a href="{{ route('school.students.index') }}" class="ui-card ui-card-hover block p-4">
                        <span class="font-semibold text-text-primary">Student 360</span>
                        <span class="mt-1 block text-sm text-text-secondary">Open academic profiles and result workspaces.</span>
                    </a>
                @endif
                @if(($features['results.upload']['enabled'] ?? true))
                    <a href="{{ route('school.results.upload.index') }}" class="ui-card ui-card-hover block p-4">
                        <span class="font-semibold text-text-primary">Result Upload</span>
                        <span class="mt-1 block text-sm text-text-secondary">Upload class-based result files.</span>
                    </a>
                @endif
                @if(($features['results.review']['enabled'] ?? true))
                    <a href="{{ route('school.result-reviews.index') }}" class="ui-card ui-card-hover block p-4">
                        <span class="font-semibold text-text-primary">Review Queue</span>
                        <span class="mt-1 block text-sm text-text-secondary">Return, review, or approve submissions.</span>
                    </a>
                @endif
                @if(($features['results.publish']['enabled'] ?? true))
                    <a href="{{ route('school.results.publishing.index') }}" class="ui-card ui-card-hover block p-4">
                        <span class="font-semibold text-text-primary">Publishing</span>
                        <span class="mt-1 block text-sm text-text-secondary">Publish or unpublish approved results.</span>
                    </a>
                @endif
                @if(($features['cbt.publish_results']['enabled'] ?? false))
                    <a href="{{ route('school.cbt.dashboard') }}" class="ui-card ui-card-hover block p-4">
                        <span class="font-semibold text-text-primary">CBT Results</span>
                        <span class="mt-1 block text-sm text-text-secondary">Review CBT attempts and publish approved CBT outcomes.</span>
                    </a>
                @endif
            </div>
        </x-ui.panel>
    </section>

    @if(($upcomingParticipantLiveClasses ?? collect())->isNotEmpty())
        <x-ui.panel title="Live Classes" description="Participant invitations assigned to your account.">
            <div class="grid gap-3 lg:grid-cols-3">
                @foreach ($upcomingParticipantLiveClasses as $liveClass)
                    <a href="{{ route('portal.live-classes.show', $liveClass) }}" class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm transition hover:border-border-hover hover:bg-bg-tertiary">
                        <span class="block font-semibold text-text-primary">{{ $liveClass->title }}</span>
                        <span class="mt-1 block text-text-secondary">{{ $liveClass->starts_at?->format('d M Y H:i') }}</span>
                        <span class="mt-1 block text-xs text-text-tertiary">{{ $liveClass->schoolClass?->name }} {{ $liveClass->schoolClass?->section }}</span>
                    </a>
                @endforeach
            </div>
        </x-ui.panel>
    @endif
</div>

<div class="rounded-2xl border border-border-subtle bg-bg-secondary p-5 shadow-sm">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <p class="text-sm font-medium text-text-secondary">Result Access Requests</p>
            <p class="mt-2 text-3xl font-bold text-text-primary">{{ number_format($pendingResultAccessRequests ?? 0) }}</p>
            <p class="mt-1 text-sm text-text-secondary">Pending parent and student result access approvals.</p>
        </div>

        <a href="{{ route('school.result-access-requests.index') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 sm:w-auto">
            Review Requests
        </a>
    </div>
</div>
