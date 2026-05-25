<section class="space-y-3">
    @forelse ($steps as $step)
        @include('onboarding.partials.step', [
            'step' => $step,
            'record' => $records->get($step->id),
        ])
    @empty
        <x-ui.empty-state title="No onboarding steps are available" body="This role does not have visible steps in the current deployment and license context." />
    @endforelse
</section>
