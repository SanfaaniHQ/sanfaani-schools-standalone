@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Installation Locked</h2>
            <p class="mt-2 text-sm text-text-secondary">The single-school installation has been finalized. Installer routes are now blocked by the installation lock, and the owner can sign in from the browser.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">School</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ $result['school']->name }}</p>
            </div>
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">Owner</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ $result['admin']->email }}</p>
            </div>
        </div>

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">Lock metadata</p>
            <p class="mt-1">Deployment mode: {{ $metadata['deployment_mode'] ?? 'unknown' }}</p>
            <p>License mode: {{ $metadata['license_mode'] ?? 'unknown' }}</p>
            <p>Installed at: {{ $metadata['installed_at'] ?? 'unknown' }}</p>
        </div>

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">Next safe checks</p>
            <p class="mt-1">Confirm the login URL, owner email, school profile, current session and term, SMTP delivery, backups, and license status before inviting staff or parents.</p>
        </div>

        <a href="{{ route('admin.login') }}" class="inline-flex rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Go to admin login</a>
    </div>
@endsection
