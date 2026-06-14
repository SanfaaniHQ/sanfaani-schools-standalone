@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Portal configuration</h2>
            <p class="mt-2 text-sm text-text-secondary">This step confirms the portal settings already saved on the server. The installer does not edit <span class="font-mono">.env</span>; update hosting values through your file manager or hosting control panel if a check needs attention.</p>
        </div>

        @include('installer.partials.checks', ['checks' => $checks])

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">If a check needs attention</p>
            <p class="mt-1">Update the matching setting on the server, refresh this page, and continue once the required checks pass. Optional warnings can be reviewed after login if the portal is otherwise ready.</p>
            <p class="mt-2">If terminal access exists, the installer or hosting provider may run safe setup commands. On shared hosting, use cPanel tools instead of forcing shell execution.</p>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.database') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <a href="{{ route('installer.app-key') }}" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Continue</a>
        </div>
    </div>
@endsection
