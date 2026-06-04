@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Welcome to standalone setup</h2>
            <p class="mt-2 text-sm text-text-secondary">This wizard is for a technical buyer or hosting provider preparing one school on its own hosting. It explains checks in plain English and avoids destructive commands.</p>
        </div>

        <div class="grid gap-3 md:grid-cols-2">
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
                <p class="font-semibold text-text-primary">Using SaaS?</p>
                <p class="mt-1">School owners on SaaS do not upload files, use Git, run Composer, run npm, or open <span class="font-mono">/install</span>. They sign in from the browser, create or receive a school workspace, and follow onboarding.</p>
            </div>

            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
                <p class="font-semibold text-text-primary">Want hands-off setup?</p>
                <p class="mt-1">Sanfaani can install this for you as a managed setup, configure the school workspace, and hand over login details after verification.</p>
            </div>
        </div>

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">What to ask your hosting provider</p>
            <ul class="mt-2 list-disc space-y-1 ps-5">
                <li>Can the domain document root point to <span class="font-mono">/public</span>?</li>
                <li>Which PHP version and required extensions are enabled?</li>
                <li>What are the database credentials: database name, username, password, host, and port?</li>
                <li>Can <span class="font-mono">storage</span> and <span class="font-mono">bootstrap/cache</span> be made writable?</li>
                <li>Is terminal, task-runner, or migration support available?</li>
            </ul>
        </div>

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            <p class="font-semibold text-text-primary">Shared-hosting friendly</p>
            <p class="mt-1">If your host does not provide shell access, complete the checks here and run database migrations from cPanel, a hosting task runner, or a managed setup session. The installer guides you, but hosting panels still control files, database creation, document root, and mail settings.</p>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('installer.requirements') }}" class="inline-flex items-center rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Start checks</a>
        </div>
    </div>
@endsection
