@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Prepare Database</h2>
            <p class="mt-2 text-sm text-text-secondary">This step checks whether the database tables needed by the portal are ready. Setup will only continue when the database is reachable.</p>
        </div>

        @include('installer.partials.checks', ['checks' => [$check]])

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">If tables are pending</p>
            <p class="mt-1">Confirm the database name first, then use your hosting migration tool or ask your hosting provider to prepare the portal tables.</p>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.app-key') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <a href="{{ route('installer.admin') }}" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Continue</a>
        </div>
    </div>
@endsection
