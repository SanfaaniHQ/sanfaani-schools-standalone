<x-ui.panel>
    <h3 class="text-base font-semibold text-text-primary">Backup items</h3>
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-border-subtle text-sm">
            <thead class="bg-bg-tertiary text-xs uppercase text-text-tertiary">
                <tr>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Label</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Reference</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-subtle">
                @forelse ($items as $item)
                    @php
                        $reference = $item->path ? str_replace([base_path(), storage_path()], ['[app]', '[storage]'], $item->path) : 'Metadata only';
                    @endphp
                    <tr>
                        <td class="px-4 py-3 font-semibold text-text-primary">{{ str($item->item_type)->title() }}</td>
                        <td class="px-4 py-3 text-text-secondary">{{ $item->source_label }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$item->status" /></td>
                        <td class="px-4 py-3 font-mono text-xs text-text-tertiary">{{ $reference }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-text-secondary">No backup items recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.panel>
