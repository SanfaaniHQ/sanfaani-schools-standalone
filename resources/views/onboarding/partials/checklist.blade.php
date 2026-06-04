<section class="space-y-3">
    @forelse ($steps as $step)
        @include('onboarding.partials.step', [
            'step' => $step,
            'record' => $records->get($step->id),
        ])
    @empty
        <x-ui.empty-state title="No onboarding steps are available" body="There is no setup checklist for this role right now. Use the dashboard to continue, or contact Sanfaani support if you expected onboarding steps." />
    @endforelse
</section>
