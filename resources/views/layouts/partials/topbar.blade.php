@php
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $workspaceLabel = auth()->check() && auth()->user()->hasRole('super_admin') ? 'Platform workspace' : 'School workspace';
@endphp

<header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-3">
            <button type="button" @click="sidebarOpen = true" class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 lg:hidden" aria-label="Open navigation">
                <span class="block h-0.5 w-5 rounded bg-current"></span>
                <span class="absolute mt-3 block h-0.5 w-5 rounded bg-current"></span>
                <span class="absolute -mt-3 block h-0.5 w-5 rounded bg-current"></span>
            </button>
            <div class="min-w-0">
                <p class="truncate text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $workspaceLabel }}</p>
                <p class="truncate text-sm font-semibold text-slate-950">{{ $brandName }}</p>
            </div>
        </div>

        @auth
            <div class="flex min-w-0 items-center gap-3">
                <a href="{{ route('profile.edit') }}" class="hidden max-w-56 truncate rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:inline-flex">
                    {{ auth()->user()->name }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-slate-950 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        Log Out
                    </button>
                </form>
            </div>
        @endauth
    </div>
</header>
