@extends('installer.layout')

@section('content')
    <form method="POST" action="{{ route('installer.complete') }}" class="space-y-5">
        @csrf
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Final Review</h2>
            <p class="mt-2 text-sm text-text-secondary">Finalize only after confirming the environment, database, owner account, and school profile.</p>
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

        <div class="flex justify-between">
            <a href="{{ route('installer.smtp') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <button type="submit" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Finalize installation</button>
        </div>
    </form>
@endsection
