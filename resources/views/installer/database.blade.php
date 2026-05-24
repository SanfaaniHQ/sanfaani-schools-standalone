@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Database Connection Check</h2>
            <p class="mt-2 text-sm text-text-secondary">This page checks connectivity and migration readiness without showing database usernames, passwords, or host secrets.</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
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
                <p class="text-xs uppercase tracking-normal text-text-muted">Migrations table</p>
                <p class="mt-1 text-sm font-semibold text-text-primary">{{ $status['migrations_table_exists'] ? 'Found' : 'Not found' }}</p>
            </div>
        </div>

        @if ($status['error'])
            <div class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $status['error'] }}</div>
        @endif

        @include('installer.partials.checks', ['checks' => [$migrationCheck]])

        <div class="flex justify-between">
            <a href="{{ route('installer.permissions') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <a href="{{ route('installer.environment') }}" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Continue</a>
        </div>
    </div>
@endsection
