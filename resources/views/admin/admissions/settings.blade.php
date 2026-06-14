<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Admission settings</h1>
                <p class="mt-1 text-sm text-gray-500">Open or close admissions, update requirements, and share the public form.</p>
            </div>
            <a href="{{ route('admin.admissions.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700">Back to admissions</a>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-5 rounded-lg bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif

    @if (session('admission_api_plain_key'))
        <div class="mb-5 rounded-lg bg-amber-50 p-4 text-sm text-amber-900">
            <strong>New website key, shown once:</strong>
            <code class="mt-2 block break-all rounded bg-gray-900 p-3 text-white">{{ session('admission_api_plain_key') }}</code>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-lg bg-rose-50 p-4 text-sm font-semibold text-rose-800">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-lg border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Admission cycle</h2>
            <p class="mt-1 text-sm text-gray-500">Parents can submit applications only when the cycle is open.</p>

            <form method="POST" action="{{ route('admin.admissions.settings.update') }}" data-loading-text="Saving admission settings..." class="mt-4 grid gap-4">
                @csrf

                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Cycle name
                    <input name="name" value="{{ old('name', $cycle?->name) }}" required class="rounded-lg border-gray-300">
                </label>

                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Academic session
                    <select name="academic_session_id" class="rounded-lg border-gray-300">
                        <option value="">No linked session</option>
                        @foreach ($sessions as $session)
                            <option value="{{ $session->id }}" @selected((string) old('academic_session_id', $cycle?->academic_session_id) === (string) $session->id)>{{ $session->name }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Opens
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $cycle?->starts_at?->format('Y-m-d\TH:i')) }}" class="rounded-lg border-gray-300">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold text-gray-700">
                        Closes
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $cycle?->ends_at?->format('Y-m-d\TH:i')) }}" class="rounded-lg border-gray-300">
                    </label>
                </div>

                <label class="grid gap-1 text-sm font-semibold text-gray-700">
                    Requirements, one per line
                    <textarea name="requirements" rows="6" class="rounded-lg border-gray-300" placeholder="Birth certificate&#10;Previous school report&#10;Passport photograph">{{ old('requirements', implode("\n", data_get($cycle?->settings, 'requirements', []))) }}</textarea>
                    <span class="text-xs font-normal text-gray-500">These requirements appear on the public admission page.</span>
                </label>

                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <input type="checkbox" name="is_open" value="1" @checked(old('is_open', $cycle?->is_open))>
                    Accept public applications
                </label>

                <button type="submit" data-loading-text="Saving admission settings..." class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Save admission settings</button>
            </form>
        </section>

        <div class="space-y-6">
            <section class="rounded-lg border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Public form and website sharing</h2>
                <p class="mt-1 text-sm text-gray-500">Use these links for the school website, WhatsApp broadcasts, email, or printed admission instructions.</p>

                <div class="mt-4 space-y-3">
                    <div class="rounded-md border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                        <span class="block text-xs font-semibold uppercase tracking-normal text-gray-500">Application form</span>
                        <span class="mt-1 block break-all font-mono">{{ $publicFormUrl }}</span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" data-copy-text="{{ $publicFormUrl }}" data-copied-label="Form link copied" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white">Copy form link</button>
                        <a href="{{ $publicFormUrl }}" target="_blank" rel="noopener" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700">Preview form</a>
                        <a href="{{ route('admin.admissions.applications.index') }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700">Review applications</a>
                    </div>
                </div>

                <div class="mt-5 rounded-md border border-gray-200 p-4 text-sm text-gray-600">
                    <p class="font-semibold text-gray-900">Embed on a school website</p>
                    @if (config('admissions.embed_enabled'))
                        <p class="mt-1">Ask the website manager to add this iframe where the admission form should appear.</p>
                        <code class="mt-3 block break-all rounded bg-gray-900 p-3 text-xs text-white">&lt;iframe src="{{ $publicEmbedUrl }}" width="100%" height="900" style="border:0;"&gt;&lt;/iframe&gt;</code>
                    @else
                        <p class="mt-1">Website embed is disabled. Add the form link to the website instead.</p>
                    @endif
                </div>
            </section>

            <section class="rounded-lg border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Website channels</h2>
                <p class="mt-1 text-sm text-gray-500">Channels help you see whether an application came from the main website, social media, or a partner link.</p>

                <form method="POST" action="{{ route('admin.admissions.channels.store') }}" data-loading-text="Adding channel..." class="mt-4 grid gap-3">
                    @csrf
                    <input name="name" placeholder="Channel name, e.g. main-website" required class="rounded-lg border-gray-300">
                    <select name="type" class="rounded-lg border-gray-300">
                        @foreach (\App\Models\Admissions\AdmissionChannel::TYPES as $type)
                            <option value="{{ $type }}">{{ str($type)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                    <input name="allowed_domain" placeholder="Website domain, optional" class="rounded-lg border-gray-300">
                    <button type="submit" data-loading-text="Adding channel..." class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700">Add channel</button>
                </form>

                <div class="mt-4 space-y-2">
                    @forelse ($channels as $channel)
                        <div class="rounded-lg bg-gray-50 p-3 text-sm text-gray-700">
                            <strong class="text-gray-900">{{ $channel->name }}</strong>
                            <span class="mx-1">-</span>
                            {{ str($channel->type)->replace('_', ' ')->title() }}
                            <span class="mx-1">-</span>
                            {{ $channel->allowed_domain ?: 'No website limit' }}
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No website channels have been added yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-lg border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Website API access</h2>
                <p class="mt-1 text-sm text-gray-500">For custom websites handled by a developer. Most schools only need the public form link above.</p>
                <p class="mt-2 text-sm text-gray-500">Status: {{ config('admissions.api_enabled') ? 'Enabled' : 'Disabled in server settings' }}.</p>

                <form method="POST" action="{{ route('admin.admissions.api-keys.store') }}" data-loading-text="Creating website key..." class="mt-4 grid gap-3">
                    @csrf
                    <input name="name" placeholder="Key name" required class="rounded-lg border-gray-300">
                    <select name="channel_id" class="rounded-lg border-gray-300">
                        <option value="">No channel</option>
                        @foreach ($channels as $channel)
                            <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                        @endforeach
                    </select>
                    <input name="allowed_domain" placeholder="Website domain, recommended" class="rounded-lg border-gray-300">
                    <button type="submit" data-loading-text="Creating website key..." class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700">Create website key</button>
                </form>

                <div class="mt-4 space-y-2">
                    @forelse ($apiKeys as $key)
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-gray-50 p-3 text-sm text-gray-700">
                            <span><strong class="text-gray-900">{{ $key->name }}</strong> - {{ $key->is_active ? 'Active' : 'Revoked' }} - Last used {{ $key->last_used_at?->diffForHumans() ?? 'never' }}</span>
                            @if ($key->is_active)
                                <form method="POST" action="{{ route('admin.admissions.api-keys.revoke', $key) }}" data-loading-text="Revoking...">
                                    @csrf
                                    <button type="submit" data-loading-text="Revoking..." class="font-semibold text-rose-700">Revoke</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No website keys have been created.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
