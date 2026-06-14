@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Prepare database</h2>
            <p class="mt-2 text-sm text-text-secondary">This step checks whether the database tables needed by the portal are ready. It does not delete data, add sample records, or run database changes from the browser.</p>
        </div>

        @include('installer.partials.checks', ['checks' => [$check]])

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">If tables are pending</p>
            <p class="mt-1">Confirm the database name first, then ask the installer or hosting provider to run <span class="font-mono">php artisan migrate --force</span> from terminal, cPanel, or an approved deployment task.</p>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.app-key') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <a href="{{ route('installer.admin') }}" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Continue</a>
        </div>
    </div>
@endsection
