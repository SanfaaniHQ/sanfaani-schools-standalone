<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            :title="__('ui.switch_role')"
            :description="__('ui.switch_role_intro')"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
            @endif

            <x-ui.table-card :title="__('ui.available_workspaces')" :description="__('ui.choose_workspace_help')">
                @if (empty($contexts))
                    <div class="p-5">
                        <x-ui.empty-state
                            :title="__('ui.no_role_contexts')"
                            :body="__('ui.no_role_contexts_help')"
                        />
                    </div>
                @else
                    @php
                        $activeContext = collect($contexts)->first(function (array $context) use ($activeSchoolId, $activeRoleName): bool {
                            return (string) ($activeSchoolId ?? '') === (string) ($context['school_id'] ?? '')
                                && (string) ($activeRoleName ?? '') === (string) $context['role_name'];
                        });
                    @endphp

                    <div class="p-4 sm:p-5">
                        <div class="rounded-2xl border border-brand-primary/20 bg-brand-primary/5 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ __('ui.current_workspace') }}</p>
                            <p class="mt-1 truncate text-sm font-semibold text-text-primary">
                                {{ $activeContext ? $activeContext['label'].' - '.$activeContext['school_name'] : __('ui.choose_workspace') }}
                            </p>
                        </div>

                        <div
                            x-data
                            x-init="$nextTick(() => $refs.activeRole?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' }))"
                            data-role-switcher="segmented"
                            class="role-switcher-scroll no-scrollbar mt-4 overflow-x-auto overscroll-x-contain scroll-smooth pb-1"
                        >
                            <div role="group" aria-label="{{ __('ui.switch_role') }}" class="inline-flex min-w-max flex-nowrap items-stretch gap-1 rounded-full border border-border-subtle bg-bg-primary p-1 shadow-inner">
                                @foreach ($contexts as $context)
                                    @php
                                        $isActive = (string) ($activeSchoolId ?? '') === (string) ($context['school_id'] ?? '')
                                            && (string) ($activeRoleName ?? '') === (string) $context['role_name'];
                                    @endphp

                                    <form method="POST" action="{{ route('role-context.switch') }}" class="shrink-0">
                                        @csrf
                                        <input type="hidden" name="school_id" value="{{ $context['school_id'] }}">
                                        <input type="hidden" name="role_name" value="{{ $context['role_name'] }}">
                                        <button
                                            type="submit"
                                            data-role-name="{{ $context['role_name'] }}"
                                            data-state="{{ $isActive ? 'active' : 'inactive' }}"
                                            x-on:click="$el.classList.add('role-segment-button-selecting')"
                                            @if ($isActive) x-ref="activeRole" aria-current="true" disabled @endif
                                            @class([
                                                'role-segment-button group relative inline-flex min-h-11 shrink-0 items-center gap-2 overflow-hidden rounded-full border px-4 py-2 text-start text-sm font-semibold transition-all duration-300 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg-secondary',
                                                'role-segment-button-active border-brand-primary bg-brand-primary text-white shadow-md shadow-emerald-900/20' => $isActive,
                                                'border-transparent bg-transparent text-text-secondary hover:border-border-subtle hover:bg-bg-tertiary hover:text-text-primary hover:shadow-sm active:scale-[0.98]' => ! $isActive,
                                            ])
                                        >
                                            <span class="relative z-10 whitespace-nowrap">
                                                <span class="block leading-5">{{ $context['label'] }}</span>
                                                <span @class([
                                                    'block max-w-40 truncate text-[11px] font-medium leading-4 transition-colors duration-300',
                                                    'text-white/80' => $isActive,
                                                    'text-text-tertiary group-hover:text-text-secondary' => ! $isActive,
                                                ])>{{ $context['school_name'] }}</span>
                                            </span>
                                            @if ($isActive)
                                                <svg aria-hidden="true" class="relative z-10 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                    <path d="m5 12 4 4L19 6"></path>
                                                </svg>
                                                <span class="sr-only">{{ __('ui.current_context') }}</span>
                                            @endif
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </x-ui.table-card>

            @if (auth()->user()?->hasRole('super_admin'))
                <section data-installation-admin-action class="flex flex-col gap-4 rounded-2xl border border-border-subtle bg-bg-secondary p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">
                    <div class="min-w-0">
                        <h2 class="font-semibold text-text-primary">{{ __('ui.installation_admin') }}</h2>
                        <p class="mt-1 text-sm text-text-secondary">{{ __('ui.installation_admin_intro') }}</p>
                    </div>
                    <form method="POST" action="{{ route('workspace.installation-admin') }}">
                        @csrf
                        <button type="submit" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-full border border-border-subtle bg-bg-secondary px-4 py-2 text-sm font-semibold text-text-secondary shadow-sm transition-all duration-200 ease-out hover:border-border-hover hover:bg-bg-tertiary hover:text-text-primary hover:shadow-md">
                            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"></path>
                            </svg>
                            {{ __('ui.installation_admin') }}
                        </button>
                    </form>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
