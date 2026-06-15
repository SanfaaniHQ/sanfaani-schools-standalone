<x-app-layout>
    @php
        $statusTone = fn (string $status): string => $status === 'pass'
            ? 'bg-green-100 text-green-700'
            : ($status === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700');
        $valueLabel = function (mixed $value): string {
            if (is_array($value)) {
                return array_key_exists('enabled', $value)
                    ? ((bool) $value['enabled'] ? 'enabled' : 'disabled')
                    : 'configured';
            }

            return $value ? 'enabled' : 'disabled';
        };
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">License Status</h2>
                <p class="mt-1 text-sm text-gray-500">License validation for this school portal.</p>
            </div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.license.validate') }}" data-loading-text="Checking license...">
                    @csrf
                    <button type="submit" data-loading-text="Checking license..." class="rounded-md border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Check license</button>
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
                @include('admin.license.partials.status-card', ['label' => 'Portal mode', 'value' => str($deploymentMode)->replace('_', ' ')->title(), 'meta' => 'License mode: '.str($licenseMode)->replace('_', ' ')->title()])
                @include('admin.license.partials.status-card', ['label' => 'Client', 'value' => $school?->name ?? $license?->issued_to_name ?? 'Platform/global', 'meta' => $license?->issued_to_email])
                @include('admin.license.partials.status-card', ['label' => 'Stored key', 'value' => $license?->maskedKey() ?? 'No license activated', 'meta' => 'Full keys stay hidden for security.'])
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">License details</h3>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-4">
                    @include('admin.license.partials.status-card', ['label' => 'License type', 'value' => $license ? str($license->license_type)->replace('_', ' ')->title() : 'Missing'])
                    @include('admin.license.partials.status-card', ['label' => 'Current status', 'value' => $license ? str($license->status)->title() : 'Missing'])
                    @include('admin.license.partials.status-card', ['label' => 'Domain', 'value' => $license?->domain ?: 'Any configured domain'])
                    @include('admin.license.partials.status-card', ['label' => 'Expiry date', 'value' => $license?->expires_at?->toFormattedDateString() ?? 'No expiry', 'meta' => $daysUntilExpiry === null ? 'Lifetime or unset.' : $daysUntilExpiry.' day(s) remaining'])
                    @include('admin.license.partials.status-card', ['label' => 'Temporary offline access', 'value' => $license?->offline_grace_until?->isFuture() ? 'Available' : 'Not available', 'meta' => $license?->offline_grace_until?->toDayDateTimeString()])
                    @include('admin.license.partials.status-card', ['label' => 'Last checked', 'value' => $license?->last_validated_at?->toDayDateTimeString() ?? 'Never'])
                    @include('admin.license.partials.status-card', ['label' => 'Expiry warning', 'value' => $shouldWarnExpiring ? 'Warning active' : 'No warning'])
                @include('admin.license.partials.status-card', ['label' => 'Domain matching', 'value' => config('licensing.require_domain_match') ? 'Required' : 'Disabled'])
            </div>
        </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Support-safe diagnostics</h3>
                    <p class="mt-1 text-sm text-gray-500">Statuses only. License keys, security keys, database passwords, mail credentials, server URLs, and private paths are hidden.</p>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($supportDiagnostics as $item)
                        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <p class="text-sm font-medium text-gray-500">{{ $item['label'] }}</p>
                            <p class="mt-2 break-words text-lg font-semibold text-gray-900">{{ $item['value'] }}</p>
                            <span class="mt-3 inline-flex rounded-md px-2 py-1 text-xs font-semibold {{ $statusTone($item['status']) }}">
                                {{ str($item['status'])->upper() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Included services</h3>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse ($entitlements as $key => $value)
                            <span class="rounded-full {{ $valueLabel($value) === 'disabled' ? 'bg-slate-100 text-slate-700' : 'bg-indigo-50 text-indigo-700' }} px-3 py-1 text-xs font-semibold">{{ $key }}: {{ $valueLabel($value) }}</span>
                        @empty
                            <p class="text-sm text-gray-500">No additional services are active.</p>
                        @endforelse
                    </div>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Included modules</h3>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse ($features as $key => $value)
                            <span class="rounded-full {{ $valueLabel($value) === 'disabled' ? 'bg-slate-100 text-slate-700' : 'bg-emerald-50 text-emerald-700' }} px-3 py-1 text-xs font-semibold">{{ $key }}: {{ $valueLabel($value) }}</span>
                        @empty
                            <p class="text-sm text-gray-500">No additional modules are active.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Module access</h3>
                    <p class="mt-1 text-sm text-gray-500">Shows which modules are available for this school portal.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">Module</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">License value</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">Current access</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-normal text-gray-500">Reason</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($entitlementRows as $row)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $row['label'] }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ str($row['category'])->replace('_', ' ')->title() }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $row['license_label'] }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $row['access_enabled'] ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $row['access_label'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $row['reason'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @include('admin.license.partials.audit-log', ['auditLogs' => $auditLogs])
        </div>
    </div>
</x-app-layout>
