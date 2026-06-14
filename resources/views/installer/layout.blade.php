<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sanfaani Schools') }} Installer</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-bg-secondary text-text-primary antialiased">
    @php
        $stepList = collect($steps ?? []);
        $currentIndex = max(0, $stepList->search($step ?? 'welcome'));
        $nextStep = $stepList->get($currentIndex + 1);
        $stepRoutes = [
            'welcome' => 'installer.welcome',
            'requirements' => 'installer.requirements',
            'permissions' => 'installer.permissions',
            'database' => 'installer.database',
            'environment' => 'installer.environment',
            'app-key' => 'installer.app-key',
            'migrations' => 'installer.migrations',
            'admin' => 'installer.admin',
            'school' => 'installer.school',
            'smtp' => 'installer.smtp',
            'review' => 'installer.review',
        ];
        $stepLabels = [
            'welcome' => 'Welcome',
            'requirements' => 'Requirements',
            'permissions' => 'Folder access',
            'database' => 'Database connection',
            'environment' => 'Portal configuration',
            'app-key' => 'Security key',
            'migrations' => 'Prepare database',
            'admin' => 'Owner account',
            'school' => 'School profile',
            'smtp' => 'Email settings',
            'review' => 'Review',
            'complete' => 'Complete',
        ];
    @endphp

    <main class="mx-auto flex min-h-screen w-full max-w-5xl flex-col px-4 py-6 sm:px-6 lg:px-8">
        <header class="mb-6 flex flex-col gap-3 border-b border-border-subtle pb-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-text-muted">School setup</p>
                <h1 class="text-2xl font-semibold text-text-primary">{{ config('app.name', 'Sanfaani Schools') }}</h1>
                <p class="mt-1 text-sm text-text-secondary">A guided setup for this private school portal.</p>
            </div>
            <div class="rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-xs text-text-secondary">
                Step {{ $currentIndex + 1 }} of {{ max(1, $stepList->count()) }}
            </div>
        </header>

        <div class="mb-5 grid gap-3 lg:grid-cols-2">
            <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Complete these steps before staff, parents, or applicants use the portal. If Sanfaani is handling setup for you, share this page with your installer.
            </div>
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                Hosting safety: your domain document root must point to <span class="font-mono">/public</span>, or your host must expose only the safe public folder.
            </div>
        </div>

        @if (session('error'))
            <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid gap-5 lg:grid-cols-[220px_1fr]">
            <aside class="rounded-md border border-border-subtle bg-bg-primary p-3">
                <nav class="space-y-1" aria-label="Installer steps">
                    @foreach ($stepList as $index => $installerStep)
                        @php
                            $route = $stepRoutes[$installerStep] ?? null;
                            $isCurrent = $installerStep === ($step ?? 'welcome');
                        @endphp
                        @if ($route)
                            <a href="{{ route($route) }}" class="flex items-center justify-between rounded-md px-3 py-2 text-sm {{ $isCurrent ? 'bg-brand-primary text-white' : 'text-text-secondary hover:bg-bg-secondary hover:text-text-primary' }}">
                                <span>{{ $stepLabels[$installerStep] ?? str($installerStep)->replace('-', ' ')->title() }}</span>
                                <span class="text-xs">{{ $index + 1 }}</span>
                            </a>
                        @else
                            <div class="flex items-center justify-between rounded-md px-3 py-2 text-sm {{ $isCurrent ? 'bg-brand-primary text-white' : 'text-text-secondary' }}">
                                <span>{{ $stepLabels[$installerStep] ?? str($installerStep)->replace('-', ' ')->title() }}</span>
                                <span class="text-xs">{{ $index + 1 }}</span>
                            </div>
                        @endif
                    @endforeach
                </nav>
            </aside>

            <section class="rounded-md border border-border-subtle bg-bg-primary p-5 shadow-sm">
                @yield('content')
            </section>
        </div>

        <footer class="mt-6 text-xs text-text-muted">
            Installation lock: <span class="font-mono">{{ $lockLabel ?? 'storage/app/installed.lock' }}</span>
        </footer>
    </main>
</body>
</html>
