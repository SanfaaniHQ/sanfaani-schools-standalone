<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Admin / Settings / Mail</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Platform Mail Settings</h2>
                <p class="mt-1 text-sm text-gray-500">Configure global SMTP fallback and super admin communications.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-6xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="lg:col-span-2">
                @if (session('success'))
                    <div class="mb-6 rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.mail-settings.update') }}" data-loading-text="Saving..." class="space-y-6 rounded-2xl bg-white p-6 shadow-sm">
                    @csrf
                    @method('PATCH')

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mailer</label>
                            <select name="mailer" class="mt-1 block w-full rounded-xl border-gray-300">
                                <option value="log" @selected(old('mailer', $setting->mailer) === 'log')>Log</option>
                                <option value="smtp" @selected(old('mailer', $setting->mailer) === 'smtp')>SMTP</option>
                            </select>
                        </div>
                        <label class="mt-6 flex items-center gap-3 rounded-xl border border-gray-200 p-4 text-sm text-gray-700">
                            <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $setting->is_enabled)) class="rounded border-gray-300">
                            Enable dashboard mail settings
                        </label>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Host</label>
                            <input name="host" value="{{ old('host', $setting->host) }}" class="mt-1 block w-full rounded-xl border-gray-300">
                            @error('host')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Port</label>
                            <input name="port" type="number" value="{{ old('port', $setting->port) }}" class="mt-1 block w-full rounded-xl border-gray-300">
                            @error('port')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Username</label>
                            <input name="username" value="{{ old('username', $setting->username) }}" class="mt-1 block w-full rounded-xl border-gray-300">
                            @error('username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input name="password" type="password" placeholder="{{ $masker->maskedPassword($setting) }}" autocomplete="new-password" class="mt-1 block w-full rounded-xl border-gray-300">
                            <p class="mt-1 text-xs text-gray-500">Leave blank to keep the current encrypted password.</p>
                            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Encryption</label>
                            <select name="encryption" class="mt-1 block w-full rounded-xl border-gray-300">
                                <option value="none" @selected(in_array(old('encryption', $setting->encryption), [null, '', 'none'], true))>None</option>
                                <option value="tls" @selected(old('encryption', $setting->encryption) === 'tls')>TLS</option>
                                <option value="ssl" @selected(old('encryption', $setting->encryption) === 'ssl')>SSL</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">From Address</label>
                            <input name="from_address" value="{{ old('from_address', $setting->from_address) }}" class="mt-1 block w-full rounded-xl border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">From Name</label>
                            <input name="from_name" value="{{ old('from_name', $setting->from_name) }}" class="mt-1 block w-full rounded-xl border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reply-To Email</label>
                            <input name="reply_to_email" value="{{ old('reply_to_email', $setting->reply_to_email) }}" class="mt-1 block w-full rounded-xl border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Connection Timeout (seconds)</label>
                            <input name="timeout" type="number" min="1" max="120" value="{{ old('timeout', data_get($setting->metadata, 'timeout', 10)) }}" class="mt-1 block w-full rounded-xl border-gray-300">
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <h3 class="text-sm font-semibold text-gray-900">School Mail Governance</h3>
                        <div class="mt-4 grid gap-3 md:grid-cols-3">
                            <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-white p-3 text-sm text-gray-700">
                                <input type="checkbox" name="school_custom_smtp_enabled" value="1" @checked(old('school_custom_smtp_enabled', data_get($mailGovernance, 'school_custom_smtp_enabled', true))) class="mt-1 rounded border-gray-300">
                                <span>
                                    <span class="block font-semibold text-gray-900">Allow school SMTP</span>
                                    <span class="mt-1 block text-xs text-gray-500">Schools can configure encrypted SMTP credentials.</span>
                                </span>
                            </label>
                            <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-white p-3 text-sm text-gray-700">
                                <input type="checkbox" name="force_platform_mailer" value="1" @checked(old('force_platform_mailer', data_get($mailGovernance, 'force_platform_mailer', false))) class="mt-1 rounded border-gray-300">
                                <span>
                                    <span class="block font-semibold text-gray-900">Platform SMTP only</span>
                                    <span class="mt-1 block text-xs text-gray-500">Force all school delivery through platform settings.</span>
                                </span>
                            </label>
                            <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-white p-3 text-sm text-gray-700">
                                <input type="checkbox" name="platform_fallback_enabled" value="1" @checked(old('platform_fallback_enabled', data_get($mailGovernance, 'platform_fallback_enabled', true))) class="mt-1 rounded border-gray-300">
                                <span>
                                    <span class="block font-semibold text-gray-900">Enable fallback</span>
                                    <span class="mt-1 block text-xs text-gray-500">Retry school SMTP failures through platform mail.</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">Please fix the highlighted fields.</div>
                    @endif

                    <div class="flex justify-end">
                        <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Save Mail Settings</button>
                    </div>
                </form>
            </div>

            <div class="space-y-6">
                <form method="POST" action="{{ route('admin.mail-settings.test') }}" data-loading-text="Sending..." class="rounded-2xl bg-white p-6 shadow-sm">
                    @csrf
                    <h3 class="text-base font-semibold text-gray-900">Send Test Email</h3>
                    <label class="mt-4 block text-sm font-medium text-gray-700">Recipient</label>
                    <input name="test_email" value="{{ old('test_email', auth()->user()->email) }}" class="mt-1 block w-full rounded-xl border-gray-300">
                    <button class="mt-4 w-full rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Send Test</button>
                </form>

                <div class="rounded-2xl bg-white p-6 text-sm text-gray-600 shadow-sm">
                    <h3 class="font-semibold text-gray-900">Fallback Behavior</h3>
                    <p class="mt-2">If dashboard mail settings are disabled, Laravel uses the values from .env. Local and staging environments can use MAIL_MAILER=log safely.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
