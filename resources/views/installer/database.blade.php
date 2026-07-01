@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Connect Database</h2>
            <p class="mt-2 text-sm text-text-secondary">This page checks whether the portal can reach the school database using the saved hosting credentials. It does not show database usernames, passwords, or host secrets.</p>
        </div>

        <div class="responsive-form-grid gap-3">
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">Connection</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ $status['connection'] }}</p>
            </div>
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">Database</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ $status['database_name'] }}</p>
            </div>
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">Connected</p>
                <p class="mt-1 text-sm font-semibold {{ $status['connected'] ? 'text-green-700' : 'text-red-700' }}">{{ $status['connected'] ? 'Yes' : 'No' }}</p>
            </div>
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4">
                <p class="text-xs uppercase tracking-normal text-text-muted">Database table log</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ $status['migrations_table_exists'] ? 'Found' : 'Not found' }}</p>
            </div>
        </div>

        @if ($status['error'])
            <div class="space-y-2 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p>{{ $status['error'] }}</p>
                <p>Create a MySQL database and user in your hosting control panel, assign the user to the database with all privileges, then return here and click Test Again.</p>
            </div>
        @endif

        @include('installer.partials.checks', ['checks' => [$migrationCheck]])

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">Database requirement</p>
            <p class="mt-1">Create the database in your hosting panel before this step. You will need the database name, username, password, host, and port from your hosting provider.</p>
            <p class="mt-2">If this check fails, confirm the saved database credentials, assign the database user to the database with all privileges, then return here and click Test Again.</p>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.permissions') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <div class="flex gap-3">
                <a href="{{ route('installer.database') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Test Again</a>
                <a href="{{ route('installer.environment') }}" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Continue</a>
            </div>
        </div>
    </div>
@endsection
