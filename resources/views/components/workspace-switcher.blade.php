@php
    $contexts = collect($contexts)->values();
    $installationContexts = $contexts->where('type', \App\Services\TenantContext::WORKSPACE_INSTALLATION_ADMIN);
    $schoolContexts = $contexts->where('type', \App\Services\TenantContext::WORKSPACE_SCHOOL)->groupBy('school_name');
    $active = $contexts->firstWhere('key', $activeKey);
@endphp

@if ($contexts->count() > 1)
    <div x-data="{ open: false }" @keydown.escape.window="open = false" class="relative" data-workspace-switcher>
        <button
            type="button"
            @click="open = true; $nextTick(() => $refs.activeWorkspace?.focus())"
            class="inline-flex h-11 items-center gap-2 rounded-full border border-brand-primary/30 bg-brand-primary px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-hover sm:h-10 sm:px-4"
            aria-haspopup="dialog"
            aria-controls="workspace-switcher-popup"
            :aria-expanded="open.toString()"
        >
            <svg aria-hidden="true" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M7 7h10M7 17h10M10 4 7 7l3 3m4 4 3 3-3 3"></path>
            </svg>
            <span class="hidden max-w-44 truncate sm:inline">{{ $active['label'] ?? __('ui.switch_role') }}</span>
            <span class="sm:hidden">{{ __('ui.switch_role') }}</span>
        </button>

        <div
            id="workspace-switcher-popup"
            x-cloak
            x-show="open"
            x-transition.opacity.duration.150ms
            x-on:click.self="open = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="workspace-switcher-title"
            class="fixed inset-0 z-[80] flex items-start justify-center overflow-y-auto bg-black/60 px-4 py-4 backdrop-blur-sm sm:items-center sm:py-10"
        >
            <section class="w-full max-w-2xl overflow-hidden rounded-2xl border border-border-subtle bg-bg-secondary shadow-2xl">
                <header class="flex items-start justify-between gap-4 border-b border-border-subtle px-4 py-4 sm:px-5">
                    <div>
                        <p id="workspace-switcher-title" class="text-sm font-semibold text-text-primary">{{ __('ui.workspace_switcher_title') }}</p>
                        <p class="mt-1 text-xs leading-5 text-text-secondary">{{ __('ui.workspace_switcher_intro') }}</p>
                    </div>
                    <button type="button" @click="open = false" class="inline-flex h-9 w-9 items-center justify-center rounded-md text-text-tertiary hover:bg-bg-tertiary" aria-label="{{ __('ui.close_navigation') }}">
                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"></path></svg>
                    </button>
                </header>

                <div class="border-b border-border-subtle px-4 py-3 sm:px-5">
                    <p class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ __('ui.current_workspace') }}</p>
                    <p class="mt-1 truncate text-sm font-semibold text-text-primary">{{ $active['label'] ?? __('ui.choose_workspace') }}</p>
                </div>

                <div class="max-h-[65vh] space-y-5 overflow-y-auto p-4 sm:p-5" data-workspace-options>
                    @if ($installationContexts->isNotEmpty())
                        <section aria-labelledby="installation-workspaces-heading">
                            <h3 id="installation-workspaces-heading" class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ __('ui.installation_admin') }}</h3>
                            <div class="mt-2 grid gap-2">
                                @foreach ($installationContexts as $context)
                                    <x-workspace-option :context="$context" :active-key="$activeKey" />
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($schoolContexts->isNotEmpty())
                        <section aria-labelledby="school-workspaces-heading">
                            <h3 id="school-workspaces-heading" class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ __('ui.available_school_workspaces') }}</h3>
                            <div class="mt-2 space-y-3">
                                @foreach ($schoolContexts as $schoolName => $schoolWorkspaces)
                                    <div>
                                        <p class="truncate text-xs font-semibold text-text-secondary">{{ $schoolName }}</p>
                                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                            @foreach ($schoolWorkspaces as $context)
                                                <x-workspace-option :context="$context" :active-key="$activeKey" />
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>

                <footer class="flex justify-end border-t border-border-subtle px-4 py-3 sm:px-5">
                    <a
                        href="{{ route(($active['type'] ?? null) === \App\Services\TenantContext::WORKSPACE_SCHOOL ? 'role-context.index' : 'workspace.create') }}"
                        class="inline-flex min-h-10 items-center justify-center rounded-md border border-border-subtle px-3 text-sm font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary"
                    >
                        {{ __('ui.manage_role_contexts') }}
                    </a>
                </footer>
            </section>
        </div>
    </div>
@endif
