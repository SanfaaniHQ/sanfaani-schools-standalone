@extends('installer.layout')

@section('content')
    <div class="space-y-5">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Welcome to school setup</h2>
            <p class="mt-2 text-sm text-text-secondary">This guided setup prepares one school portal on its own domain. It checks the server, confirms the school details, creates the owner account, and locks the installer when setup is complete.</p>
        </div>

        <div class="grid gap-3 md:grid-cols-2">
            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
                <p class="font-semibold text-text-primary">What this setup does</p>
                <p class="mt-1">It confirms hosting readiness, records the school profile, creates the first owner login, reviews email settings, and protects the portal from being installed twice.</p>
            </div>

            <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
                <p class="font-semibold text-text-primary">What you need before starting</p>
                <p class="mt-1">Have the database details, school contact details, owner email, and email account information ready. You can pause and return to any earlier step before final review.</p>
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
            <p class="mt-1">If your host does not provide shell access, complete the checks here and ask your host or installer to run any required database step from cPanel or an approved hosting tool. Hosting panels still control files, database creation, document root, and mail settings.</p>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('installer.requirements') }}" class="inline-flex items-center rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Start school setup</a>
        </div>
    </div>
@endsection
