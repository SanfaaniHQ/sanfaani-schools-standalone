<x-ui.panel>
    <h3 class="text-base font-semibold text-text-primary">Verification</h3>
    @if ($verification)
        <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="text-sm text-text-secondary">Status</dt>
                <dd class="mt-1 font-semibold text-text-primary">{{ str($verification->status)->replace('_', ' ')->title() }}</dd>
            </div>
            <div>
                <dt class="text-sm text-text-secondary">Checked</dt>
                <dd class="mt-1 font-semibold text-text-primary">{{ $verification->checked_at?->format('d M Y H:i') ?? 'Not checked' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-text-secondary">Checksum</dt>
                <dd class="mt-1 font-semibold text-text-primary">{{ $verification->checksum_valid === null ? 'Not available' : ($verification->checksum_valid ? 'Valid' : 'Invalid') }}</dd>
            </div>
            <div>
                <dt class="text-sm text-text-secondary">Required items</dt>
                <dd class="mt-1 font-semibold text-text-primary">{{ $verification->required_items_present ? 'Present' : 'Review needed' }}</dd>
            </div>
        </dl>
        <p class="mt-4 text-sm text-text-secondary">{{ $verification->message }}</p>
    @else
        <p class="mt-2 text-sm text-text-secondary">No verification has been recorded yet.</p>
    @endif
</x-ui.panel>
