@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Welcome</h2>
            <p class="mt-2 text-sm text-text-secondary">This wizard prepares a single-school Sanfaani Schools installation without running destructive commands or changing licensing, updates, backups, marketplace packaging, or demo automation.</p>
        </div>

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">Shared-hosting friendly</p>
            <p class="mt-1">If your host does not provide shell access, complete the checks here and run database migrations from cPanel, a hosting task runner, or a managed setup session.</p>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('installer.requirements') }}" class="inline-flex items-center rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Start checks</a>
        </div>
    </div>
@endsection
