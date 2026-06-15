@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Security Key</h2>
            <p class="mt-2 text-sm text-text-secondary">The security key protects logins, sessions, and private settings. It must be ready before the school admin account is created.</p>
        </div>

        @include('installer.partials.checks', ['checks' => [$check]])

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">If the key is missing</p>
            <p class="mt-1">Use your hosting setup tool to generate and save the application security key, then refresh this page before continuing.</p>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.environment') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <a href="{{ route('installer.migrations') }}" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Continue</a>
        </div>
    </div>
@endsection
