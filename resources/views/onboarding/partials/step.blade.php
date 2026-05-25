@php
    $status = $record?->status ?? \App\Models\UserOnboardingProgress::STATUS_PENDING;
    $completed = $status === \App\Models\UserOnboardingProgress::STATUS_COMPLETED;
    $skipped = $status === \App\Models\UserOnboardingProgress::STATUS_SKIPPED;
    $actionUrl = $step->resolvedActionUrl();
@endphp

<article class="rounded-lg border border-border-subtle bg-bg-secondary p-5 shadow-sm">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="text-base font-semibold text-text-primary">{{ $step->title }}</h3>
                <x-status-badge :status="$status" />
                @if (! $step->required)
                    <span class="rounded-full bg-bg-tertiary px-2 py-1 text-xs font-semibold text-text-tertiary">Optional</span>
                @endif
            </div>
            @if ($step->description)
                <p class="mt-2 text-sm leading-6 text-text-secondary">{{ $step->description }}</p>
            @endif
        </div>

        <div class="flex shrink-0 flex-wrap gap-2">
            @if ($actionUrl && $actionUrl !== '#')
                <a href="{{ $actionUrl }}" class="ui-button-secondary">{{ $step->action_label ?: 'Open' }}</a>
            @endif
            @unless ($completed)
                <form method="POST" action="{{ route('onboarding.steps.complete', $step) }}">
                    @csrf
                    <button class="ui-button-primary">Complete</button>
                </form>
            @endunless
            @if (! $completed && ! $skipped)
                <form method="POST" action="{{ route('onboarding.steps.skip', $step) }}">
                    @csrf
                    <button class="rounded-md border border-border-subtle px-3 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-tertiary">Skip</button>
                </form>
            @endif
        </div>
    </div>
</article>
