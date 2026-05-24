@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Environment Setup Guidance</h2>
            <p class="mt-2 text-sm text-text-secondary">Review the hosting environment. This installer does not write to `.env`; update values manually through your file manager, deployment panel, or managed setup process.</p>
        </div>

        @include('installer.partials.checks', ['checks' => $checks])

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">Manual command fallback</p>
            <p class="mt-1">If terminal access exists, safe commands may be run by the operator. On shared hosting, use cPanel tools or request managed setup instead of forcing shell execution.</p>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.database') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <a href="{{ route('installer.app-key') }}" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Continue</a>
        </div>
    </div>
@endsection
