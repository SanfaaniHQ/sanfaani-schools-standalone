<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Standalone Status"
            description="Local-first school installation status, installer readiness, and safe sync foundation."
        />
    </x-slot>

    @php
        $yesNo = fn (bool $value) => $value ? 'Enabled' : 'Disabled';
        $configured = fn (bool $value) => $value ? 'Configured' : 'Missing';
        $lastSync = $syncStatus['last_sync'];
    @endphp

    <div class="space-y-6">
        <x-ui.alert
            tone="warning"
            title="Browser offline/PWA is not complete"
            body="This foundation supports local-first deployment on a school computer, LAN server, VPS, or cPanel account where the database is local. Full browser offline/PWA behavior is a later phase."
        />

        @foreach ($editionStatus['warnings'] as $warning)
            <x-ui.alert tone="warning" :body="$warning" />
        @endforeach

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card
                label="Product edition"
                :value="$editionStatus['product_label']"
                :meta="'Mode: '.$editionStatus['deployment_mode']"
                tone="brand"
            />
            <x-ui.stat-card
                label="Installer"
                :value="$yesNo($editionStatus['installer_enabled'])"
                :meta="$editionStatus['installed'] ? 'Installed lock/config present' : 'Ready for fresh installation'"
                :tone="$editionStatus['installer_enabled'] ? 'success' : 'warning'"
            />
            <x-ui.stat-card
                label="License mode"
                :value="$editionStatus['license_mode']"
                :meta="'Recommended: '.$editionStatus['recommended_license_mode']"
            />
            <x-ui.stat-card
                label="Sync"
                :value="$yesNo($editionStatus['sync_enabled'])"
                :meta="$configured($editionStatus['sync_endpoint_configured']).' endpoint'"
                :tone="$editionStatus['sync_enabled'] ? 'info' : 'neutral'"
            />
        </section>

        <x-ui.table-card
            title="Standalone Configuration"
            description="Read-only local-first and sync settings. Secrets are never displayed."
        >
            <table class="enterprise-table">
                <tbody>
                    <tr>
                        <th scope="row" class="w-72 px-5 py-4 text-left text-sm font-medium text-text-secondary">Product edition</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $editionStatus['product_label'] }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-4 text-left text-sm font-medium text-text-secondary">Deployment mode</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $editionStatus['deployment_mode'] }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-4 text-left text-sm font-medium text-text-secondary">Installer</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $yesNo($editionStatus['installer_enabled']) }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-4 text-left text-sm font-medium text-text-secondary">Installed status</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $editionStatus['installed'] ? 'Installed' : 'Not installed' }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-4 text-left text-sm font-medium text-text-secondary">License mode</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $editionStatus['license_mode'] }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-4 text-left text-sm font-medium text-text-secondary">Offline mode</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $editionStatus['offline_mode'] }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-4 text-left text-sm font-medium text-text-secondary">Cloud sync</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $yesNo($editionStatus['sync_enabled']) }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-4 text-left text-sm font-medium text-text-secondary">Sync endpoint</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $editionStatus['sync_endpoint_configured'] ? $editionStatus['sync_endpoint'] : 'Missing' }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-4 text-left text-sm font-medium text-text-secondary">Last sync status</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">
                            {{ $lastSync ? str($lastSync['status'])->replace('_', ' ')->title() : 'No sync has run yet' }}
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-4 text-left text-sm font-medium text-text-secondary">Backup sync status</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $yesNo($editionStatus['backup_sync_enabled']) }}</td>
                    </tr>
                </tbody>
            </table>
        </x-ui.table-card>

        <section class="grid gap-4 lg:grid-cols-3">
            <x-ui.panel
                title="Sync Foundation"
                description="Outbox readiness only. No external API call or local data deletion is performed in this stage."
            >
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-text-secondary">Tables</dt>
                        <dd class="font-semibold text-text-primary">{{ $syncStatus['tables_ready'] ? 'Ready' : 'Not migrated' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-text-secondary">Pending items</dt>
                        <dd class="font-mono font-semibold text-text-primary">{{ $syncStatus['pending_count'] ?? 0 }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-text-secondary">Failed items</dt>
                        <dd class="font-mono font-semibold text-text-primary">{{ $syncStatus['failed_count'] ?? 0 }}</dd>
                    </div>
                </dl>
            </x-ui.panel>

            <x-ui.panel
                class="lg:col-span-2"
                title="Pending Outbox Preview"
                description="First pending local records that would be considered by a dry-run sync."
            >
                @if ($pendingOutboxItems->isEmpty())
                    <x-ui.empty-state
                        title="No pending sync items"
                        body="The foundation is ready, but no model capture is enabled yet."
                    />
                @else
                    <div class="overflow-x-auto">
                        <table class="enterprise-table">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 text-left">Entity</th>
                                    <th class="px-5 py-3 text-left">Action</th>
                                    <th class="px-5 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingOutboxItems as $item)
                                    <tr>
                                        <td class="px-5 py-4 text-sm text-text-primary">{{ $item->entity_type }} #{{ $item->entity_id ?? 'local' }}</td>
                                        <td class="px-5 py-4 text-sm text-text-secondary">{{ $item->action }}</td>
                                        <td class="px-5 py-4 text-sm">
                                            <x-ui.badge tone="warning">{{ str($item->status)->title() }}</x-ui.badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-ui.panel>
        </section>
    </div>
</x-app-layout>
