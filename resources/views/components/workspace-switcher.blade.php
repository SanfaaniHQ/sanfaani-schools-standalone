@php
    $contexts = collect($contexts)->values();
    $installationContexts = $contexts->where('type', \App\Services\TenantContext::WORKSPACE_INSTALLATION_ADMIN);
    $schoolContexts = $contexts->where('type', \App\Services\TenantContext::WORKSPACE_SCHOOL)->groupBy('school_name');
    $active = $contexts->firstWhere('key', $activeKey);
    $switcherId = 'workspace-switcher-'.substr(md5($activeKey ?: 'workspace'), 0, 10);
    $searchesWorkspaces = $contexts->count() > 5;
@endphp

@if ($contexts->count() > 1)
    <div
        x-data="workspaceChooser({ searchable: {{ $searchesWorkspaces ? 'true' : 'false' }} })"
        x-id="['workspace-trigger', 'workspace-panel', 'workspace-search']"
        x-init="mount()"
        @keydown.escape.window="close()"
        class="relative"
        data-workspace-switcher
        data-workspace-count="{{ $contexts->count() }}"
    >
        <button
            type="button"
            x-ref="trigger"
            @click="toggle()"
            @keydown.arrow-down.prevent="openChooser('keyboard')"
            @keydown.arrow-up.prevent="openChooser('keyboard')"
            class="inline-flex min-h-11 max-w-full items-center gap-2 rounded-md border border-brand-primary/30 bg-brand-primary px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg-secondary sm:min-h-10 sm:px-4"
            aria-haspopup="dialog"
            :aria-controls="$id('workspace-panel')"
            :aria-expanded="open.toString()"
            data-workspace-trigger
        >
            <svg aria-hidden="true" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M7 7h10M7 17h10M10 4 7 7l3 3m4 4 3 3-3 3"></path>
            </svg>
            <span class="hidden max-w-44 truncate sm:inline">{{ $active['label'] ?? __('ui.switch_role') }}</span>
            <span class="sm:hidden">{{ __('ui.switch_role') }}</span>
        </button>

        <template x-teleport="body">
            <div
                x-cloak
                x-show="open"
                x-transition.opacity.duration.120ms
                @click.self="close()"
                @keydown.tab="trapFocus($event)"
                class="fixed inset-0 z-[90] overflow-hidden p-0"
                :class="isSheet ? 'flex items-end justify-center bg-black/60 px-3 pb-3 pt-12 backdrop-blur-sm sm:px-4 sm:pb-4 md:items-center md:p-6' : 'bg-transparent'"
                data-workspace-overlay
            >
                <section
                    x-ref="panel"
                    x-bind:id="$id('workspace-panel')"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="{{ $switcherId }}-title"
                    aria-describedby="{{ $switcherId }}-description"
                    data-workspace-chooser-panel
                    data-positioning="anchored-popover"
                    x-bind:style="isSheet ? null : panelStyle"
                    :class="isSheet
                        ? 'workspace-sheet pointer-events-auto flex max-h-[calc(100dvh-1rem)] w-full max-w-3xl flex-col overflow-hidden rounded-t-lg border border-border-subtle bg-bg-secondary shadow-2xl md:max-h-[min(46rem,calc(100dvh-3rem))] md:rounded-lg'
                        : 'workspace-popover pointer-events-auto fixed flex w-[min(40rem,calc(100vw-1rem))] max-h-[min(42rem,calc(100dvh-1rem))] flex-col overflow-hidden rounded-lg border border-border-subtle bg-bg-secondary shadow-2xl'"
                    data-workspace-mobile-sheet
                >
                    <div class="mx-auto mt-2 h-1.5 w-12 rounded-full bg-border-hover md:hidden" aria-hidden="true"></div>

                    <header class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-border-subtle bg-bg-secondary px-4 py-4 sm:px-5">
                        <div class="min-w-0">
                            <p id="{{ $switcherId }}-title" class="text-base font-semibold text-text-primary">{{ __('ui.workspace_switcher_title') }}</p>
                            <p id="{{ $switcherId }}-description" class="mt-1 text-xs leading-5 text-text-secondary">{{ __('ui.workspace_switcher_intro') }}</p>
                        </div>
                        <button type="button" @click="close()" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md text-text-tertiary hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary" aria-label="{{ __('ui.close_navigation') }}">
                            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"></path></svg>
                        </button>
                    </header>

                    <div class="border-b border-border-subtle px-4 py-3 sm:px-5">
                        <p class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ __('ui.current_workspace') }}</p>
                        <p class="mt-1 truncate text-sm font-semibold text-text-primary">{{ $active['label'] ?? __('ui.choose_workspace') }}</p>
                    </div>

                    @if ($searchesWorkspaces)
                        <div class="border-b border-border-subtle px-4 py-3 sm:px-5">
                            <label class="sr-only" x-bind:for="$id('workspace-search')">{{ __('ui.search_workspaces') }}</label>
                            <div class="flex min-h-11 items-center gap-2 rounded-md border border-border-subtle bg-bg-primary px-3 focus-within:border-brand-primary focus-within:ring-2 focus-within:ring-emerald-700/20">
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-text-tertiary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.3-4.3"></path>
                                </svg>
                                <input
                                    x-ref="search"
                                    x-model.debounce.100ms="query"
                                    x-bind:id="$id('workspace-search')"
                                    type="search"
                                    autocomplete="off"
                                    class="h-10 flex-1 border-0 bg-transparent p-0 text-sm text-text-primary placeholder:text-text-tertiary focus:ring-0"
                                    placeholder="{{ __('ui.search_workspaces') }}"
                                    data-workspace-search
                                >
                            </div>
                        </div>
                    @endif

                    <div class="workspace-options-scroll flex-1 space-y-5 overflow-y-auto overscroll-contain p-4 sm:p-5" data-workspace-options>
                        @if ($installationContexts->isNotEmpty())
                            <section aria-labelledby="{{ $switcherId }}-installation-heading" data-workspace-group>
                                <h3 id="{{ $switcherId }}-installation-heading" class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ __('ui.installation_admin') }}</h3>
                                <div class="mt-2 grid gap-2">
                                    @foreach ($installationContexts as $context)
                                        <x-workspace-option :context="$context" :active-key="$activeKey" />
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if ($schoolContexts->isNotEmpty())
                            <section aria-labelledby="{{ $switcherId }}-school-heading" data-workspace-group>
                                <h3 id="{{ $switcherId }}-school-heading" class="text-xs font-semibold uppercase tracking-normal text-text-tertiary">{{ __('ui.available_school_workspaces') }}</h3>
                                <div class="mt-2 space-y-3">
                                    @foreach ($schoolContexts as $schoolName => $schoolWorkspaces)
                                        <div data-workspace-group>
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

                    <footer class="flex flex-col-reverse gap-2 border-t border-border-subtle px-4 py-3 sm:flex-row sm:justify-end sm:px-5">
                        <button type="button" @click="close()" class="ui-button-secondary sm:hidden">
                            {{ __('ui.close_navigation') }}
                        </button>
                        <a
                            href="{{ route(($active['type'] ?? null) === \App\Services\TenantContext::WORKSPACE_SCHOOL ? 'role-context.index' : 'workspace.create') }}"
                            class="ui-button-secondary"
                        >
                            {{ __('ui.manage_role_contexts') }}
                        </a>
                    </footer>
                </section>
            </div>
        </template>
    </div>
@endif
