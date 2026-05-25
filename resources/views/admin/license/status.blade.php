<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">License Status</h2>
                <p class="mt-1 text-sm text-gray-500">Local activation, validation, and entitlement status for this deployment.</p>
            </div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.license.validate') }}">
                    @csrf
                    <button type="submit" class="rounded-md border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Validate now</button>
                </form>
                <a href="{{ route('admin.license.activate') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Activate license</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @include('admin.license.partials.status-card', ['label' => 'Validation status', 'value' => str($result->status)->replace('_', ' ')->title(), 'meta' => $result->message])
                @include('admin.license.partials.status-card', ['label' => 'Deployment mode', 'value' => str($deploymentMode)->replace('_', ' ')->title(), 'meta' => 'License mode: '.str($licenseMode)->replace('_', ' ')->title()])
                @include('admin.license.partials.status-card', ['label' => 'Client', 'value' => $school?->name ?? $license?->issued_to_name ?? 'Platform/global', 'meta' => $license?->issued_to_email])
                @include('admin.license.partials.status-card', ['label' => 'Masked key', 'value' => $license?->maskedKey() ?? 'No license', 'meta' => 'Raw keys are never shown.'])
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">License Details</h3>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-4">
                    @include('admin.license.partials.status-card', ['label' => 'License type', 'value' => $license ? str($license->license_type)->replace('_', ' ')->title() : 'Missing'])
                    @include('admin.license.partials.status-card', ['label' => 'Stored status', 'value' => $license ? str($license->status)->title() : 'Missing'])
                    @include('admin.license.partials.status-card', ['label' => 'Domain', 'value' => $license?->domain ?: 'Any configured domain'])
                    @include('admin.license.partials.status-card', ['label' => 'Expiry date', 'value' => $license?->expires_at?->toFormattedDateString() ?? 'No expiry', 'meta' => $daysUntilExpiry === null ? 'Lifetime or unset.' : $daysUntilExpiry.' day(s) remaining'])
                    @include('admin.license.partials.status-card', ['label' => 'Offline grace', 'value' => $license?->offline_grace_until?->isFuture() ? 'Available' : 'Not available', 'meta' => $license?->offline_grace_until?->toDayDateTimeString()])
                    @include('admin.license.partials.status-card', ['label' => 'Last validation', 'value' => $license?->last_validated_at?->toDayDateTimeString() ?? 'Never'])
                    @include('admin.license.partials.status-card', ['label' => 'Expiry warning', 'value' => $shouldWarnExpiring ? 'Warning active' : 'No warning'])
                    @include('admin.license.partials.status-card', ['label' => 'Domain matching', 'value' => config('licensing.require_domain_match') ? 'Required' : 'Disabled'])
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Enabled Entitlements</h3>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse ($entitlements as $key => $value)
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $key }}: {{ is_array($value) ? json_encode($value) : ($value ? 'enabled' : 'disabled') }}</span>
                        @empty
                            <p class="text-sm text-gray-500">No license entitlements are active.</p>
                        @endforelse
                    </div>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Enabled License Features</h3>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse ($features as $key => $value)
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $key }}: {{ is_array($value) ? json_encode($value) : ($value ? 'enabled' : 'disabled') }}</span>
                        @empty
                            <p class="text-sm text-gray-500">No license feature flags are active.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            @include('admin.license.partials.audit-log', ['auditLogs' => $auditLogs])
        </div>
    </div>
</x-app-layout>
