<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Reports Center"
            :description="'Consolidated school-scoped summaries for '.$school->name"
            eyebrow="Stage 20 Reports Pack"
        >
            <x-slot name="actions">
                <a href="{{ route('school.dashboard') }}" class="ui-button-secondary">Dashboard</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-6">
        @include('school.reports.partials.filter-form', [
            'filters' => $filters,
            'filterOptions' => $filterOptions,
        ])

        <x-ui.panel tone="info">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <h3 class="text-base font-semibold text-text-primary">School-Wide Overview</h3>
                    <p class="mt-1 text-sm leading-6 text-text-secondary">
                        These summaries reuse existing module records and protected report routes. They are read-only, school-scoped, and intentionally aggregate-only.
                    </p>
                </div>
                <x-ui.badge tone="brand">Reports Pack Available</x-ui.badge>
            </div>
        </x-ui.panel>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($report['summary_cards'] as $card)
                @include('school.reports.partials.summary-card', ['card' => $card])
            @endforeach
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            @foreach ($report['groups'] as $group)
                <x-ui.panel :title="$group['title']" :description="$group['description']">
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($group['cards'] as $card)
                            @include('school.reports.partials.summary-card', [
                                'card' => $card,
                                'class' => 'p-4 sm:p-5',
                            ])
                        @endforeach
                    </div>

                    @if ($group['links'] !== [])
                        <div class="mt-4 border-t border-border-subtle pt-4">
                            <p class="text-xs font-semibold uppercase tracking-normal text-text-secondary">Detailed Reports</p>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                @foreach ($group['links'] as $link)
                                    @include('school.reports.partials.module-card', ['link' => $link])
                                @endforeach
                            </div>
                        </div>
                    @endif
                </x-ui.panel>
            @endforeach
        </section>

        <section class="grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
            <x-ui.panel
                title="Existing Export Links"
                description="Only existing protected CSV tools are linked here."
            >
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($report['export_links'] as $link)
                        @include('school.reports.partials.module-card', ['link' => $link])
                    @endforeach
                </div>
            </x-ui.panel>

            <x-ui.panel
                title="Privacy Boundaries"
                description="The reports center is intentionally limited to safe summaries."
            >
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($report['privacy_notes'] as $note)
                        <div class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm leading-6 text-text-secondary">
                            {{ $note }}
                        </div>
                    @endforeach
                </div>
            </x-ui.panel>
        </section>
    </div>
</x-app-layout>
