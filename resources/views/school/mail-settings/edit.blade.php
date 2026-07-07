<x-app-layout>
    @php
        $activeProviders = $providers->where('is_enabled', true);
        $primaryProvider = $activeProviders->firstWhere('is_primary', true) ?? $activeProviders->first();
        $secondaryCount = $activeProviders->where('id', '!=', $primaryProvider?->id)->count();
        $lastSuccess = $recentAttempts->firstWhere('status', 'accepted_by_smtp');
        $lastFailure = $recentAttempts->first(fn ($attempt) => $attempt->status !== 'accepted_by_smtp' && $attempt->status !== 'fallback_accepted');
        $formProvider = $editingProvider ?? new \App\Models\SchoolMailProviderProfile([
            'provider_type' => 'gmail',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'encryption' => 'ssl',
            'timeout' => 10,
            'priority' => max(10, ((int) $providers->max('priority')) + 10),
            'is_enabled' => true,
            'is_primary' => $providers->isEmpty(),
        ]);
        $fallbackLabel = strtoupper($platformStatus['driver']).($platformStatus['external_delivery'] ? '' : ' — non-delivery');
    @endphp

    <div
        class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8"
        x-data="{
            showProviderForm: {{ ($editingProvider || $errors->any()) ? 'true' : 'false' }},
            step: 1,
            providerType: @js(old('provider_type', $formProvider->provider_type)),
            applyPreset(type) {
                this.providerType = type;
                const host = document.querySelector('[name=host]');
                if ((type === 'gmail' || type === 'google_workspace') && host) host.value = 'smtp.gmail.com';
            }
        }"
    >
        <header class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 px-5 py-7 text-white shadow-xl sm:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-200">School communications</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Email Delivery</h1>
                    <span class="sr-only">School Mail Settings</span>
                    <p class="mt-3 text-sm leading-6 text-slate-300 sm:text-base">Connect and manage the email providers used for admissions, password resets, invoices, announcements, and other school notifications.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="showProviderForm = true; step = 1; $nextTick(() => document.getElementById('provider-form')?.scrollIntoView({ behavior: 'smooth' }))" class="inline-flex min-h-11 items-center rounded-xl bg-white px-4 text-sm font-semibold text-slate-950 hover:bg-indigo-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Add Email Provider</button>
                    <a href="#full-chain-test" class="inline-flex min-h-11 items-center rounded-xl border border-white/30 px-4 text-sm font-semibold hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Send Test Email</a>
                    <a href="{{ route('school.mail-settings.history') }}" class="inline-flex min-h-11 items-center rounded-xl border border-white/30 px-4 text-sm font-semibold hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">View Delivery History</a>
                    <a href="{{ route('school.dashboard') }}" class="inline-flex min-h-11 items-center rounded-xl border border-white/30 px-4 text-sm font-semibold hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">Back to Dashboard</a>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div role="alert" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                <p class="font-semibold">{{ session('success') }}</p>
                @if (session('mail_test_notice'))<p class="mt-1 font-normal">{{ session('mail_test_notice') }}</p>@endif
            </div>
        @endif
        @if (session('warning'))
            <div role="alert" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ session('warning') }}</div>
        @endif
        @if (session('error'))
            <div role="alert" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ session('error') }}</div>
        @endif

        @if (session('mail_test_result'))
            @php($testResult = session('mail_test_result'))
            <section aria-labelledby="latest-test-result" class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Latest synchronous test</p><h2 id="latest-test-result" class="mt-1 text-lg font-bold text-slate-950">Accepted by SMTP</h2></div>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">{{ $testResult['provider_name'] ?? strtoupper($testResult['transport'] ?? 'SMTP') }}</span>
                </div>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Host</dt><dd class="mt-1 break-all font-medium text-slate-900">{{ $testResult['host'] ?? 'Platform managed' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Port / encryption</dt><dd class="mt-1 font-medium text-slate-900">{{ $testResult['port'] ?? '—' }} / {{ strtoupper($testResult['encryption'] ?? '—') }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Sender / recipient</dt><dd class="mt-1 break-all font-medium text-slate-900">{{ $testResult['sender'] ?? 'Platform sender' }} → {{ $testResult['recipient'] ?? '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Timestamp</dt><dd class="mt-1 font-medium text-slate-900">{{ $testResult['accepted_at'] ?? now()->toIso8601String() }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Configuration</dt><dd class="mt-1 font-medium text-slate-900">{{ ucfirst($testResult['configuration'] ?? 'saved') }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Position</dt><dd class="mt-1 font-medium text-slate-900">{{ ucfirst($testResult['provider_position'] ?? (($testResult['fallback_used'] ?? false) ? 'fallback' : 'primary')) }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase text-slate-500">Provider message ID</dt><dd class="mt-1 break-all font-mono text-xs text-slate-900">{{ $testResult['provider_message_id'] ?? 'Not provided by transport' }}</dd></div>
                </dl>
                <p class="mt-4 text-xs leading-5 text-slate-600">SMTP acceptance means the sending server accepted the message. It does not guarantee Inbox placement.</p>
            </section>
        @endif

        <section aria-labelledby="delivery-health-heading">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 id="delivery-health-heading" class="text-xl font-bold text-slate-950">Delivery health</h2>
                    <p class="mt-1 text-sm text-slate-600">SMTP acceptance is reported separately from Inbox placement.</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $activeProviders->isNotEmpty() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $activeProviders->isNotEmpty() ? 'Healthy configuration' : 'Needs attention' }}
                </span>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                @foreach ([
                    ['Active Providers', $activeProviders->count(), $activeProviders->isNotEmpty() ? 'Healthy' : 'Incomplete'],
                    ['Primary Provider', $primaryProvider?->name ?? 'None', $primaryProvider ? 'Enabled' : 'Incomplete'],
                    ['Secondary Providers', $secondaryCount, $secondaryCount ? 'Ready for failover' : 'None enabled'],
                    ['Platform Fallback', $fallbackLabel, $platformFallbackEnabled ? 'Policy enabled' : 'Disabled'],
                    ['Last Successful Test', $lastSuccess?->created_at?->diffForHumans() ?? 'Not run', $lastSuccess?->provider_name ?? '—'],
                    ['Last Failed Attempt', $lastFailure?->created_at?->diffForHumans() ?? 'None', $lastFailure?->safe_error_category ?? '—'],
                ] as [$label, $value, $note])
                    <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
                        <p class="mt-2 truncate text-lg font-bold text-slate-950" title="{{ $value }}">{{ $value }}</p>
                        <p class="mt-1 truncate text-xs text-slate-500">{{ $note }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section aria-labelledby="providers-heading" class="space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 id="providers-heading" class="text-xl font-bold text-slate-950">Email providers</h2>
                    <p class="mt-1 text-sm text-slate-600">The primary provider is tried first. Secondary providers follow in priority order.</p>
                </div>
            </div>

            @forelse ($providers as $provider)
                @php($password = $providerService->passwordState($provider))
                <article class="overflow-hidden rounded-3xl border {{ $provider->is_primary ? 'border-indigo-300 ring-1 ring-indigo-200' : 'border-slate-200' }} bg-white shadow-sm" data-provider-card data-provider-id="{{ $provider->id }}">
                    <div class="grid gap-5 p-5 lg:grid-cols-[1.2fr_1fr] lg:p-6">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="mr-2 text-xl font-bold text-slate-950">{{ $provider->name }}</h3>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $providerTypes[$provider->provider_type] ?? 'Custom SMTP' }}</span>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $provider->is_primary ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700' }}">{{ $provider->is_primary ? 'Primary' : 'Secondary' }}</span>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $provider->is_enabled ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">{{ $provider->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $providerService->isComplete($provider) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $providerService->isComplete($provider) ? 'Configuration complete' : 'Incomplete' }}</span>
                            </div>
                            <dl class="mt-5 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2 xl:grid-cols-3">
                                <div><dt class="text-xs font-semibold uppercase text-slate-500">Host</dt><dd class="mt-1 break-all font-medium text-slate-900">{{ $provider->host }}</dd></div>
                                <div><dt class="text-xs font-semibold uppercase text-slate-500">Port / Encryption</dt><dd class="mt-1 font-medium text-slate-900">{{ $provider->port }} / {{ strtoupper($provider->encryption) }}</dd></div>
                                <div><dt class="text-xs font-semibold uppercase text-slate-500">From Address</dt><dd class="mt-1 break-all font-medium text-slate-900">{{ $provider->from_address }}</dd></div>
                                <div><dt class="text-xs font-semibold uppercase text-slate-500">Password</dt><dd class="mt-1 font-medium {{ $password['unusable'] ? 'text-red-700' : 'text-slate-900' }}">{{ $password['unusable'] ? 'Needs re-entry' : ($password['available'] ? 'Available' : 'Not set') }}</dd></div>
                                <div><dt class="text-xs font-semibold uppercase text-slate-500">Last test</dt><dd class="mt-1 font-medium text-slate-900">{{ $provider->last_test_status ? str($provider->last_test_status)->replace('_', ' ')->title() : 'Not run' }}</dd></div>
                                <div><dt class="text-xs font-semibold uppercase text-slate-500">Last error</dt><dd class="mt-1 font-medium {{ $provider->last_error_category ? 'text-red-700' : 'text-slate-500' }}">{{ $provider->last_error_category ?? 'None' }}</dd></div>
                            </dl>
                            <div class="mt-5 flex flex-wrap gap-2">
                                <a href="{{ route('school.mail-settings.edit', ['provider' => $provider->id]) }}#provider-form" class="inline-flex min-h-10 items-center rounded-xl border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Edit</a>
                                @unless ($provider->is_primary)
                                    <form method="POST" action="{{ route('school.mail-settings.providers.primary', $provider) }}">@csrf<button class="min-h-10 rounded-xl border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Make Primary</button></form>
                                @endunless
                                <form method="POST" action="{{ route('school.mail-settings.providers.toggle', $provider) }}">@csrf<button class="min-h-10 rounded-xl border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ $provider->is_enabled ? 'Disable' : 'Enable' }}</button></form>
                                @unless ($provider->is_primary)
                                    <form method="POST" action="{{ route('school.mail-settings.providers.move', $provider) }}">@csrf<input type="hidden" name="direction" value="up"><button class="min-h-10 rounded-xl border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50" aria-label="Move {{ $provider->name }} up">Move up</button></form>
                                    <form method="POST" action="{{ route('school.mail-settings.providers.move', $provider) }}">@csrf<input type="hidden" name="direction" value="down"><button class="min-h-10 rounded-xl border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50" aria-label="Move {{ $provider->name }} down">Move down</button></form>
                                @endunless
                                <a href="{{ route('school.mail-settings.history', ['provider' => $provider->id]) }}" class="inline-flex min-h-10 items-center rounded-xl border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">View attempts</a>
                                <form method="POST" action="{{ route('school.mail-settings.providers.destroy', $provider) }}" onsubmit="return confirm('Delete this provider? Delivery history will be retained.')">@csrf @method('DELETE')<button class="min-h-10 rounded-xl border border-red-200 px-3 text-sm font-semibold text-red-700 hover:bg-red-50">Delete</button></form>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('school.mail-settings.providers.test', $provider) }}" class="rounded-2xl bg-slate-50 p-4 sm:p-5">
                            @csrf
                            <h4 class="font-bold text-slate-950">Test {{ $provider->name }}</h4>
                            <p class="mt-1 text-xs leading-5 text-slate-600">Uses only this provider, synchronously. It never invokes a secondary provider or platform fallback.</p>
                            <label class="mt-4 block text-sm font-semibold text-slate-700" for="test-email-{{ $provider->id }}">Test recipient</label>
                            <input id="test-email-{{ $provider->id }}" name="test_email" type="email" required value="{{ old('test_email', auth()->user()->email) }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <label class="mt-3 block text-sm font-semibold text-slate-700" for="test-subject-{{ $provider->id }}">Optional subject label</label>
                            <input id="test-subject-{{ $provider->id }}" name="subject_label" class="mt-1 block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button class="mt-4 min-h-11 w-full rounded-xl bg-slate-950 px-4 text-sm font-bold text-white hover:bg-indigo-950 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Test Provider</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border-2 border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                    <h3 class="text-lg font-bold text-slate-950">No email provider yet</h3>
                    <p class="mx-auto mt-2 max-w-xl text-sm text-slate-600">Add Gmail, Google Workspace, cPanel/Webmail, or a custom SMTP server. Existing legacy settings are imported automatically by the migration.</p>
                    <button type="button" @click="showProviderForm = true" class="mt-5 min-h-11 rounded-xl bg-slate-950 px-5 text-sm font-bold text-white">Add Email Provider</button>
                </div>
            @endforelse
        </section>

        <section id="provider-form" x-cloak x-show="showProviderForm" x-transition class="scroll-mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Guided setup</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-950">{{ $editingProvider ? 'Edit '.$editingProvider->name : 'Add email provider' }}</h2>
                </div>
                <button type="button" @click="showProviderForm = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Close provider form">✕</button>
            </div>
            <ol class="mt-5 grid grid-cols-5 gap-1" aria-label="Provider setup progress">
                @foreach (['Provider', 'Connection', 'Sender', 'Delivery role', 'Review'] as $index => $label)
                    <li><button type="button" @click="step = {{ $index + 1 }}" :class="step === {{ $index + 1 }} ? 'bg-indigo-700 text-white' : 'bg-slate-100 text-slate-600'" class="min-h-10 w-full rounded-lg px-1 text-[10px] font-bold sm:text-xs"><span class="hidden sm:inline">{{ $index + 1 }}. </span>{{ $label }}</button></li>
                @endforeach
            </ol>

            <form method="POST" action="{{ $editingProvider ? route('school.mail-settings.providers.update', $editingProvider) : route('school.mail-settings.providers.store') }}" class="mt-6">
                @csrf
                @if ($editingProvider) @method('PUT') @endif

                <fieldset x-show="step === 1" class="grid gap-4 sm:grid-cols-2">
                    <legend class="sr-only">Choose provider type</legend>
                    @foreach ($providerTypes as $type => $label)
                        <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 hover:border-indigo-300" :class="providerType === '{{ $type }}' ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : ''">
                            <input class="sr-only" type="radio" name="provider_type" value="{{ $type }}" @checked(old('provider_type', $formProvider->provider_type) === $type) @change="applyPreset('{{ $type }}')">
                            <span class="font-bold text-slate-950">{{ $label }}</span>
                            <span class="mt-1 block text-xs text-slate-600">{{ in_array($type, ['gmail', 'google_workspace']) ? 'Google App Password with smtp.gmail.com.' : ($type === 'cpanel' ? 'Exact outgoing server from cPanel Connect Devices.' : 'Any standards-compliant SMTP service.') }}</span>
                        </label>
                    @endforeach
                </fieldset>

                <fieldset x-show="step === 2" class="grid gap-4 sm:grid-cols-2">
                    <legend class="mb-4 text-lg font-bold text-slate-950 sm:col-span-2">Connection details</legend>
                    <div><label class="text-sm font-semibold text-slate-700">Provider name</label><input name="name" required value="{{ old('name', $formProvider->name) }}" class="mt-1 block w-full rounded-xl border-slate-300">@error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                    <div><label class="text-sm font-semibold text-slate-700">Host</label><input name="host" required value="{{ old('host', $formProvider->host) }}" class="mt-1 block w-full rounded-xl border-slate-300">@error('host')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                    <div><label class="text-sm font-semibold text-slate-700">Port</label><select name="port" class="mt-1 block w-full rounded-xl border-slate-300"><option value="465" @selected((int) old('port', $formProvider->port) === 465)>465</option><option value="587" @selected((int) old('port', $formProvider->port) === 587)>587</option></select></div>
                    <div><label class="text-sm font-semibold text-slate-700">Encryption</label><select name="encryption" class="mt-1 block w-full rounded-xl border-slate-300"><option value="ssl" @selected(old('encryption', $formProvider->encryption) === 'ssl')>SSL (port 465)</option><option value="tls" @selected(old('encryption', $formProvider->encryption) === 'tls')>TLS (port 587)</option></select>@error('encryption')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                    <div><label class="text-sm font-semibold text-slate-700">Username</label><input name="username" type="email" value="{{ old('username', $formProvider->username) }}" autocomplete="username" class="mt-1 block w-full rounded-xl border-slate-300"><p class="mt-1 text-xs text-slate-500">Use the full email address.</p></div>
                    <div><label class="text-sm font-semibold text-slate-700">Password / App Password</label><input name="password" type="password" autocomplete="new-password" class="mt-1 block w-full rounded-xl border-slate-300" placeholder="{{ $editingProvider ? 'Leave blank to keep saved password' : 'Enter provider password' }}"><p class="mt-1 text-xs text-slate-500">Never displayed. Blank or a masked value preserves the saved encrypted password.</p>@error('password')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                    <div><label class="text-sm font-semibold text-slate-700">Timeout (seconds)</label><input name="timeout" type="number" min="1" max="120" required value="{{ old('timeout', $formProvider->timeout) }}" class="mt-1 block w-full rounded-xl border-slate-300"></div>
                </fieldset>

                <fieldset x-show="step === 3" class="grid gap-4 sm:grid-cols-2">
                    <legend class="mb-4 text-lg font-bold text-slate-950 sm:col-span-2">Sender identity</legend>
                    <div><label class="text-sm font-semibold text-slate-700">From Address</label><input name="from_address" type="email" required value="{{ old('from_address', $formProvider->from_address) }}" class="mt-1 block w-full rounded-xl border-slate-300">@error('from_address')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror</div>
                    <div><label class="text-sm font-semibold text-slate-700">From Name</label><input name="from_name" value="{{ old('from_name', $formProvider->from_name ?: $school->name) }}" class="mt-1 block w-full rounded-xl border-slate-300"></div>
                    <div><label class="text-sm font-semibold text-slate-700">Reply-To Address</label><input name="reply_to_address" type="email" value="{{ old('reply_to_address', $formProvider->reply_to_address) }}" class="mt-1 block w-full rounded-xl border-slate-300"></div>
                    <div><label class="text-sm font-semibold text-slate-700">Reply-To Name</label><input name="reply_to_name" value="{{ old('reply_to_name', $formProvider->reply_to_name) }}" class="mt-1 block w-full rounded-xl border-slate-300"></div>
                </fieldset>

                <fieldset x-show="step === 4" class="grid gap-4 sm:grid-cols-3">
                    <legend class="mb-4 text-lg font-bold text-slate-950 sm:col-span-3">Delivery role</legend>
                    <label class="flex min-h-16 items-center gap-3 rounded-2xl border border-slate-200 p-4"><input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $formProvider->is_enabled)) class="rounded border-slate-300"><span><span class="block font-bold text-slate-900">Enabled</span><span class="text-xs text-slate-500">Available to the chain</span></span></label>
                    <label class="flex min-h-16 items-center gap-3 rounded-2xl border border-slate-200 p-4"><input type="checkbox" name="is_primary" value="1" @checked(old('is_primary', $formProvider->is_primary)) class="rounded border-slate-300"><span><span class="block font-bold text-slate-900">Primary</span><span class="text-xs text-slate-500">Tried before secondaries</span></span></label>
                    <div><label class="text-sm font-semibold text-slate-700">Secondary priority</label><input name="priority" type="number" min="1" max="10000" required value="{{ old('priority', $formProvider->priority) }}" class="mt-1 block w-full rounded-xl border-slate-300"><p class="mt-1 text-xs text-slate-500">Lower numbers run first.</p></div>
                </fieldset>

                <fieldset x-show="step === 5" class="space-y-4">
                    <legend class="text-lg font-bold text-slate-950">Review and test</legend>
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="rounded-2xl bg-indigo-50 p-4 text-sm leading-6 text-indigo-950" x-show="providerType === 'gmail' || providerType === 'google_workspace'">
                            <h3 class="font-bold">Gmail / Google Workspace</h3>
                            <p>Enable 2-Step Verification, create a Google App Password, and use it instead of the normal account password. Use smtp.gmail.com with 465/SSL or 587/TLS. The From Address must be the account or an authorised alias.</p>
                        </div>
                        <div class="rounded-2xl bg-amber-50 p-4 text-sm leading-6 text-amber-950" x-show="providerType === 'cpanel'">
                            <h3 class="font-bold">cPanel / Webmail</h3>
                            <p>Open Email Accounts → Connect Devices. Use the exact outgoing server, full mailbox address, mailbox password, and matching From Address. Use 465/SSL or 587/TLS.</p>
                        </div>
                        @unless ($editingProvider)
                            <div class="rounded-2xl border border-slate-200 p-4 lg:col-span-2">
                                <label class="text-sm font-semibold text-slate-700">Recipient for “Test Without Saving”</label>
                                <input name="test_email" type="email" value="{{ old('test_email', auth()->user()->email) }}" class="mt-1 block w-full rounded-xl border-slate-300">
                                <label class="mt-3 block text-sm font-semibold text-slate-700">Optional subject label</label>
                                <input name="subject_label" class="mt-1 block w-full rounded-xl border-slate-300">
                            </div>
                        @endunless
                    </div>
                </fieldset>

                <div class="mt-7 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-5">
                    <button type="button" @click="step = Math.max(1, step - 1)" class="min-h-11 rounded-xl border border-slate-300 px-4 text-sm font-bold text-slate-700">Previous</button>
                    <div class="flex flex-wrap gap-2">
                        @unless ($editingProvider)
                            <button type="submit" formaction="{{ route('school.mail-settings.providers.test-temporary') }}" class="min-h-11 rounded-xl border border-indigo-300 px-4 text-sm font-bold text-indigo-800">Test Without Saving</button>
                        @endunless
                        <button type="submit" name="save_action" value="save" class="min-h-11 rounded-xl bg-slate-950 px-4 text-sm font-bold text-white">{{ $editingProvider ? 'Save Provider' : 'Save Provider' }}</button>
                        @unless ($editingProvider)<button type="submit" name="save_action" value="save_and_test" class="min-h-11 rounded-xl bg-indigo-700 px-4 text-sm font-bold text-white">Save and Test</button>@endunless
                        <button type="button" @click="step = Math.min(5, step + 1)" x-show="step < 5" class="min-h-11 rounded-xl border border-slate-300 px-4 text-sm font-bold text-slate-700">Next</button>
                    </div>
                </div>
            </form>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section id="full-chain-test" class="scroll-mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Primary → secondary</p>
                <h2 class="mt-1 text-xl font-bold text-slate-950">Test Full Delivery Chain</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Records each failed provider, stops at the first SMTP acceptance, and never attempts another provider after acceptance.</p>
                <form method="POST" action="{{ route('school.mail-settings.test-chain') }}" class="mt-5 grid gap-3">
                    @csrf
                    <div><label class="text-sm font-semibold text-slate-700">Test recipient</label><input name="test_email" type="email" required value="{{ auth()->user()->email }}" class="mt-1 block w-full rounded-xl border-slate-300"></div>
                    <div><label class="text-sm font-semibold text-slate-700">Optional subject label</label><input name="subject_label" class="mt-1 block w-full rounded-xl border-slate-300"></div>
                    <button class="min-h-11 rounded-xl bg-indigo-700 px-4 text-sm font-bold text-white">Test Full Delivery Chain</button>
                </form>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Separate safety net</p>
                <h2 class="mt-1 text-xl font-bold text-slate-950">Platform Fallback</h2>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-3"><dt class="text-xs font-semibold uppercase text-slate-500">Driver</dt><dd class="mt-1 font-bold text-slate-900">{{ strtoupper($platformStatus['driver']) }}</dd></div>
                    <div class="rounded-xl bg-slate-50 p-3"><dt class="text-xs font-semibold uppercase text-slate-500">Policy</dt><dd class="mt-1 font-bold text-slate-900">{{ $platformFallbackEnabled ? 'Enabled' : 'Disabled' }}</dd></div>
                    <div class="rounded-xl bg-slate-50 p-3"><dt class="text-xs font-semibold uppercase text-slate-500">External delivery</dt><dd class="mt-1 font-bold text-slate-900">{{ $platformStatus['external_delivery'] ? 'Possible' : 'No' }}</dd></div>
                    <div class="rounded-xl bg-slate-50 p-3"><dt class="text-xs font-semibold uppercase text-slate-500">Status</dt><dd class="mt-1 font-bold text-slate-900">{{ $platformStatus['configured'] ? 'Configured' : 'Incomplete' }}</dd></div>
                </dl>
                @if (in_array($platformStatus['driver'], ['log', 'array']))
                    <p class="mt-4 rounded-xl bg-amber-50 p-3 text-sm leading-6 text-amber-900"><strong>{{ strtoupper($platformStatus['driver']) }} — non-delivery.</strong> {{ $platformStatus['driver'] === 'log' ? 'Messages are written to the application log and are not sent externally.' : 'Messages remain in memory for testing and are not sent externally.' }}</p>
                @endif
                <form method="POST" action="{{ route('school.mail-settings.test-fallback') }}" class="mt-4 flex flex-col gap-2 sm:flex-row">
                    @csrf
                    <label class="sr-only" for="fallback-recipient">Fallback test recipient</label>
                    <input id="fallback-recipient" name="test_email" type="email" required value="{{ auth()->user()->email }}" class="min-h-11 flex-1 rounded-xl border-slate-300">
                    <button class="min-h-11 rounded-xl border border-slate-300 px-4 text-sm font-bold text-slate-700">Test Platform Fallback</button>
                </form>
            </section>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 p-5 sm:p-6">
                <div><h2 class="text-xl font-bold text-slate-950">Recent delivery history</h2><p class="mt-1 text-sm text-slate-600">No message bodies or credentials are retained.</p></div>
                <a href="{{ route('school.mail-settings.history') }}" class="text-sm font-bold text-indigo-700 hover:text-indigo-900">View all</a>
            </div>
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Date</th><th class="px-5 py-3">Provider</th><th class="px-5 py-3">Recipient</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Kind</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">@forelse($recentAttempts as $attempt)<tr><td class="px-5 py-3 text-slate-600">{{ $attempt->created_at?->format('M j, Y H:i') }}</td><td class="px-5 py-3 font-semibold text-slate-900">{{ $attempt->provider_name ?? strtoupper($attempt->transport) }}</td><td class="px-5 py-3 text-slate-600">{{ $attempt->recipient ?? '—' }}</td><td class="px-5 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold {{ in_array($attempt->status, ['accepted_by_smtp', 'fallback_accepted']) ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">{{ str($attempt->status)->replace('_', ' ')->title() }}</span></td><td class="px-5 py-3 text-slate-600">{{ ucfirst($attempt->message_kind ?? 'transactional') }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">No delivery attempts recorded yet.</td></tr>@endforelse</tbody>
                </table>
            </div>
            <div class="divide-y divide-slate-100 md:hidden">@forelse($recentAttempts as $attempt)<article class="p-4"><div class="flex items-start justify-between gap-3"><p class="font-bold text-slate-900">{{ $attempt->provider_name ?? strtoupper($attempt->transport) }}</p><span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold">{{ str($attempt->status)->replace('_', ' ')->title() }}</span></div><p class="mt-2 break-all text-sm text-slate-600">{{ $attempt->recipient ?? 'No recipient recorded' }}</p><p class="mt-1 text-xs text-slate-500">{{ $attempt->created_at?->format('M j, Y H:i') }}</p></article>@empty<p class="p-8 text-center text-sm text-slate-500">No delivery attempts recorded yet.</p>@endforelse</div>
        </section>
    </div>
</x-app-layout>
