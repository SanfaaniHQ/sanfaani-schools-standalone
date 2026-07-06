<x-app-layout>
    @php
        $schoolMailControlsDisabled = ! $schoolScopeReady || ! $schoolCustomSmtpAllowed;
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">School / Settings / Mail</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">School Mail Settings</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $school->name }}</p>
            </div>
            <a href="{{ route('school.dashboard') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-6xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="lg:col-span-2">
                @if (session('success'))
                    <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
                @endif
                @if (session('mail_test_result'))
                    @php($testResult = session('mail_test_result'))
                    <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900">
                        <p class="font-semibold">School SMTP accepted the test message for delivery.</p>
                        <p class="mt-1">SMTP acceptance means the sending server accepted the message. It does not guarantee inbox placement.</p>
                        <dl class="mt-3 grid gap-2 sm:grid-cols-2">
                            <div><dt class="text-xs uppercase text-green-700">Transport</dt><dd>{{ strtoupper($testResult['transport']) }}</dd></div>
                            <div><dt class="text-xs uppercase text-green-700">Configuration</dt><dd>{{ ucfirst($testResult['configuration']) }} values</dd></div>
                            <div><dt class="text-xs uppercase text-green-700">Server</dt><dd>{{ $testResult['host'] }}:{{ $testResult['port'] }} ({{ strtoupper($testResult['encryption']) }})</dd></div>
                            <div><dt class="text-xs uppercase text-green-700">Sender</dt><dd class="break-all">{{ $testResult['sender'] }}</dd></div>
                            <div><dt class="text-xs uppercase text-green-700">Recipient</dt><dd class="break-all">{{ $testResult['recipient'] }}</dd></div>
                            <div><dt class="text-xs uppercase text-green-700">Timestamp</dt><dd>{{ $testResult['timestamp'] }}</dd></div>
                            @if ($testResult['provider_message_id'])
                                <div class="sm:col-span-2"><dt class="text-xs uppercase text-green-700">Provider message ID</dt><dd class="break-all font-mono text-xs">{{ $testResult['provider_message_id'] }}</dd></div>
                            @endif
                        </dl>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
                @endif
                @if (session('warning'))
                    <div class="mb-6 rounded-lg bg-amber-50 p-4 text-sm text-amber-800">{{ session('warning') }}</div>
                @endif
                @if (! $schoolScopeReady)
                    <div class="mb-6 rounded-lg bg-amber-50 p-4 text-sm text-amber-800">School scoped mail settings are not ready. Run migrations before saving changes.</div>
                @endif
                @if (! $schoolCustomSmtpAllowed)
                    <div class="mb-6 rounded-lg bg-amber-50 p-4 text-sm text-amber-800">
                        Custom school SMTP is currently disabled by the platform administrator. Outgoing school mail will use the platform mail system.
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-700">Please fix the highlighted fields.</div>
                @endif

                <form id="school-mail-settings-form" method="POST" action="{{ route('school.mail-settings.update') }}" data-loading-text="Saving..." class="space-y-6 rounded-lg bg-white p-6 shadow-sm">
                    @csrf
                    @method('PATCH')

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Provider preset</label>
                            <select id="smtp-provider-preset" class="mt-1 block w-full rounded-lg border-gray-300" @disabled($schoolMailControlsDisabled)>
                                <option value="custom">Custom SMTP</option>
                                <option value="gmail-587">Gmail / Google Workspace — TLS 587</option>
                                <option value="gmail-465">Gmail / Google Workspace — SSL 465</option>
                                <option value="cpanel-587">cPanel / Webmail — TLS 587</option>
                                <option value="cpanel-465">cPanel / Webmail — SSL 465</option>
                            </select>
                            <input type="hidden" name="mailer" value="smtp">
                            <p class="mt-1 text-xs text-gray-500">Presets fill safe defaults; confirm the exact server details with your provider.</p>
                            @error('mailer')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="mt-6 flex items-center gap-3 rounded-lg border border-gray-200 p-4 text-sm text-gray-700">
                            <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $setting->is_enabled)) @disabled($schoolMailControlsDisabled) class="rounded border-gray-300">
                            Enable school SMTP override
                        </label>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Host</label>
                            <input name="host" value="{{ old('host', $setting->host) }}" @disabled($schoolMailControlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                            @error('host')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Port</label>
                            <input name="port" type="number" value="{{ old('port', $setting->port) }}" @disabled($schoolMailControlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                            @error('port')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Username</label>
                            <input name="username" value="{{ old('username', $setting->username) }}" @disabled($schoolMailControlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                            @error('username')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input name="password" type="password" placeholder="{{ $masker->maskedPassword($setting) }}" autocomplete="new-password" @disabled($schoolMailControlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                            <p class="mt-1 text-xs text-gray-500">Leave empty to keep the current encrypted password.</p>
                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Encryption</label>
                            <select name="encryption" @disabled($schoolMailControlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                                <option value="none" @selected(in_array(old('encryption', $setting->encryption), [null, '', 'none'], true))>None</option>
                                <option value="tls" @selected(old('encryption', $setting->encryption) === 'tls')>TLS</option>
                                <option value="ssl" @selected(old('encryption', $setting->encryption) === 'ssl')>SSL</option>
                            </select>
                            @error('encryption')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">From Address</label>
                            <input name="from_address" value="{{ old('from_address', $setting->from_address) }}" @disabled($schoolMailControlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                            @error('from_address')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">From Name</label>
                            <input name="from_name" value="{{ old('from_name', $setting->from_name) }}" @disabled($schoolMailControlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                            @error('from_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reply-To Email</label>
                        <input name="reply_to_email" value="{{ old('reply_to_email', $setting->reply_to_email) }}" @disabled($schoolMailControlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                        @error('reply_to_email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Connection Timeout (seconds)</label>
                        <input name="timeout" type="number" min="1" max="120" value="{{ old('timeout', data_get($setting->metadata, 'timeout', 10)) }}" @disabled($schoolMailControlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                        @error('timeout')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button @disabled($schoolMailControlsDisabled) class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:bg-gray-400">Save Settings</button>
                    </div>
                </form>
            </div>

            <div class="space-y-6">
                <div class="rounded-lg bg-white p-6 text-sm text-gray-600 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Delivery Status</h3>
                    <dl class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-4">
                            <dt>School SMTP enabled</dt>
                            <dd class="font-medium {{ $schoolStatus['enabled'] ? 'text-green-700' : 'text-gray-700' }}">{{ $schoolStatus['enabled'] ? 'Enabled' : 'Disabled' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt>School SMTP configured</dt>
                            <dd class="font-medium {{ $schoolStatus['configured'] ? 'text-green-700' : 'text-amber-700' }}">{{ $schoolStatus['configured'] ? 'Complete' : 'Incomplete' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt>SMTP password</dt>
                            <dd class="font-medium {{ $schoolStatus['password_unusable'] ? 'text-red-700' : ($schoolStatus['password_available'] ? 'text-green-700' : 'text-amber-700') }}">{{ $schoolStatus['password_unusable'] ? 'Needs re-entry' : ($schoolStatus['password_available'] ? 'Available' : 'Not set') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt>Platform fallback</dt>
                            <dd class="font-medium {{ $platformFallbackConfigured ? 'text-green-700' : 'text-amber-700' }}">{{ $platformFallbackEnabled ? ($platformFallbackConfigured ? 'Configured' : 'Not configured') : 'Disabled' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt>Fallback transport</dt>
                            <dd class="font-medium text-gray-700">{{ strtoupper($platformStatus['driver']) }}{{ $platformStatus['external_delivery'] ? '' : ' (non-delivery)' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt>Platform-only mode</dt>
                            <dd class="font-medium {{ $forcePlatformMailer ? 'text-amber-700' : 'text-gray-700' }}">{{ $forcePlatformMailer ? 'Enabled' : 'Off' }}</dd>
                        </div>
                        @if ($schoolStatus['last_test_outcome'])
                            <div class="flex items-center justify-between gap-4">
                                <dt>Last school test</dt>
                                <dd class="text-right font-medium {{ $schoolStatus['last_test_outcome'] === 'accepted_by_smtp' ? 'text-green-700' : 'text-red-700' }}">
                                    {{ ucfirst($schoolStatus['last_test_outcome']) }} via {{ $schoolStatus['last_test_transport'] }}
                                    @if ($schoolStatus['last_test_configuration'] === 'temporary')
                                        <span class="block text-xs font-normal text-amber-700">temporary values</span>
                                    @endif
                                    @if ($schoolStatus['last_test_at'])
                                        <span class="block text-xs font-normal text-gray-500">{{ $schoolStatus['last_test_at'] }}</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                        <div class="flex items-center justify-between gap-4">
                            <dt>SMTP accepted</dt>
                            <dd class="font-medium {{ $schoolStatus['last_test_smtp_accepted'] ? 'text-green-700' : 'text-gray-700' }}">{{ $schoolStatus['last_test_smtp_accepted'] ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt>Fallback used</dt>
                            <dd class="font-medium text-gray-700">{{ $schoolStatus['last_test_fallback_used'] ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt>External delivery attempted</dt>
                            <dd class="font-medium text-gray-700">{{ $schoolStatus['last_test_external_delivery_attempted'] ? 'Yes' : 'No' }}</dd>
                        </div>
                        @if ($schoolStatus['last_test_category'])
                            <div class="flex items-center justify-between gap-4">
                                <dt>Last safe error category</dt>
                                <dd class="font-mono text-xs text-red-700">{{ $schoolStatus['last_test_category'] }}</dd>
                            </div>
                        @endif
                        @if ($latestDeliveryAttempt)
                            <div class="flex items-center justify-between gap-4">
                                <dt>Latest delivery status</dt>
                                <dd class="font-mono text-xs text-gray-700">{{ $latestDeliveryAttempt->status }}</dd>
                            </div>
                        @endif
                    </dl>
                    @if ($platformFallbackEnabled && ! $platformFallbackConfigured)
                        <p class="mt-4 text-xs leading-5 text-amber-700">Platform fallback is not configured. Please configure platform mail settings or use school SMTP.</p>
                    @endif
                </div>

                <form id="school-mail-test-form" method="POST" action="{{ route('school.mail-settings.test') }}" data-loading-text="Testing..." class="rounded-lg bg-white p-6 shadow-sm">
                    @csrf
                    <h3 class="text-base font-semibold text-gray-900">Test School SMTP</h3>
                    <p class="mt-1 text-sm text-gray-600">Choose saved settings or the unsaved values currently in the form. Platform fallback is never used by either action.</p>
                    <div id="school-mail-test-payload"></div>
                    <label class="mt-4 block text-sm font-medium text-gray-700">Recipient</label>
                    <input name="test_email" value="{{ old('test_email', auth()->user()->email) }}" @disabled($schoolMailControlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                    @error('test_email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="mt-4 grid gap-2">
                        <button name="test_mode" value="saved" @disabled($schoolMailControlsDisabled) class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:bg-gray-400">Test Saved School SMTP</button>
                        <button name="test_mode" value="temporary" @disabled($schoolMailControlsDisabled) class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 disabled:cursor-not-allowed disabled:text-gray-400">Test Temporary Values</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('school.mail-settings.test-fallback') }}" data-loading-text="Testing fallback..." class="rounded-lg bg-white p-6 shadow-sm">
                    @csrf
                    <h3 class="text-base font-semibold text-gray-900">Test Platform Fallback</h3>
                    <p class="mt-1 text-sm text-gray-600">Tests the platform mailer separately. Log and array transports do not deliver external email.</p>
                    <label class="mt-4 block text-sm font-medium text-gray-700">Recipient</label>
                    <input name="test_email" value="{{ old('test_email', auth()->user()->email) }}" class="mt-1 block w-full rounded-lg border-gray-300">
                    <button class="mt-4 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Test Platform Fallback</button>
                </form>

                <div class="rounded-lg bg-white p-6 text-sm leading-6 text-gray-600 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Provider Guidance</h3>
                    <p class="mt-2 font-medium text-gray-800">Gmail / Google Workspace</p>
                    <p>Use <span class="font-mono">smtp.gmail.com</span>, the full address, and usually a Google App Password. Choose 465 + SSL or 587 + TLS.</p>
                    <p class="mt-3 font-medium text-gray-800">cPanel / Webmail</p>
                    <p>Use the exact outgoing server from "Connect Devices", the full mailbox username, and its password. Choose 465 + SSL or 587 + TLS. Keep the From Address aligned with the authenticated mailbox.</p>
                    <h4 class="mt-4 font-semibold text-gray-900">If SMTP accepts but the message is not visible</h4>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <li>Check Spam, Promotions, Updates, and All Mail.</li>
                        <li>In cPanel, open Track Delivery and Email Deliverability.</li>
                        <li>Verify SPF, DKIM, and DMARC for the sender domain.</li>
                        <li>Confirm the sender matches the authenticated mailbox or an authorised alias.</li>
                        <li>Test a same-domain recipient first, then an external provider.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const settingsForm = document.getElementById('school-mail-settings-form');
            const testForm = document.getElementById('school-mail-test-form');
            const payload = document.getElementById('school-mail-test-payload');
            const preset = document.getElementById('smtp-provider-preset');

            if (!settingsForm || !testForm || !payload) {
                return;
            }

            preset?.addEventListener('change', () => {
                const options = {
                    'gmail-587': { host: 'smtp.gmail.com', port: '587', encryption: 'tls' },
                    'gmail-465': { host: 'smtp.gmail.com', port: '465', encryption: 'ssl' },
                    'cpanel-587': { port: '587', encryption: 'tls' },
                    'cpanel-465': { port: '465', encryption: 'ssl' },
                };
                const selected = options[preset.value];

                if (!selected) return;

                Object.entries(selected).forEach(([name, value]) => {
                    const field = settingsForm.elements.namedItem(name);
                    if (field && value) field.value = value;
                });
            });

            const fields = [
                'mailer',
                'host',
                'port',
                'username',
                'password',
                'encryption',
                'from_address',
                'from_name',
                'reply_to_email',
                'timeout',
            ];

            testForm.addEventListener('submit', () => {
                payload.innerHTML = '';

                fields.forEach((name) => {
                    const source = settingsForm.elements.namedItem(name);
                    const input = document.createElement('input');

                    input.type = 'hidden';
                    input.name = name;
                    input.value = source?.value || '';
                    payload.appendChild(input);
                });

                const enabled = document.createElement('input');
                const checkbox = settingsForm.elements.namedItem('is_enabled');

                enabled.type = 'hidden';
                enabled.name = 'is_enabled';
                enabled.value = checkbox?.checked ? '1' : '0';
                payload.appendChild(enabled);
            });
        });
    </script>
</x-app-layout>
