@props(['summary' => null, 'compact' => false])

@php
    $summary ??= null;

    if ($summary === null && auth()->check() && config('onboarding.progress_widget_enabled', true)) {
        try {
            $currentSchool = app(\App\Services\CurrentSchoolService::class);
            $user = auth()->user();
            $summary = app(\App\Services\Onboarding\OnboardingChecklistService::class)
                ->summaryFor($user, $currentSchool->get($user));
        } catch (\Throwable) {
            $summary = ['available' => false];
        }
    }
@endphp

@if (($summary['available'] ?? false) && config('onboarding.progress_widget_enabled', true))
    <x-ui.panel>
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">Guided onboarding</p>
                <h3 class="mt-2 text-base font-semibold text-text-primary">{{ $summary['checklist']->name }}</h3>
                @unless ($compact)
                    <p class="mt-1 text-sm text-text-secondary">{{ $summary['progress']['completed'] }} of {{ $summary['progress']['total'] }} setup steps complete. Continue when you are ready to add school data.</p>
                @endunless
            </div>
            <span class="font-mono text-lg font-semibold text-brand-primary">{{ $summary['progress']['percent'] }}%</span>
        </div>
        <div class="mt-4 h-2 overflow-hidden rounded-full bg-bg-tertiary">
            <div class="h-full rounded-full bg-brand-primary" style="width: {{ $summary['progress']['percent'] }}%"></div>
        </div>
        <a href="{{ route('onboarding.index') }}" class="mt-4 inline-flex text-sm font-semibold text-brand-primary">Continue setup</a>
    </x-ui.panel>
@endif
