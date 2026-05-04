<x-guest-layout>
    <main class="min-h-screen bg-slate-950 px-4 py-10 text-white sm:px-6 lg:px-8">
        <div class="mx-auto flex min-h-[calc(100vh-5rem)] max-w-md flex-col justify-center">
            <div class="mb-8 text-center">
                <a href="{{ route('landing.home') }}" class="inline-flex items-center justify-center gap-3">
                    <x-platform-logo class="h-12 w-auto object-contain" mark-class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-600 text-sm font-semibold text-white" name-class="text-lg font-semibold text-white" />
                </a>
                <h1 class="mt-6 text-2xl font-semibold">Sanfaani Schools System Administration</h1>
                <p class="mt-2 text-sm text-slate-300">Secure access for Super Admin users only.</p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white p-6 text-gray-900 shadow-xl">
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('admin.login.store') }}" data-loading-text="Signing in..." class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" class="mt-1 block w-full rounded-xl" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Password" />
                        <x-text-input id="password" class="mt-1 block w-full rounded-xl" type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-slate-900 shadow-sm focus:ring-slate-900" name="remember">
                            <span class="ms-2 text-sm text-gray-600">Remember me</span>
                        </label>

                        <a href="{{ route('landing.home') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">Back to home</a>
                    </div>

                    <button type="submit" data-loading-text="Signing in..." class="inline-flex w-full items-center justify-center rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                        Log in to Admin
                    </button>
                </form>
            </div>
        </div>
    </main>
</x-guest-layout>
