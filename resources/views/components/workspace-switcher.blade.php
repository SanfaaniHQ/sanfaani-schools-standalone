@php
    $contexts = collect($contexts)->values();
    $active = $contexts->firstWhere('key', $activeKey);
    $switcherId = 'workspace-switcher-'.substr(md5($activeKey ?: 'workspace'), 0, 10);
@endphp

@if ($contexts->count() > 1)
    <div
        x-data="workspaceChooser({
            selectedKey: @js($activeKey),
            selectedLabel: @js($active['label'] ?? ''),
            totalItems: {{ $contexts->count() }}
        })"
        x-id="['workspace-trigger', 'workspace-panel']"
        x-init="mount()"
        @keydown.escape.window="close()"
        class="relative"
        data-workspace-switcher
        data-workspace-count="{{ $contexts->count() }}"
    >
        <button
            type="button"
            x-ref="trigger"
            @click="openChooser()"
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
            <span class="hidden max-w-44 truncate sm:inline">{{ $active['label'] ?? __('ui.switch_workspace') }}</span>
            <span class="sm:hidden">{{ __('ui.switch_workspace') }}</span>
        </button>

        <template x-teleport="body">
            <div
                x-cloak
                x-show="open"
                x-transition.opacity.duration.150ms
                @click.self="backdropClose()"
                @keydown.tab="trapFocus($event)"
                class="fixed inset-0 z-[90] flex items-center justify-center overflow-hidden bg-slate-950/45 px-3 py-3 backdrop-blur-[2px] sm:px-6 sm:py-8"
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
                    data-positioning="centered-modal"
                    class="workspace-selector-modal flex max-h-[calc(100dvh-1.5rem)] w-full max-w-[820px] flex-col overflow-hidden rounded-lg border border-border-subtle bg-bg-secondary shadow-2xl sm:max-h-[min(46rem,calc(100dvh-4rem))]"
                    data-workspace-modal
                >
                    <form
                        method="POST"
                        action="{{ route('workspace.store') }}"
                        class="flex min-h-0 flex-1 flex-col"
                        data-workspace-switch-form
                        data-loading-text="{{ __('ui.switching_workspace') }}"
                        @submit="submitWorkspace($event)"
                    >
                        @csrf
                        <input type="hidden" name="workspace" x-bind:value="selectedKey" data-selected-workspace-key>

                        <header class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-border-subtle bg-bg-secondary px-4 py-4 sm:px-6 sm:py-5">
                            <div class="min-w-0">
                                <h2 id="{{ $switcherId }}-title" class="text-xl font-semibold text-text-primary sm:text-2xl">{{ __('ui.workspace_selector_title') }}</h2>
                                <p id="{{ $switcherId }}-description" class="mt-2 text-sm leading-6 text-text-secondary">{{ __('ui.workspace_selector_intro') }}</p>
                            </div>
                            <button
                                type="button"
                                @click="close()"
                                :disabled="submitting"
                                class="inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center rounded-md text-text-tertiary transition hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary disabled:cursor-not-allowed disabled:opacity-50"
                                aria-label="{{ __('ui.close_workspace_selector') }}"
                                data-workspace-close
                            >
                                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 6 6 18M6 6l12 12"></path>
                                </svg>
                            </button>
                        </header>

                        <div class="workspace-selector-content min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6 sm:py-5" data-workspace-options>
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-workspace-grid>
                                @foreach ($contexts as $context)
                                    <x-workspace-option :context="$context" :active-key="$activeKey" :index="$loop->index" />
                                @endforeach
                            </div>
                        </div>

                        <footer class="sticky bottom-0 z-10 border-t border-border-subtle bg-bg-secondary px-4 py-3 sm:px-6" data-workspace-footer>
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div class="flex min-h-8 items-center justify-center gap-2 md:justify-start" x-show="hasMultiplePages()" data-workspace-page-controls>
                                    <button
                                        type="button"
                                        @click="previousPage()"
                                        :disabled="submitting || page === 0"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border-subtle text-text-secondary transition hover:border-border-hover hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary disabled:cursor-not-allowed disabled:opacity-40"
                                        aria-label="{{ __('ui.previous_workspaces') }}"
                                        data-workspace-prev
                                    >
                                        <svg aria-hidden="true" class="h-4 w-4 rtl-flip" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="m15 18-6-6 6-6"></path>
                                        </svg>
                                    </button>

                                    <div class="flex items-center gap-1.5" role="group" aria-label="{{ __('ui.workspace_pages') }}">
                                        <template x-for="dot in pages()" :key="dot">
                                            <button
                                                type="button"
                                                @click="goToPage(dot)"
                                                :disabled="submitting"
                                                class="h-2.5 rounded-full transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg-secondary"
                                                :class="page === dot ? 'w-6 bg-brand-primary' : 'w-2.5 bg-border-hover hover:bg-text-tertiary'"
                                                :aria-label="pageLabel(dot)"
                                                :aria-current="page === dot ? 'page' : null"
                                                data-workspace-page-dot
                                            ></button>
                                        </template>
                                    </div>

                                    <button
                                        type="button"
                                        @click="nextPage()"
                                        :disabled="submitting || page >= totalPages() - 1"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border-subtle text-text-secondary transition hover:border-border-hover hover:bg-bg-tertiary hover:text-text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary disabled:cursor-not-allowed disabled:opacity-40"
                                        aria-label="{{ __('ui.next_workspaces') }}"
                                        data-workspace-next
                                    >
                                        <svg aria-hidden="true" class="h-4 w-4 rtl-flip" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="m9 18 6-6-6-6"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                    <button type="button" @click="close()" :disabled="submitting" class="ui-button-secondary" data-workspace-cancel>
                                        {{ __('ui.cancel') }}
                                    </button>
                                    <button
                                        type="submit"
                                        class="ui-button-primary"
                                        :disabled="!selectedKey || submitting"
                                        :aria-busy="submitting.toString()"
                                        data-workspace-submit
                                        data-loading-text="{{ __('ui.switching_workspace') }}"
                                    >
                                        <span x-show="!submitting">{{ __('ui.continue') }}</span>
                                        <span x-cloak x-show="submitting" class="inline-flex items-center gap-2">
                                            <span class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                                            <span>{{ __('ui.switching_workspace') }}</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </footer>
                    </form>
                </section>
            </div>
        </template>
    </div>
@endif
