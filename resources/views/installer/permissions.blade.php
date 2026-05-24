@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Folder Permission Check</h2>
            <p class="mt-2 text-sm text-text-secondary">The application needs write access to runtime folders. Public storage links can usually be created from hosting tools if terminal access is unavailable.</p>
        </div>

        @include('installer.partials.checks', ['checks' => $checks])

        <div class="flex justify-between">
            <a href="{{ route('installer.requirements') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <a href="{{ route('installer.database') }}" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Continue</a>
        </div>
    </div>
@endsection
