@props(['context', 'activeKey' => null, 'index' => 0])

@php
    $key = (string) ($context['key'] ?? '');
    $roleName = (string) ($context['role_name'] ?? '');
    $schoolName = $context['school_name'] ?? null;
    $isActive = $key === $activeKey;
    $isInstallation = ($context['type'] ?? null) === \App\Services\TenantContext::WORKSPACE_INSTALLATION_ADMIN;
    $roleLabel = $isInstallation
        ? ($context['label'] ?? __('ui.installation_admin'))
        : ($context['role_label'] ?? $context['label'] ?? __('ui.workspace'));
    $iconRole = $isInstallation ? 'installation_admin' : $roleName;
    $workspaceLabel = $context['label'] ?? $roleLabel;
@endphp

<button
    type="button"
    data-workspace-card
    data-workspace-option
    data-workspace-index="{{ $index }}"
    data-workspace-key="{{ $key }}"
    data-workspace-type="{{ $context['type'] ?? '' }}"
    data-role-name="{{ $roleName }}"
    data-workspace-label="{{ $workspaceLabel }}"
    data-active="{{ $isActive ? 'true' : 'false' }}"
    data-installation-workspace="{{ $isInstallation ? 'true' : 'false' }}"
    x-show="visibleOnPage($el)"
    x-transition.opacity.duration.100ms
    x-bind:data-selected="selectedKey === @js($key) ? 'true' : 'false'"
    x-bind:aria-pressed="(selectedKey === @js($key)).toString()"
    x-on:click="selectWorkspace(@js($key), @js($workspaceLabel))"
    x-on:keydown.arrow-down.prevent="focusNext($el)"
    x-on:keydown.arrow-right.prevent="focusNext($el)"
    x-on:keydown.arrow-up.prevent="focusPrevious($el)"
    x-on:keydown.arrow-left.prevent="focusPrevious($el)"
    x-on:keydown.home.prevent="focusFirst()"
    x-on:keydown.end.prevent="focusLast()"
    @if ($isActive) x-ref="activeWorkspace" aria-current="true" @endif
    class="group relative flex min-h-[8.75rem] w-full flex-col items-start justify-between gap-4 rounded-lg border border-border-subtle bg-bg-secondary p-4 text-start text-sm text-text-primary shadow-sm transition duration-200 ease-default hover:border-border-hover hover:bg-bg-tertiary hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg-secondary"
>
    <span class="flex w-full items-start justify-between gap-3">
        <span
            @class([
                'flex h-11 w-11 shrink-0 items-center justify-center rounded-md border',
                'border-brand-primary/30 bg-brand-primary/10 text-brand-primary' => ! $isInstallation,
                'border-emerald-700/30 bg-emerald-700/10 text-emerald-700 dark:text-emerald-300' => $isInstallation,
            ])
            aria-hidden="true"
        >
            @switch($iconRole)
                @case('school_admin')
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 21V7l8-4 8 4v14"></path>
                        <path d="M9 21v-6h6v6"></path>
                        <path d="M9 9h.01M12 9h.01M15 9h.01"></path>
                    </svg>
                    @break

                @case('teacher')
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 5h16v11H4z"></path>
                        <path d="M8 21h8"></path>
                        <path d="M12 16v5"></path>
                        <path d="M8 9h8M8 12h5"></path>
                    </svg>
                    @break

                @case('result_officer')
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 4h6l1 2h3v15H5V6h3z"></path>
                        <path d="M9 14l2 2 4-5"></path>
                    </svg>
                    @break

                @case('accountant')
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="3" width="14" height="18" rx="2"></rect>
                        <path d="M8 7h8M8 11h2M12 11h2M16 11h.01M8 15h2M12 15h2M16 15h.01"></path>
                    </svg>
                    @break

                @case('admissions_officer')
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9.5" cy="7" r="4"></circle>
                        <path d="M19 8v6M22 11h-6"></path>
                    </svg>
                    @break

                @case('parent')
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    @break

                @case('student')
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10 12 5 2 10l10 5 10-5Z"></path>
                        <path d="M6 12v5c3 2 9 2 12 0v-5"></path>
                    </svg>
                    @break

                @default
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3 5 6v5c0 4.5 3 8.5 7 10 4-1.5 7-5.5 7-10V6z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
            @endswitch
        </span>

        <span class="flex shrink-0 items-center gap-2">
            @if ($isActive)
                <span class="rounded-full border border-brand-primary/20 bg-brand-primary/10 px-2 py-0.5 text-[11px] font-semibold text-brand-primary" data-current-workspace-badge>
                    {{ __('ui.current') }}
                </span>
            @endif
            <span class="workspace-card-check flex h-6 w-6 items-center justify-center rounded-full bg-brand-primary text-white opacity-0 transition group-data-[selected=true]:opacity-100" aria-hidden="true">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <path d="m5 12 4 4L19 6"></path>
                </svg>
            </span>
        </span>
    </span>

    <span class="min-w-0">
        <span class="block text-base font-semibold leading-6 text-text-primary">{{ $roleLabel }}</span>
        @if ($isInstallation)
            <span class="mt-1 inline-flex rounded-full border border-emerald-700/20 bg-emerald-700/10 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 dark:text-emerald-300">
                {{ __('ui.installation_level_workspace') }}
            </span>
        @elseif ($schoolName)
            <span class="mt-1 block truncate text-sm leading-5 text-text-secondary">{{ $schoolName }}</span>
        @endif
    </span>

    <span class="sr-only">
        @if ($isActive)
            {{ __('ui.current_workspace') }}.
        @endif
        {{ __('ui.select_workspace', ['workspace' => $workspaceLabel]) }}
    </span>
</button>
