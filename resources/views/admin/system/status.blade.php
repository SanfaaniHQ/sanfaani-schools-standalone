<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="System Status"
            description="Read-only deployment, license, feature, and behavior configuration for this installation."
        />
    </x-slot>

    @php
        $behavior = app(\App\Services\System\DeploymentBehaviorService::class);
        $listLabels = fn (array $items) => collect($items)
            ->map(fn (string $item) => str($item)->replace('_', ' ')->title())
            ->implode(', ');
    @endphp

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card
                label="Deployment"
                :value="$behaviorSummary['label']"
                :meta="$behaviorSummary['description']"
            />
            <x-ui.stat-card
                label="Commercial model"
                :value="$behaviorSummary['commercial_model_label']"
                :meta="'License mode: '.$statusItems['License mode']"
                tone="brand"
            />
            <x-ui.stat-card
                label="Enabled features"
                :value="$enabledFeatureCount"
                :meta="$disabledFeatureCount.' disabled in this context.'"
                tone="success"
            />
            <x-ui.stat-card
                label="Runtime"
                :value="$environmentItems['App environment']"
                :meta="'Debug: '.$environmentItems['Debug mode']"
                tone="info"
            />
        </section>

        <x-ui.table-card
            title="Configuration"
            description="Values are read from Laravel config, including cached config in production."
        >
            <table class="enterprise-table">
                <tbody>
                    @foreach ($statusItems as $label => $value)
                        <tr>
                            <th scope="row" class="w-64 px-5 py-4 text-left text-sm font-medium text-text-secondary">{{ $label }}</th>
                            <td class="px-5 py-4 text-sm font-semibold text-text-primary">{{ $value }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-ui.table-card>

        <section class="grid gap-4 xl:grid-cols-3">
            <x-ui.settings-card title="Enabled Route Groups">
                <p class="text-sm leading-6 text-text-secondary">{{ $listLabels($behaviorSummary['route_groups']) ?: 'None' }}</p>
            </x-ui.settings-card>
            <x-ui.settings-card title="Enabled Dashboard Widgets">
                <p class="text-sm leading-6 text-text-secondary">{{ $listLabels($behaviorSummary['dashboard_widgets']) ?: 'None' }}</p>
            </x-ui.settings-card>
            <x-ui.settings-card title="Enabled Settings Sections">
                <p class="text-sm leading-6 text-text-secondary">{{ $listLabels($behaviorSummary['settings_sections']) ?: 'None' }}</p>
            </x-ui.settings-card>
        </section>

        <x-ui.panel
            title="Deployment Capabilities"
            description="Mode-aware flags for installer, billing, update, and managed-service features."
        >
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ($capabilityItems as $label => $enabled)
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                        <p class="text-sm font-medium text-text-secondary">{{ $label }}</p>
                        <x-ui.badge :tone="$enabled ? 'success' : 'neutral'" class="mt-3">
                            {{ $enabled ? 'Yes' : 'No' }}
                        </x-ui.badge>
                    </div>
                @endforeach
            </div>
        </x-ui.panel>

        <x-ui.panel
            title="Environment Indicators"
            description="Operational runtime signals only. Secrets are never shown here."
        >
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ($environmentItems as $label => $value)
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                        <p class="text-sm font-medium text-text-secondary">{{ $label }}</p>
                        <p class="mt-2 break-words text-sm font-semibold text-text-primary">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        </x-ui.panel>

        <x-ui.panel
            title="Mode-gated setup states"
            description="These show which sections are available, disabled, or need attention for the current portal mode."
        >
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($placeholderCards as $card)
                    @if ($behavior->allowsRouteGroup($card['route_group'], user: auth()->user()))
                        <a href="{{ $card['href'] }}" class="ui-card ui-card-hover block rounded-md p-4 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg-primary">
                            <span class="text-sm font-semibold text-text-primary">{{ $card['title'] }}</span>
                            <span class="mt-2 block text-sm leading-6 text-text-secondary">{{ $card['body'] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
