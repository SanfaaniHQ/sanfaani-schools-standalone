@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">School portal is ready</h2>
            <p class="mt-2 text-sm text-text-secondary">Your school portal is ready. The installer is now locked, and the school admin can sign in from the browser.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">School</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ $result['school']->name }}</p>
            </div>
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">School Admin</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ $result['admin']->email }}</p>
            </div>
        </div>

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">Setup record</p>
            <p class="mt-1">Portal mode: {{ $metadata['deployment_mode'] ?? 'unknown' }}</p>
            <p>License mode: {{ $metadata['license_mode'] ?? 'unknown' }}</p>
            <p>Installed at: {{ $metadata['installed_at'] ?? 'unknown' }}</p>
        </div>

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">Next safe checks</p>
            <p class="mt-1">Keep database and email credentials safe. After login, confirm the school profile, current session and term, email delivery, backups, and license status before inviting staff or parents.</p>
        </div>

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
            <p class="text-sm font-semibold text-text-primary">Post-install diagnostic summary</p>
            <p class="mt-1 text-xs text-text-secondary">Statuses only. Secrets, raw environment values, and private server paths stay hidden.</p>
            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($diagnostics as $item)
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <dt class="text-xs uppercase tracking-normal text-text-muted">{{ $item['label'] }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $item['value'] }}</dd>
                        <span class="mt-2 inline-flex rounded-md px-2 py-1 text-xs font-semibold {{ $item['status'] === 'pass' ? 'bg-green-100 text-green-700' : ($item['status'] === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                            {{ str($item['status'])->upper() }}
                        </span>
                    </div>
                @endforeach
            </dl>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.login') }}" class="inline-flex rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Go to Login</a>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Open Admin Dashboard</a>
        </div>
    </div>
@endsection
