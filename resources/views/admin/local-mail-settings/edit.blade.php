<x-app-layout>
    @php
        $controlsDisabled = ! $schoolScopeReady || ! $schoolCustomSmtpAllowed;
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">Local Installation</p>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">Email Delivery</h2>
                <p class="mt-1 text-sm text-text-secondary">Connect your school email account to send admission updates, password resets, invoices, and announcements.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="ui-button-secondary">Back to Dashboard</a>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @if (session('success'))
                <x-ui.alert tone="success" :body="session('success')" />
            @endif
            @if (session('error'))
                <x-ui.alert tone="danger" :body="session('error')" />
            @endif
            @if ($errors->any())
                <x-ui.alert tone="danger" body="Review the highlighted email settings and try again." />
            @endif

            @unless ($schoolScopeReady)
                <x-ui.alert tone="warning" body="Email delivery settings need the latest database tables before changes can be saved." />
            @endunless
            @unless ($schoolCustomSmtpAllowed)
                <x-ui.alert tone="warning" body="School SMTP override is currently disabled by the platform mail policy." />
            @endunless

            <form id="local-mail-settings-form" method="POST" action="{{ route('admin.local-mail-settings.update') }}" data-loading-text="Saving..." class="space-y-6 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @method('PATCH')

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="mailer" class="block text-sm font-medium text-gray-700">Mailer</label>
                        <select id="mailer" name="mailer" @disabled($controlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                            <option value="smtp" @selected(old('mailer', $setting->mailer) === 'smtp')>SMTP</option>
                            <option value="log" @selected(old('mailer', $setting->mailer) === 'log')>Log</option>
                        </select>
                        @error('mailer')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <label class="mt-6 flex items-center gap-3 rounded-lg border border-gray-200 p-4 text-sm text-gray-700">
                        <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $setting->is_enabled)) @disabled($controlsDisabled) class="rounded border-gray-300">
                        Enable school email delivery override
                    </label>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="host" class="block text-sm font-medium text-gray-700">Host</label>
                        <input id="host" name="host" value="{{ old('host', $setting->host) }}" placeholder="smtp.example.com" @disabled($controlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                        @error('host')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="port" class="block text-sm font-medium text-gray-700">Port</label>
                        <input id="port" name="port" type="number" value="{{ old('port', $setting->port) }}" placeholder="465" @disabled($controlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                        @error('port')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input id="username" name="username" value="{{ old('username', $setting->username) }}" @disabled($controlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                        @error('username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="password" name="password" type="password" placeholder="{{ $masker->maskedPassword($setting) }}" autocomplete="new-password" @disabled($controlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                        <p class="mt-1 text-xs text-gray-500">Leave empty to keep the current encrypted password.</p>
                        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <label for="encryption" class="block text-sm font-medium text-gray-700">Encryption</label>
                        <select id="encryption" name="encryption" @disabled($controlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                            <option value="ssl" @selected(old('encryption', $setting->encryption) === 'ssl')>SSL</option>
                            <option value="tls" @selected(old('encryption', $setting->encryption) === 'tls')>TLS</option>
                            <option value="none" @selected(in_array(old('encryption', $setting->encryption), [null, '', 'none'], true))>None</option>
                        </select>
                        @error('encryption')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="from_address" class="block text-sm font-medium text-gray-700">From Address</label>
                        <input id="from_address" name="from_address" value="{{ old('from_address', $setting->from_address ?: $school->email) }}" @disabled($controlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                        @error('from_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="from_name" class="block text-sm font-medium text-gray-700">From Name</label>
                        <input id="from_name" name="from_name" value="{{ old('from_name', $setting->from_name ?: $school->name) }}" @disabled($controlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                        @error('from_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="reply_to_email" class="block text-sm font-medium text-gray-700">Reply-To Email</label>
                    <input id="reply_to_email" name="reply_to_email" value="{{ old('reply_to_email', $setting->reply_to_email) }}" @disabled($controlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                    @error('reply_to_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="timeout" class="block text-sm font-medium text-gray-700">Connection Timeout (seconds)</label>
                    <input id="timeout" name="timeout" type="number" min="1" max="120" value="{{ old('timeout', data_get($setting->metadata, 'timeout', 10)) }}" @disabled($controlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                    @error('timeout')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end">
                    <button @disabled($controlsDisabled) class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:bg-gray-400">Save Settings</button>
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
                        <dd class="font-medium {{ $schoolStatus['password_unusable'] ? 'text-red-700' : ($schoolStatus['password_available'] ? 'text-green-700' : 'text-amber-700') }}">
                            {{ $schoolStatus['password_unusable'] ? 'Needs re-entry' : ($schoolStatus['password_available'] ? 'Available' : 'Not set') }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt>Platform fallback</dt>
                        <dd class="font-medium {{ $platformFallbackConfigured ? 'text-green-700' : 'text-amber-700' }}">{{ $platformFallbackConfigured ? 'Configured' : 'Not configured' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt>Fallback transport</dt>
                        <dd class="font-medium text-gray-700">{{ strtoupper($platformStatus['driver']) }}{{ $platformStatus['external_delivery'] ? '' : ' (non-delivery)' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt>Fallback policy</dt>
                        <dd class="font-medium {{ $platformFallbackEnabled ? 'text-green-700' : 'text-gray-700' }}">{{ $platformFallbackEnabled ? 'Enabled' : 'Disabled' }}</dd>
                    </div>
                    @if ($schoolStatus['last_test_outcome'])
                        <div class="flex items-center justify-between gap-4">
                            <dt>Last school test</dt>
                            <dd class="text-right font-medium {{ $schoolStatus['last_test_outcome'] === 'accepted' ? 'text-green-700' : 'text-red-700' }}">
                                {{ ucfirst($schoolStatus['last_test_outcome']) }} via {{ $schoolStatus['last_test_transport'] }}
                                @if ($schoolStatus['last_test_configuration'] === 'temporary')
                                    <span class="block text-xs font-normal text-amber-700">temporary values</span>
                                @endif
                            </dd>
                        </div>
                    @endif
                </dl>
                @unless ($platformFallbackConfigured)
                    <p class="mt-4 text-xs leading-5 text-amber-700">Platform fallback is not configured. Please configure platform mail settings or use school SMTP.</p>
                @endunless
            </div>

            <form id="local-mail-test-form" method="POST" action="{{ route('admin.local-mail-settings.test') }}" data-loading-text="Testing..." class="rounded-lg bg-white p-6 shadow-sm">
                @csrf
                <h3 class="text-base font-semibold text-gray-900">Test School SMTP</h3>
                <p class="mt-1 text-sm text-gray-600">Use the outgoing SMTP details from your hosting email account. This tests the form values directly and never hides a school SMTP failure behind fallback.</p>
                <div id="local-mail-test-payload"></div>
                <label for="test_email" class="mt-4 block text-sm font-medium text-gray-700">Test Recipient</label>
                <input id="test_email" name="test_email" value="{{ old('test_email', auth()->user()->email) }}" @disabled($controlsDisabled) class="mt-1 block w-full rounded-lg border-gray-300">
                @error('test_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <button @disabled($controlsDisabled) class="mt-4 w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:bg-gray-400">Test School SMTP</button>
            </form>

            <form method="POST" action="{{ route('admin.local-mail-settings.test-fallback') }}" data-loading-text="Testing fallback..." class="rounded-lg bg-white p-6 shadow-sm">
                @csrf
                <h3 class="text-base font-semibold text-gray-900">Test Platform Fallback</h3>
                <p class="mt-1 text-sm text-gray-600">Tests the installation-level mailer separately. Log and array transports do not deliver external email.</p>
                <label for="fallback_test_email" class="mt-4 block text-sm font-medium text-gray-700">Test Recipient</label>
                <input id="fallback_test_email" name="test_email" value="{{ old('test_email', auth()->user()->email) }}" class="mt-1 block w-full rounded-lg border-gray-300">
                <button class="mt-4 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">Test Platform Fallback</button>
            </form>

            <div class="rounded-lg bg-white p-6 text-sm leading-6 text-gray-600 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Provider Guidance</h3>
                <p class="mt-2 font-medium text-gray-800">Gmail / Google Workspace</p>
                <p>Use <span class="font-mono">smtp.gmail.com</span>, the full email address, and usually a Google App Password. Use 465 + SSL or 587 + TLS. The From Address should match the account or an authorised alias.</p>
                <p class="mt-3 font-medium text-gray-800">cPanel / Webmail</p>
                <p>Use the exact outgoing hostname from cPanel “Connect Devices”, the full mailbox username, and mailbox password. Use 465 + SSL or 587 + TLS. A certificate mismatch usually means the server hostname must be used instead of a mail alias.</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const settingsForm = document.getElementById('local-mail-settings-form');
            const testForm = document.getElementById('local-mail-test-form');
            const payload = document.getElementById('local-mail-test-payload');

            if (!settingsForm || !testForm || !payload) {
                return;
            }

            const fields = ['mailer', 'host', 'port', 'username', 'password', 'encryption', 'from_address', 'from_name', 'reply_to_email', 'timeout'];

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
