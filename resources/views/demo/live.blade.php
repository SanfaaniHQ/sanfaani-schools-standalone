@php
    $platformName = config('sanfaani.platform_name', config('app.name', 'Sanfaani Schools'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Explore Sanfaani Schools Live Demo - {{ $platformName }}</title>
        <meta name="description" content="Use safe public demo credentials to preview Sanfaani Schools before buying the standalone package or installation service.">
        @if (! empty($platformFaviconUrl))
            <link rel="icon" href="{{ $platformFaviconUrl }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white font-sans text-gray-950 antialiased">
        @include('public.landing.partials.nav')

        <main id="main-content">
            <section class="marketing-soft-gradient py-14 sm:py-16">
                <x-ui.container>
                    <div class="mx-auto max-w-4xl text-center">
                        <x-marketing.badge icon="shield">Public buyer preview</x-marketing.badge>
                        <h1 class="mt-4 text-4xl font-semibold leading-tight text-gray-950 sm:text-5xl">
                            Explore Sanfaani Schools Live Demo
                        </h1>
                        <p class="mt-5 text-lg leading-8 text-gray-600">
                            Preview the school management workflows with safe sample accounts before buying the cPanel-ready standalone package or requesting done-for-you installation.
                        </p>
                    </div>

                    <div class="mt-10 grid gap-4 md:grid-cols-3">
                        <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-5 text-sm leading-6 text-emerald-950">
                            <p class="font-semibold">Demo data resets regularly</p>
                            <p class="mt-2">This public preview is refreshed about every {{ $resetHours }} hours.</p>
                        </div>
                        <div class="rounded-lg border border-amber-100 bg-amber-50 p-5 text-sm leading-6 text-amber-950">
                            <p class="font-semibold">Destructive actions are disabled</p>
                            <p class="mt-2">Password, email, bulk communication, payment, update, backup, and destructive admin actions are blocked in safe mode.</p>
                        </div>
                        <div class="rounded-lg border border-sky-100 bg-sky-50 p-5 text-sm leading-6 text-sky-950">
                            <p class="font-semibold">Fake sample data only</p>
                            <p class="mt-2">The preview school and users are public demo records, not customer data.</p>
                        </div>
                    </div>
                </x-ui.container>
            </section>

            <section class="bg-white py-14">
                <x-ui.container>
                    <div class="grid gap-5 lg:grid-cols-3">
                        @foreach ($accounts as $account)
                            <article class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold uppercase tracking-normal text-emerald-700">Demo account</p>
                                        <h2 class="mt-2 text-2xl font-semibold text-gray-950">{{ $account['label'] }}</h2>
                                    </div>
                                    <x-marketing.icon name="users" class="h-6 w-6 shrink-0 text-emerald-700" />
                                </div>

                                <p class="mt-4 min-h-12 text-sm leading-6 text-gray-600">{{ $account['description'] }}</p>

                                <dl class="mt-5 space-y-3 text-sm">
                                    <div>
                                        <dt class="font-medium text-gray-500">Role</dt>
                                        <dd class="mt-1 font-semibold text-gray-950">{{ $account['label'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-500">Email</dt>
                                        <dd class="mt-1 break-all rounded-md bg-gray-50 px-3 py-2 font-mono text-gray-950">{{ $account['email'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-500">Password</dt>
                                        <dd class="mt-1 rounded-md bg-gray-50 px-3 py-2 font-mono text-gray-950">{{ $account['password'] }}</dd>
                                    </div>
                                </dl>

                                <div class="mt-6 grid gap-3">
                                    @if ($autoLoginEnabled)
                                        <form method="POST" action="{{ route('demo.live.login', $account['role']) }}">
                                            @csrf
                                            <button type="submit" class="ui-button-primary w-full py-3">
                                                Login as {{ $account['label'] }}
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('login') }}" class="ui-button-primary w-full py-3">
                                            Open login page
                                        </a>
                                    @endif
                                    <button type="button" class="ui-button-secondary w-full py-3" data-copy-credentials="{{ $account['email'] }} / {{ $account['password'] }}">
                                        Copy credentials
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-10 rounded-lg border border-gray-200 bg-gray-50 p-6">
                        <div class="grid gap-4 md:grid-cols-3">
                            <a href="{{ route('landing.pricing') }}" class="ui-button-primary justify-center py-3">Buy standalone package</a>
                            <a href="{{ route('landing.contact', ['interest' => 'done-for-you-installation']) }}" class="ui-button-secondary justify-center py-3">Done-for-you installation</a>
                            <a href="{{ route('landing.contact') }}" class="ui-button-secondary justify-center py-3">Contact Sanfaani</a>
                        </div>
                        <p class="mt-5 text-center text-sm leading-6 text-gray-600">
                            Standalone buyers receive the package and install support. SaaS buyers use the hosted service and do not receive source code.
                        </p>
                    </div>
                </x-ui.container>
            </section>
        </main>

        @include('public.landing.partials.footer')

        <script>
            document.querySelectorAll('[data-copy-credentials]').forEach((button) => {
                button.addEventListener('click', async () => {
                    await navigator.clipboard.writeText(button.dataset.copyCredentials);
                    button.textContent = 'Copied';
                    setTimeout(() => { button.textContent = 'Copy credentials'; }, 1600);
                });
            });
        </script>
    </body>
</html>
