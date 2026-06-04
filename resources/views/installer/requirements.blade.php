@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Requirements Check</h2>
            <p class="mt-2 text-sm text-text-secondary">This step checks whether the server has the PHP version and extensions Sanfaani needs. Required checks must pass before production use; warnings are optional improvements that can be enabled later.</p>
        </div>

        @include('installer.partials.checks', ['checks' => $checks])

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">If a required item fails</p>
            <p class="mt-1">Ask your hosting provider to enable the missing PHP extension or switch the domain to the supported PHP version before continuing.</p>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.welcome') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <a href="{{ route('installer.permissions') }}" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Continue</a>
        </div>
    </div>
@endsection
