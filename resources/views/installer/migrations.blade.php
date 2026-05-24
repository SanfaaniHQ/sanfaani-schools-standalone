@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Migration Readiness</h2>
            <p class="mt-2 text-sm text-text-secondary">The installer checks migration state but does not run destructive migrations or seeders.</p>
        </div>

        @include('installer.partials.checks', ['checks' => [$check]])

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">Manual migration guidance</p>
            <p class="mt-1">Run `php artisan migrate --force` only after confirming the database target. On cPanel, use terminal access, a deployment task, or managed setup support.</p>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.app-key') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <a href="{{ route('installer.admin') }}" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Continue</a>
        </div>
    </div>
@endsection
