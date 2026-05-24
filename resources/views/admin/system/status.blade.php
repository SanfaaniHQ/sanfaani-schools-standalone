<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">System Status</h2>
            <p class="mt-1 text-sm text-gray-500">Read-only deployment, license, feature, and behavior configuration for this installation.</p>
        </div>
    </x-slot>

    @php
        $behavior = app(\App\Services\System\DeploymentBehaviorService::class);
        $listLabels = fn (array $items) => collect($items)
            ->map(fn (string $item) => str($item)->replace('_', ' ')->title())
            ->implode(', ');
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Deployment</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900">{{ $behaviorSummary['label'] }}</p>
                    <p class="mt-1 text-sm text-gray-600">{{ $behaviorSummary['description'] }}</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Commercial model</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900">{{ $behaviorSummary['commercial_model_label'] }}</p>
                    <p class="mt-1 text-sm text-gray-600">License mode: {{ $statusItems['License mode'] }}</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Enabled features</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $enabledFeatureCount }}</p>
                    <p class="mt-1 text-sm text-gray-600">{{ $disabledFeatureCount }} disabled in this context.</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Runtime</p>
                    <p class="mt-2 text-lg font-semibold text-gray-900">{{ $environmentItems['App environment'] }}</p>
                    <p class="mt-1 text-sm text-gray-600">Debug: {{ $environmentItems['Debug mode'] }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Configuration</h3>
                    <p class="mt-1 text-sm text-gray-500">Values are read from Laravel config, including cached config in production.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($statusItems as $label => $value)
                                <tr>
                                    <th scope="row" class="w-64 px-6 py-4 text-left text-sm font-medium text-gray-600">{{ $label }}</th>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-3">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Enabled Route Groups</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">{{ $listLabels($behaviorSummary['route_groups']) ?: 'None' }}</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Enabled Dashboard Widgets</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">{{ $listLabels($behaviorSummary['dashboard_widgets']) ?: 'None' }}</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Enabled Settings Sections</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">{{ $listLabels($behaviorSummary['settings_sections']) ?: 'None' }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Deployment Capabilities</h3>
                    <p class="mt-1 text-sm text-gray-500">Foundation flags for future installer, billing, update, and managed-service features.</p>
                </div>

                <div class="grid gap-4 p-6 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach ($capabilityItems as $label => $enabled)
                        <div class="rounded-xl border border-gray-200 p-4">
                            <p class="text-sm font-medium text-gray-600">{{ $label }}</p>
                            <p class="mt-3 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $enabled ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $enabled ? 'Yes' : 'No' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Environment Indicators</h3>
                    <p class="mt-1 text-sm text-gray-500">Operational runtime signals only. Secrets are never shown here.</p>
                </div>
                <div class="grid gap-4 p-6 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach ($environmentItems as $label => $value)
                        <div class="rounded-xl border border-gray-200 p-4">
                            <p class="text-sm font-medium text-gray-600">{{ $label }}</p>
                            <p class="mt-2 break-words text-sm font-semibold text-gray-900">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Mode-Gated Placeholders</h3>
                <p class="mt-1 text-sm text-gray-500">These prove route visibility only. The underlying commercial systems are intentionally not implemented here.</p>
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($placeholderCards as $card)
                        @if ($behavior->allowsRouteGroup($card['route_group'], user: auth()->user()))
                            <a href="{{ $card['href'] }}" class="rounded-xl border border-gray-200 p-4 transition hover:border-gray-300 hover:bg-gray-50">
                                <span class="text-sm font-semibold text-gray-900">{{ $card['title'] }}</span>
                                <span class="mt-2 block text-sm leading-6 text-gray-600">{{ $card['body'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
