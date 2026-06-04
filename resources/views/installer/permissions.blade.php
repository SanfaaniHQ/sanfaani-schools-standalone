@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Folder Permission Check</h2>
            <p class="mt-2 text-sm text-text-secondary">The application needs write access to runtime folders so it can save cache files, uploaded files, and safe installation state. Public storage links can usually be created from hosting tools if terminal access is unavailable.</p>
        </div>

        @include('installer.partials.checks', ['checks' => $checks])

        <div class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            Keep private folders out of public access. The domain document root should point to <span class="font-mono">/public</span>, not the project root that contains <span class="font-mono">.env</span>, <span class="font-mono">storage</span>, logs, backups, or source files.
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.requirements') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <a href="{{ route('installer.database') }}" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Continue</a>
        </div>
    </div>
@endsection
