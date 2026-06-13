@extends('installer.layout')

@section('content')
    <form method="POST" action="{{ route('installer.complete') }}" class="space-y-5">
        @csrf
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Final Review</h2>
            <p class="mt-2 text-sm text-text-secondary">Finalize only after confirming hosting, the <span class="font-mono">/public</span> document root, database credentials, owner account, school profile, and email plan.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">Owner</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ $admin['name'] }}</p>
                <p class="text-sm text-text-secondary">{{ $admin['email'] }}</p>
            </div>
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">School</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ $school['name'] }}</p>
                <p class="text-sm text-text-secondary">{{ $school['email'] ?? 'No email set' }}</p>
            </div>
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">SMTP</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ strtoupper($smtp['mailer'] ?? 'LOG') }}</p>
                <p class="text-sm text-text-secondary">Password: {{ ($smtp['password_provided'] ?? false) ? 'Provided, not displayed' : 'Not provided' }}</p>
            </div>
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">Database</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ $database['connected'] ? 'Connected' : 'Not connected' }}</p>
                <p class="text-sm text-text-secondary">{{ $database['pending_migrations_count'] ?? 'Unknown' }} pending migrations</p>
            </div>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            Finalization writes the installation lock and prevents reinstall. It does not run seeders, migrations, license activation, updates, backups, or demo automation.
        </div>

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
            <p class="text-sm font-semibold text-text-primary">Support-safe diagnostics</p>
            <p class="mt-1 text-xs text-text-secondary">Share these statuses with Sanfaani support if needed. App keys, database credentials, mail passwords, license keys, and private server paths are not shown.</p>
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
            If any item still depends on your host, pause here and ask your hosting provider or Sanfaani managed setup team to verify it before finalizing.
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.smtp') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <button type="submit" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Finalize installation</button>
        </div>
    </form>
@endsection
