@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">App Key Status</h2>
            <p class="mt-2 text-sm text-text-secondary">The application key must exist before accounts and encrypted settings are used.</p>
        </div>

        @include('installer.partials.checks', ['checks' => [$check]])

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">Shared-hosting note</p>
            <p class="mt-1">If `php artisan key:generate` cannot be run on the server, generate the key during packaging or in a managed setup session and paste it into `.env`.</p>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.environment') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <a href="{{ route('installer.migrations') }}" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Continue</a>
        </div>
    </div>
@endsection
