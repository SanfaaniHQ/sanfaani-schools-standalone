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

        @if ($verification->context)
            <div class="mt-4 rounded-md border border-border-subtle bg-bg-primary p-4">
                <h4 class="text-sm font-semibold text-text-primary">Safe verification details</h4>
                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="text-text-secondary">Metadata file</dt>
                        <dd class="mt-1 font-semibold text-text-primary">{{ data_get($verification->context, 'metadata_file_readable') ? 'Readable' : 'Review needed' }}</dd>
                    </div>
                    <div>
                        <dt class="text-text-secondary">Metadata size</dt>
                        <dd class="mt-1 font-mono text-text-primary">{{ number_format((int) data_get($verification->context, 'metadata_file_size_bytes', 0)) }} bytes</dd>
                    </div>
                    <div>
                        <dt class="text-text-secondary">Manifest consistency</dt>
                        <dd class="mt-1 font-semibold text-text-primary">{{ data_get($verification->context, 'manifest_consistent') ? 'Passed' : 'Review needed' }}</dd>
                    </div>
                    <div>
                        <dt class="text-text-secondary">Checksum available</dt>
                        <dd class="mt-1 font-semibold text-text-primary">{{ data_get($verification->context, 'checksum_available') ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div>
                        <dt class="text-text-secondary">Warning items</dt>
                        <dd class="mt-1 font-mono text-text-primary">{{ count((array) data_get($verification->context, 'items_with_warnings', [])) }}</dd>
                    </div>
                </dl>
            </div>
        @endif
    @else
        <p class="mt-2 text-sm text-text-secondary">No verification has been recorded yet.</p>
    @endif
</x-ui.panel>
