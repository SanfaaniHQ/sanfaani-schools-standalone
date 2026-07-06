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
                        $activeContext = collect($contexts)->firstWhere('key', $activeWorkspaceKey);
                        $schoolContexts = collect($contexts)->where('type', \App\Services\TenantContext::WORKSPACE_SCHOOL);
                        $installationContext = collect($contexts)->firstWhere('type', \App\Services\TenantContext::WORKSPACE_INSTALLATION_ADMIN);
                    @endphp

                    <div class="p-4 sm:p-5">
                        <div class="rounded-2xl border border-brand-primary/20 bg-brand-primary/5 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ __('ui.current_workspace') }}</p>
                            <p class="mt-1 truncate text-sm font-semibold text-text-primary">
                                {{ $activeContext ? $activeContext['label'] : __('ui.choose_workspace') }}
                            </p>
                        </div>

                        <div
                            x-data
                            x-init="$nextTick(() => $refs.activeRole?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' }))"
                            data-role-switcher="segmented"
                            class="role-switcher-scroll no-scrollbar mt-4 overflow-x-auto overscroll-x-contain scroll-smooth pb-1"
                        >
                            <div role="group" aria-label="{{ __('ui.switch_role') }}" class="inline-flex min-w-max flex-nowrap items-stretch gap-1 rounded-full border border-border-subtle bg-bg-primary p-1 shadow-inner">
                                @foreach ($schoolContexts as $context)
                                    @php
                                        $isActive = $context['key'] === $activeWorkspaceKey;
                                    @endphp

                                    <form method="POST" action="{{ route('role-context.switch') }}" class="shrink-0">
                                        @csrf
                                        <input type="hidden" name="workspace" value="{{ $context['key'] }}">
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
                                                <span class="block leading-5">{{ $context['role_label'] }}</span>
                                                <span @class([
                                                    'block max-w-40 truncate text-[11px] font-medium leading-4 transition-colors duration-300',
                                                    'text-white/80' => $isActive,
                                                    'text-text-tertiary group-hover:text-text-secondary' => ! $isActive,
                                                ])>{{ $context['school_name'] ?? 'Installation-level workspace' }}</span>
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

                        @if ($installationContext)
                            <section data-installation-admin-action class="mt-5 flex flex-col gap-3 rounded-xl border border-border-subtle bg-bg-primary p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-text-primary">{{ $installationContext['label'] }}</h3>
                                    <p class="mt-1 text-xs text-text-secondary">Installation-level access is separate from school roles.</p>
                                </div>
                                <form method="POST" action="{{ route('workspace.installation-admin') }}">
                                    @csrf
                                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-md border border-border-subtle px-3 text-sm font-semibold text-text-secondary hover:bg-bg-tertiary">
                                        {{ $installationContext['label'] }}
                                    </button>
                                </form>
                            </section>
                        @endif
                    </div>
                @endif
            </x-ui.table-card>

        </div>
    </div>
</x-app-layout>
