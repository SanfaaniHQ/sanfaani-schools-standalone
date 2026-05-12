<x-app-layout>
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
                @if (session('error'))
                    <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
                @endif
                @if (! $schoolScopeReady)
                    <div class="mb-6 rounded-lg bg-amber-50 p-4 text-sm text-amber-800">School scoped mail settings are not ready. Run migrations before saving changes.</div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-700">Please fix the highlighted fields.</div>
                @endif

                <form id="school-mail-settings-form" method="POST" action="{{ route('school.mail-settings.update') }}" data-loading-text="Saving..." class="space-y-6 rounded-lg bg-white p-6 shadow-sm">
                    @csrf
                    @method('PATCH')

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mailer</label>
                            <select name="mailer" @disabled(! $schoolScopeReady) class="mt-1 block w-full rounded-lg border-gray-300">
                                <option value="log" @selected(old('mailer', $setting->mailer) === 'log')>Log</option>
                                <option value="smtp" @selected(old('mailer', $setting->mailer) === 'smtp')>SMTP</option>
                            </select>
                            @error('mailer')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="mt-6 flex items-center gap-3 rounded-lg border border-gray-200 p-4 text-sm text-gray-700">
                            <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $setting->is_enabled)) @disabled(! $schoolScopeReady) class="rounded border-gray-300">
                            Enable school SMTP override
                        </label>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Host</label>
                            <input name="host" value="{{ old('host', $setting->host) }}" @disabled(! $schoolScopeReady) class="mt-1 block w-full rounded-lg border-gray-300">
                            @error('host')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Port</label>
                            <input name="port" type="number" value="{{ old('port', $setting->port) }}" @disabled(! $schoolScopeReady) class="mt-1 block w-full rounded-lg border-gray-300">
                            @error('port')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Username</label>
                            <input name="username" value="{{ old('username', $setting->username) }}" @disabled(! $schoolScopeReady) class="mt-1 block w-full rounded-lg border-gray-300">
                            @error('username')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input name="password" type="password" placeholder="{{ $masker->maskedPassword($setting) }}" autocomplete="new-password" @disabled(! $schoolScopeReady) class="mt-1 block w-full rounded-lg border-gray-300">
                            <p class="mt-1 text-xs text-gray-500">Leave empty to keep the current encrypted password.</p>
                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Encryption</label>
                            <select name="encryption" @disabled(! $schoolScopeReady) class="mt-1 block w-full rounded-lg border-gray-300">
                                <option value="">None</option>
                                <option value="tls" @selected(old('encryption', $setting->encryption) === 'tls')>TLS</option>
                                <option value="ssl" @selected(old('encryption', $setting->encryption) === 'ssl')>SSL</option>
                            </select>
                            @error('encryption')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">From Address</label>
                            <input name="from_address" value="{{ old('from_address', $setting->from_address) }}" @disabled(! $schoolScopeReady) class="mt-1 block w-full rounded-lg border-gray-300">
                            @error('from_address')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">From Name</label>
                            <input name="from_name" value="{{ old('from_name', $setting->from_name) }}" @disabled(! $schoolScopeReady) class="mt-1 block w-full rounded-lg border-gray-300">
                            @error('from_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reply-To Email</label>
                        <input name="reply_to_email" value="{{ old('reply_to_email', $setting->reply_to_email) }}" @disabled(! $schoolScopeReady) class="mt-1 block w-full rounded-lg border-gray-300">
                        @error('reply_to_email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button @disabled(! $schoolScopeReady) class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:bg-gray-400">Save Settings</button>
                    </div>
                </form>
            </div>

            <div class="space-y-6">
                <div class="rounded-lg bg-white p-6 text-sm text-gray-600 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Delivery Source</h3>
                    <dl class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-4">
                            <dt>School override</dt>
                            <dd class="font-medium {{ $setting->is_enabled ? 'text-green-700' : 'text-gray-700' }}">{{ $setting->is_enabled ? 'Enabled' : 'Disabled' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt>Platform fallback</dt>
                            <dd class="font-medium {{ $platformSetting->is_enabled ? 'text-green-700' : 'text-gray-700' }}">{{ $platformSetting->is_enabled ? 'Configured' : 'Default' }}</dd>
                        </div>
                    </dl>
                </div>

                <form id="school-mail-test-form" method="POST" action="{{ route('school.mail-settings.test') }}" data-loading-text="Testing..." class="rounded-lg bg-white p-6 shadow-sm">
                    @csrf
                    <h3 class="text-base font-semibold text-gray-900">Send Test Email</h3>
                    <div id="school-mail-test-payload"></div>
                    <label class="mt-4 block text-sm font-medium text-gray-700">Recipient</label>
                    <input name="test_email" value="{{ old('test_email', auth()->user()->email) }}" @disabled(! $schoolScopeReady) class="mt-1 block w-full rounded-lg border-gray-300">
                    @error('test_email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <button @disabled(! $schoolScopeReady) class="mt-4 w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:bg-gray-400">Send Test</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const settingsForm = document.getElementById('school-mail-settings-form');
            const testForm = document.getElementById('school-mail-test-form');
            const payload = document.getElementById('school-mail-test-payload');

            if (!settingsForm || !testForm || !payload) {
                return;
            }

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
