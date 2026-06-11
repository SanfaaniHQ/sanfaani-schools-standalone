<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Standalone Status"
            description="Local-first school installation status, system health, scheduler monitoring, and safe sync foundation."
        />
    </x-slot>

    @php
        $yesNo = fn (bool $value) => $value ? 'Enabled' : 'Disabled';
        $configured = fn (bool $value) => $value ? 'Configured' : 'Missing';
        $lastSync = $syncStatus['last_sync'];
        $healthTone = $systemHealth['overall']['tone'] ?? 'info';
        $contextValue = function (mixed $value): string {
            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }

            if (is_array($value)) {
                return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: 'Unknown';
            }

            if ($value === null || $value === '') {
                return 'Unknown';
            }

            return (string) $value;
        };
    @endphp

    <div class="space-y-6">
        <x-ui.alert
            tone="warning"
            title="Attendance-only browser offline pilot"
            :body="$editionStatus['offline_attendance_sync_enabled']
                ? 'Offline attendance capture and its authenticated sync endpoint are enabled. Full portal offline mode is not implemented, and browser-local pending records are invisible to the server until sync.'
                : 'Offline attendance capture and sync are disabled by default. Full portal offline mode is not implemented, and browser-local pending records are invisible to the server until sync.'"
        />

        @foreach ($editionStatus['warnings'] as $warning)
            <x-ui.alert tone="warning" :body="$warning" />
        @endforeach

        <x-ui.alert
            :tone="$healthTone"
            title="System health summary"
            :body="$systemHealth['overall']['message']"
        />

        <x-ui.alert
            tone="info"
            title="Safe output rules"
            body="This page shows statuses, counts, relative paths, and configured/missing flags only. Database passwords, .env values, license secrets, sync tokens, API keys, and private backup paths are not displayed."
        />

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
                :meta="$configured($editionStatus['sync_endpoint_configured']).' endpoint / '.$configured($editionStatus['sync_token_configured']).' token'"
                :tone="$editionStatus['sync_enabled'] ? 'info' : 'neutral'"
            />
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @foreach ($systemHealth['cards'] as $card)
                <x-ui.stat-card
                    :label="$card['label']"
                    :value="$card['value']"
                    :meta="$card['meta']"
                    :tone="$card['tone']"
                />
            @endforeach
        </section>

        <x-ui.panel
            title="Health Check Totals"
            description="Read-only summary generated from Laravel, database, storage, queue, scheduler, license, backup, update, installer, and sync checks."
        >
            <dl class="grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    <dt class="text-text-secondary">Passing</dt>
                    <dd class="mt-1 font-mono text-2xl font-semibold text-emerald-700 dark:text-emerald-300">{{ $systemHealth['summary']['pass'] }}</dd>
                </div>
                <div>
                    <dt class="text-text-secondary">Warnings</dt>
                    <dd class="mt-1 font-mono text-2xl font-semibold text-amber-700 dark:text-amber-300">{{ $systemHealth['summary']['warning'] }}</dd>
                </div>
                <div>
                    <dt class="text-text-secondary">Blocking</dt>
                    <dd class="mt-1 font-mono text-2xl font-semibold text-rose-700 dark:text-rose-300">{{ $systemHealth['summary']['fail'] }}</dd>
                </div>
                <div>
                    <dt class="text-text-secondary">Informational</dt>
                    <dd class="mt-1 font-mono text-2xl font-semibold text-text-primary">{{ $systemHealth['summary']['info'] }}</dd>
                </div>
                <div>
                    <dt class="text-text-secondary">Generated</dt>
                    <dd class="mt-1 text-sm font-semibold text-text-primary">{{ \Illuminate\Support\Carbon::parse($systemHealth['generated_at'])->diffForHumans() }}</dd>
                </div>
            </dl>
        </x-ui.panel>

        @foreach ($systemHealth['sections'] as $section)
            <x-ui.table-card
                :title="$section['label']"
                description="Safe diagnostic summary. Secrets and raw environment values are not printed."
            >
                <table class="enterprise-table">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 text-left">Check</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left">Message</th>
                            <th class="px-5 py-3 text-left">Safe details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($section['checks'] as $check)
                            <tr>
                                <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $check['label'] }}</td>
                                <td class="px-5 py-4 text-sm">
                                    <x-ui.badge :tone="$check['tone']">{{ str($check['status'])->title() }}</x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-sm text-text-secondary">{{ $check['message'] }}</td>
                                <td class="px-5 py-4 text-xs text-text-secondary">
                                    @if (empty($check['context']))
                                        No additional safe details.
                                    @else
                                        <dl class="space-y-1">
                                            @foreach ($check['context'] as $key => $value)
                                                <div class="flex justify-between gap-3">
                                                    <dt class="font-medium">{{ str((string) $key)->replace('_', ' ')->title() }}</dt>
                                                    <dd class="text-right font-mono text-text-primary">{{ $contextValue($value) }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-ui.table-card>
        @endforeach

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
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $configured($editionStatus['sync_endpoint_configured']) }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-4 text-left text-sm font-medium text-text-secondary">Sync token</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $configured($editionStatus['sync_token_configured']) }}</td>
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
                    <tr>
                        <th scope="row" class="px-5 py-4 text-left text-sm font-medium text-text-secondary">Offline attendance capture</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $yesNo($editionStatus['offline_attendance_capture_enabled']) }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-4 text-left text-sm font-medium text-text-secondary">Offline attendance sync endpoint</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $yesNo($editionStatus['offline_attendance_sync_enabled']) }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-4 text-left text-sm font-medium text-text-secondary">Offline modules allowed</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ implode(', ', $editionStatus['pwa_offline_allowed_modules']) ?: 'None' }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="px-5 py-4 text-left text-sm font-medium text-text-secondary">Full portal offline</th>
                        <td class="px-5 py-4 text-sm font-semibold text-text-primary">Not implemented</td>
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
