<x-app-layout>
    <x-slot name="header"><div><h1 class="text-xl font-semibold">Admission settings</h1><p class="mt-1 text-sm text-gray-500">Manage the portal-owned cycle and website integration foundation.</p></div></x-slot>
    @if(session('success'))<div class="mb-5 rounded-xl bg-emerald-50 p-4 text-emerald-800">{{ session('success') }}</div>@endif
    @if(session('admission_api_plain_key'))<div class="mb-5 rounded-xl bg-amber-50 p-4 text-amber-900"><strong>New API key (shown once):</strong><code class="mt-2 block break-all rounded bg-gray-900 p-3 text-white">{{ session('admission_api_plain_key') }}</code></div>@endif
    @if($errors->any())<div class="mb-5 rounded-xl bg-rose-50 p-4 text-rose-800">{{ $errors->first() }}</div>@endif
    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Admission cycle</h2>
            <form method="POST" action="{{ route('admin.admissions.settings.update') }}" class="mt-4 grid gap-4">@csrf
                <label class="grid gap-1 text-sm font-semibold">Cycle name<input name="name" value="{{ old('name', $cycle?->name) }}" required class="rounded-lg border-gray-300"></label>
                <label class="grid gap-1 text-sm font-semibold">Academic session<select name="academic_session_id" class="rounded-lg border-gray-300"><option value="">No linked session</option>@foreach($sessions as $session)<option value="{{ $session->id }}" @selected((string) old('academic_session_id', $cycle?->academic_session_id) === (string) $session->id)>{{ $session->name }}</option>@endforeach</select></label>
                <div class="grid gap-3 sm:grid-cols-2"><label class="grid gap-1 text-sm font-semibold">Starts<input type="datetime-local" name="starts_at" value="{{ old('starts_at', $cycle?->starts_at?->format('Y-m-d\TH:i')) }}" class="rounded-lg border-gray-300"></label><label class="grid gap-1 text-sm font-semibold">Ends<input type="datetime-local" name="ends_at" value="{{ old('ends_at', $cycle?->ends_at?->format('Y-m-d\TH:i')) }}" class="rounded-lg border-gray-300"></label></div>
                <label class="grid gap-1 text-sm font-semibold">Requirements, one per line<textarea name="requirements" rows="6" class="rounded-lg border-gray-300">{{ old('requirements', implode("\n", data_get($cycle?->settings, 'requirements', []))) }}</textarea></label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_open" value="1" @checked(old('is_open', $cycle?->is_open))> Accept public applications</label>
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Save cycle</button>
            </form>
        </section>
        <div class="space-y-6">
            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Website channels</h2>
                <form method="POST" action="{{ route('admin.admissions.channels.store') }}" class="mt-4 grid gap-3">@csrf<input name="name" placeholder="Channel name, e.g. main-website" required class="rounded-lg border-gray-300"><select name="type" class="rounded-lg border-gray-300">@foreach(\App\Models\Admissions\AdmissionChannel::TYPES as $type)<option value="{{ $type }}">{{ str($type)->replace('_', ' ')->title() }}</option>@endforeach</select><input name="allowed_domain" placeholder="Allowed domain (optional)" class="rounded-lg border-gray-300"><button class="rounded-lg border px-4 py-2 text-sm font-semibold">Add channel</button></form>
                <div class="mt-4 space-y-2">@foreach($channels as $channel)<div class="rounded-lg bg-gray-50 p-3 text-sm"><strong>{{ $channel->name }}</strong> · {{ $channel->type }} · {{ $channel->allowed_domain ?: 'No domain restriction' }}</div>@endforeach</div>
            </section>
            <section class="rounded-2xl border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">API foundation</h2>
                <p class="mt-1 text-sm text-gray-500">API access is {{ config('admissions.api_enabled') ? 'enabled' : 'disabled by environment config' }}. Keys are stored only as SHA-256 hashes.</p>
                <form method="POST" action="{{ route('admin.admissions.api-keys.store') }}" class="mt-4 grid gap-3">@csrf<input name="name" placeholder="Key name" required class="rounded-lg border-gray-300"><select name="channel_id" class="rounded-lg border-gray-300"><option value="">No channel</option>@foreach($channels as $channel)<option value="{{ $channel->id }}">{{ $channel->name }}</option>@endforeach</select><input name="allowed_domain" placeholder="Allowed domain (recommended)" class="rounded-lg border-gray-300"><button class="rounded-lg border px-4 py-2 text-sm font-semibold">Create API key</button></form>
                <div class="mt-4 space-y-2">@foreach($apiKeys as $key)<div class="flex items-center justify-between gap-3 rounded-lg bg-gray-50 p-3 text-sm"><span><strong>{{ $key->name }}</strong> · {{ $key->is_active ? 'Active' : 'Revoked' }} · Last used {{ $key->last_used_at?->diffForHumans() ?? 'never' }}</span>@if($key->is_active)<form method="POST" action="{{ route('admin.admissions.api-keys.revoke', $key) }}">@csrf<button class="font-semibold text-rose-700">Revoke</button></form>@endif</div>@endforeach</div>
            </section>
        </div>
    </div>
</x-app-layout>
