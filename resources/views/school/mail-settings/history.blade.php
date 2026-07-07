<x-app-layout>
    @php
        $deliveryStatusLabel = fn (?string $status) => match ($status) {
            'accepted_by_smtp', 'fallback_accepted' => 'Accepted by SMTP',
            'delivery_unconfirmed' => 'Delivery unconfirmed',
            'deferred' => 'Deferred',
            'rejected' => 'Rejected',
            'fallback_non_delivery' => 'Non-delivery fallback',
            default => filled($status) ? str($status)->replace('_', ' ')->title()->toString() : 'Delivery unconfirmed',
        };
        $deliveryStatusTone = fn (?string $status) => in_array($status, ['accepted_by_smtp', 'fallback_accepted'], true)
            ? 'bg-emerald-100 text-emerald-800'
            : (in_array($status, ['deferred', 'delivery_unconfirmed'], true)
                ? 'bg-amber-100 text-amber-800'
                : 'bg-red-100 text-red-800');
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-4 rounded-3xl bg-slate-950 p-6 text-white sm:flex-row sm:items-end sm:justify-between">
            <div><p class="text-xs font-semibold uppercase tracking-wide text-indigo-200">Email Delivery</p><h1 class="mt-1 text-3xl font-bold">Delivery history</h1><p class="mt-2 text-sm text-slate-300">Safe SMTP outcomes for {{ $school->name }}. Message bodies and credentials are never stored.</p></div>
            <a href="{{ route('school.mail-settings.edit') }}" class="inline-flex min-h-11 items-center rounded-xl bg-white px-4 text-sm font-bold text-slate-950">Back to providers</a>
        </header>

        <form method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
            <div><label class="text-xs font-bold uppercase text-slate-500">Provider</label><select name="provider" class="mt-1 block w-full rounded-xl border-slate-300 text-sm"><option value="">All providers</option>@foreach($providers as $provider)<option value="{{ $provider->id }}" @selected(($filters['provider'] ?? null) == $provider->id)>{{ $provider->name }}</option>@endforeach</select></div>
            <div><label class="text-xs font-bold uppercase text-slate-500">Status</label><input name="status" value="{{ $filters['status'] ?? '' }}" placeholder="accepted_by_smtp" class="mt-1 block w-full rounded-xl border-slate-300 text-sm"></div>
            <div><label class="text-xs font-bold uppercase text-slate-500">Recipient</label><input name="recipient" value="{{ $filters['recipient'] ?? '' }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm"></div>
            <div><label class="text-xs font-bold uppercase text-slate-500">Test / transactional</label><select name="message_kind" class="mt-1 block w-full rounded-xl border-slate-300 text-sm"><option value="">All</option><option value="test" @selected(($filters['message_kind'] ?? null) === 'test')>Test</option><option value="transactional" @selected(($filters['message_kind'] ?? null) === 'transactional')>Transactional</option></select></div>
            <div><label class="text-xs font-bold uppercase text-slate-500">Primary / secondary</label><select name="provider_position" class="mt-1 block w-full rounded-xl border-slate-300 text-sm"><option value="">All</option>@foreach(['primary', 'secondary', 'platform'] as $position)<option value="{{ $position }}" @selected(($filters['provider_position'] ?? null) === $position)>{{ ucfirst($position) }}</option>@endforeach</select></div>
            <div><label class="text-xs font-bold uppercase text-slate-500">Error category</label><input name="error_category" value="{{ $filters['error_category'] ?? '' }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm"></div>
            <div><label class="text-xs font-bold uppercase text-slate-500">From date</label><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm"></div>
            <div><label class="text-xs font-bold uppercase text-slate-500">To date</label><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm"></div>
            <div class="flex gap-2 sm:col-span-2 lg:col-span-4"><button class="min-h-11 rounded-xl bg-indigo-700 px-5 text-sm font-bold text-white">Filter history</button><a href="{{ route('school.mail-settings.history') }}" class="inline-flex min-h-11 items-center rounded-xl border border-slate-300 px-5 text-sm font-bold text-slate-700">Clear</a></div>
        </form>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="hidden overflow-x-auto lg:block"><table class="min-w-full divide-y divide-slate-200 text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Date / time</th><th class="px-4 py-3">School</th><th class="px-4 py-3">Provider</th><th class="px-4 py-3">Position</th><th class="px-4 py-3">Recipient / sender</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Error</th><th class="px-4 py-3">Message ID</th><th class="px-4 py-3">Initiated by</th><th class="px-4 py-3">Kind</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($attempts as $attempt)<tr class="align-top"><td class="whitespace-nowrap px-4 py-3">{{ $attempt->created_at?->format('M j, Y H:i:s') }}</td><td class="px-4 py-3">{{ $school->name }}</td><td class="px-4 py-3"><span class="font-semibold">{{ $attempt->provider_name ?? strtoupper($attempt->transport) }}</span><span class="block text-xs text-slate-500">{{ $attempt->provider_type ?? $attempt->transport }}</span></td><td class="px-4 py-3">{{ ucfirst($attempt->provider_position ?? ($attempt->fallback_used ? 'platform' : '—')) }}</td><td class="max-w-64 break-all px-4 py-3">{{ $attempt->recipient ?? '—' }}<span class="block text-xs text-slate-500">from {{ $attempt->sender ?? '—' }}</span></td><td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold {{ $deliveryStatusTone($attempt->status) }}">{{ $deliveryStatusLabel($attempt->status) }}</span></td><td class="max-w-64 px-4 py-3 text-red-700">{{ $attempt->safe_error_category ?? '—' }}</td><td class="max-w-48 break-all px-4 py-3 text-xs">{{ $attempt->provider_message_id ?? '—' }}</td><td class="px-4 py-3">{{ $attempt->initiatingUser?->name ?? 'System' }}</td><td class="px-4 py-3">{{ ucfirst($attempt->message_kind ?? 'transactional') }}</td></tr>@empty<tr><td colspan="10" class="px-6 py-12 text-center text-slate-500">No attempts match these filters.</td></tr>@endforelse</tbody></table></div>
            <div class="divide-y divide-slate-100 lg:hidden">@forelse($attempts as $attempt)<article class="space-y-3 p-4"><div class="flex items-start justify-between gap-3"><div><h2 class="font-bold text-slate-950">{{ $attempt->provider_name ?? strtoupper($attempt->transport) }}</h2><p class="text-xs text-slate-500">{{ $attempt->created_at?->format('M j, Y H:i:s') }}</p></div><span class="rounded-full px-2 py-1 text-xs font-semibold {{ $deliveryStatusTone($attempt->status) }}">{{ $deliveryStatusLabel($attempt->status) }}</span></div><dl class="grid grid-cols-2 gap-2 text-sm"><div><dt class="text-xs text-slate-500">Recipient</dt><dd class="break-all">{{ $attempt->recipient ?? '—' }}</dd></div><div><dt class="text-xs text-slate-500">Position</dt><dd>{{ ucfirst($attempt->provider_position ?? '—') }}</dd></div><div><dt class="text-xs text-slate-500">Kind</dt><dd>{{ ucfirst($attempt->message_kind ?? 'transactional') }}</dd></div><div><dt class="text-xs text-slate-500">Error</dt><dd class="text-red-700">{{ $attempt->safe_error_category ?? '—' }}</dd></div></dl></article>@empty<p class="p-10 text-center text-sm text-slate-500">No attempts match these filters.</p>@endforelse</div>
        </div>
        {{ $attempts->links() }}
    </div>
</x-app-layout>
