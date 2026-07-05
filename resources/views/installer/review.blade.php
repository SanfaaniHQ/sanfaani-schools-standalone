@extends('installer.layout')

@section('content')
    <form method="POST" action="{{ route('installer.complete') }}" data-loading-text="Finalizing setup..." class="space-y-5">
        @csrf
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Final review</h2>
            <p class="mt-2 text-sm text-text-secondary">Finalize only after confirming hosting, the <span class="font-mono">/public</span> document root, database credentials, owner account, school profile, and email plan.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">Installation Administrator</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ $admin['name'] }}</p>
                <p class="text-sm text-text-secondary">{{ $admin['email'] }}</p>
            </div>
            @if (isset($admin['school_admin']))
                <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                    <p class="text-xs uppercase tracking-normal text-text-muted">School Admin</p>
                    <p class="mt-1 text-sm font-semibold text-text-primary">{{ data_get($admin, 'school_admin.name') }}</p>
                    <p class="text-sm text-text-secondary">{{ data_get($admin, 'school_admin.email') }}</p>
                </div>
            @endif
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">School</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ $school['name'] }}</p>
                <p class="text-sm text-text-secondary">{{ $school['email'] ?? 'No email set' }}</p>
            </div>
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">Email settings</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ match ($smtp['mailer'] ?? 'log') {
                    'smtp' => 'Mail provider',
                    'array' => 'Testing only',
                    default => 'Email log only',
                } }}</p>
                <p class="text-sm text-text-secondary">Password: {{ ($smtp['password_provided'] ?? false) ? 'Provided, not displayed' : 'Not provided' }}</p>
            </div>
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">Database</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ $database['connected'] ? 'Connected' : 'Not connected' }}</p>
                <p class="text-sm text-text-secondary">{{ $database['pending_migrations_count'] ?? 'Unknown' }} pending database updates</p>
            </div>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            Finalizing creates the school, creates the school admin login, saves email delivery settings where available, checks public storage, clears application caches, writes the installation lock, and prevents reinstall from the browser. It does not send emails or start backups.
        </div>

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
            <p class="text-sm font-semibold text-text-primary">Support-safe diagnostics</p>
            <p class="mt-1 text-xs text-text-secondary">Share these statuses with Sanfaani support if needed. Security keys, database credentials, mail passwords, and private server paths are not shown.</p>
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

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            If any item still depends on your host, pause here and ask your hosting provider or installer to verify it before finalizing.
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.smtp') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <button type="submit" data-loading-text="Finalizing setup..." class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Finalize setup</button>
        </div>
    </form>
@endsection
