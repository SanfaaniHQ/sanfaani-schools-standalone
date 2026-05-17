<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Admin / Mail</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Platform Mail System</h2>
                <p class="mt-1 text-sm text-gray-500">SMTP health, fallback policy, and platform delivery status.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.mail-settings.edit') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700">SMTP Settings</a>
                <a href="{{ route('admin.communications.logs') }}" class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white">Delivery Logs</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['label' => 'Sent', 'value' => $summary['sent']],
                    ['label' => 'Failed', 'value' => $summary['failed']],
                    ['label' => 'Pending', 'value' => $summary['pending']],
                    ['label' => 'Fallback Used', 'value' => $summary['fallback_used']],
                ] as $metric)
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $metric['label'] }}</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($metric['value']) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Active Platform SMTP</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Enabled</dt><dd class="font-medium text-gray-900">{{ $setting->is_enabled ? 'Yes' : 'No' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Mailer</dt><dd class="font-medium text-gray-900">{{ strtoupper($setting->mailer ?: 'log') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Host</dt><dd class="font-medium text-gray-900">{{ $setting->host ?: 'Not configured' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">From</dt><dd class="font-medium text-gray-900">{{ $setting->from_address ?: config('mail.from.address') }}</dd></div>
                    </dl>
                </div>

                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">School Mail Policy</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Custom SMTP</dt><dd class="font-medium text-gray-900">{{ data_get($governance, 'school_custom_smtp_enabled') ? 'Allowed' : 'Disabled' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Platform Only</dt><dd class="font-medium text-gray-900">{{ data_get($governance, 'force_platform_mailer') ? 'Enabled' : 'Off' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Fallback</dt><dd class="font-medium text-gray-900">{{ data_get($governance, 'platform_fallback_enabled') ? 'Enabled' : 'Disabled' }}</dd></div>
                    </dl>
                </div>

                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Operational Links</h3>
                    <div class="mt-4 grid gap-2 text-sm">
                        <a href="{{ route('admin.communications.index') }}" class="rounded-lg border border-gray-200 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">Open Communication Center</a>
                        <a href="{{ route('admin.communications.logs', ['status' => 'failed']) }}" class="rounded-lg border border-gray-200 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">Review Failed Mail</a>
                        <a href="{{ route('admin.audit-logs.index', ['action' => 'communication_email']) }}" class="rounded-lg border border-gray-200 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">Mail Audit Trail</a>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Recent Failures</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Recipient</th>
                                <th class="px-4 py-3 text-left">Subject</th>
                                <th class="px-4 py-3 text-left">Reason</th>
                                <th class="px-4 py-3 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($recentFailures as $failure)
                                <tr>
                                    <td class="px-4 py-3 text-gray-700">{{ $failure->recipient }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $failure->subject }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ str($failure->failure_reason)->limit(120) }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $failure->created_at?->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No platform mail failures recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
