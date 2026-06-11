@php
    $attentionItems = collect([
        ['label' => 'Returned for correction', 'value' => $returnedResults, 'href' => route('school.teacher-results.index', ['status' => 'returned'])],
        ['label' => 'Draft submissions', 'value' => $draftResults, 'href' => route('school.teacher-results.index', ['status' => 'draft'])],
        ['label' => 'Submitted for review', 'value' => $submittedResults, 'href' => route('school.teacher-results.index', ['status' => 'submitted'])],
    ])->filter(fn ($item) => (int) $item['value'] > 0);
@endphp

<div class="space-y-6">
    <section class="grid gap-4 lg:grid-cols-[1fr_24rem]">
        <x-ui.panel>
            <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">Assigned academic work</p>
            <h3 class="mt-2 text-2xl font-semibold text-text-primary">Welcome back, {{ auth()->user()->name }}</h3>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-text-secondary">
                You will only see the classes, subjects, students, and result tasks assigned to you.
            </p>
        </x-ui.panel>

        <x-ui.panel>
            <p class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">Current period</p>
            <p class="mt-3 text-lg font-semibold text-text-primary">{{ $activeTerm?->name ?? 'No active term' }}</p>
            <p class="mt-1 text-sm text-text-secondary">{{ $activeSession?->name ?? 'No active session' }}</p>
        </x-ui.panel>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Assigned Classes" :value="$totalAssignedClasses" :meta="$totalAssignedStudents . ' students'" />
        <x-ui.stat-card label="Assigned Subjects" :value="$totalAssignedSubjects" meta="Active assignments" />
        <x-ui.stat-card label="Draft Results" :value="$draftResults" meta="Saved drafts" tone="warning" />
        <x-ui.stat-card label="Submitted Results" :value="$submittedResults" meta="Under review" tone="info" />
    </section>

    <section class="grid gap-4 lg:grid-cols-3">
        <x-ui.panel class="lg:col-span-1">
            <h3 class="text-base font-semibold text-text-primary">Attention queue</h3>
            <div class="mt-4 space-y-3">
                @forelse ($attentionItems as $item)
                    <a href="{{ $item['href'] }}" class="flex items-center justify-between rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-sm transition hover:border-border-hover hover:bg-bg-tertiary">
                        <span class="font-medium text-text-primary">{{ $item['label'] }}</span>
                        <span class="font-mono text-base font-semibold text-brand-primary">{{ $item['value'] }}</span>
                    </a>
                @empty
                    <x-ui.empty-state
                        title="No pending result work"
                        body="No returned or pending result work is visible. When your school assigns classes or opens result entry, the next task will appear here."
                        class="p-4 sm:p-5"
                    />
                @endforelse
            </div>
        </x-ui.panel>

        <x-ui.panel class="lg:col-span-2">
            <h3 class="text-base font-semibold text-text-primary">Teacher tools</h3>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @if(($features['teacher.assignments.view']['enabled'] ?? true))
                    <a href="{{ route('school.teacher-assignments.my') }}" class="ui-card ui-card-hover block p-4">
                        <span class="font-semibold text-text-primary">My Classes & Subjects</span>
                        <span class="mt-1 block text-sm text-text-secondary">Review current class and subject assignments.</span>
                    </a>
                @endif
                @if(($features['teacher.results.create']['enabled'] ?? true))
                    <a href="{{ route('school.teacher-results.create') }}" class="ui-card ui-card-hover block p-4">
                        <span class="font-semibold text-text-primary">Enter Results</span>
                        <span class="mt-1 block text-sm text-text-secondary">Create scores only for assigned scopes.</span>
                    </a>
                @endif
                @if(($features['students.view_assigned']['enabled'] ?? true))
                    <a href="{{ route('school.students.index') }}" class="ui-card ui-card-hover block p-4">
                        <span class="font-semibold text-text-primary">Assigned Students</span>
                        <span class="mt-1 block text-sm text-text-secondary">Open Student 360 for visible classes.</span>
                    </a>
                @endif
                @if(($features['attendance.view']['enabled'] ?? true))
                    <a href="{{ route('school.attendance.index') }}" class="ui-card ui-card-hover block p-4">
                        <span class="font-semibold text-text-primary">Attendance</span>
                        <span class="mt-1 block text-sm text-text-secondary">Mark or review attendance for active class assignments only.</span>
                    </a>
                @endif
                @if(($features['lms.view']['enabled'] ?? true))
                    <a href="{{ route('school.lms.index') }}" class="ui-card ui-card-hover block p-4">
                        <span class="font-semibold text-text-primary">Learning Materials</span>
                        <span class="mt-1 block text-sm text-text-secondary">Post lessons and resources only for assigned classes and subjects.</span>
                    </a>
                @endif
                @if(($features['cbt.question_bank']['enabled'] ?? false))
                    <a href="{{ route('school.cbt.question-banks.index') }}" class="ui-card ui-card-hover block p-4">
                        <span class="font-semibold text-text-primary">CBT Question Bank</span>
                        <span class="mt-1 block text-sm text-text-secondary">Prepare reusable questions for assigned academic work.</span>
                    </a>
                @endif
                @if(($features['cbt.mark_theory']['enabled'] ?? false))
                    <a href="{{ route('school.cbt.marking.index') }}" class="ui-card ui-card-hover block p-4">
                        <span class="font-semibold text-text-primary">CBT Theory Marking</span>
                        <span class="mt-1 block text-sm text-text-secondary">Review and score theory answers awaiting teacher input.</span>
                    </a>
                @endif
            </div>
        </x-ui.panel>
    </section>
</div>
