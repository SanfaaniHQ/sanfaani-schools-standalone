@props(['context', 'activeKey' => null])

@php
    $workspaceKey = (string) ($context['key'] ?? '');
    $isActive = $workspaceKey === $activeKey;
@endphp

<button
    type="button"
    role="radio"
    data-workspace-type="{{ $context['type'] }}"
    data-role-name="{{ $context['role_name'] }}"
    data-workspace-option
    data-workspace-key="{{ $workspaceKey }}"
    data-workspace-label="{{ $context['label'] }}"
    data-search-text="{{ str($context['label'].' '.$context['role_label'].' '.$context['school_name'].' '.$context['role_name'])->lower() }}"
    data-active="{{ $isActive ? 'true' : 'false' }}"
    x-bind:data-selected="isSelected(@js($workspaceKey)).toString()"
    x-bind:aria-checked="isSelected(@js($workspaceKey)).toString()"
    x-show="matches($el)"
    x-on:click="selectWorkspace($el)"
    x-on:keydown.enter.prevent="selectWorkspace($el)"
    x-on:keydown.space.prevent="selectWorkspace($el)"
    x-on:keydown.arrow-down.prevent="focusNext($el)"
    x-on:keydown.arrow-right.prevent="focusNext($el)"
    x-on:keydown.arrow-up.prevent="focusPrevious($el)"
    x-on:keydown.arrow-left.prevent="focusPrevious($el)"
    x-on:keydown.home.prevent="focusFirst()"
    x-on:keydown.end.prevent="focusLast()"
    @if ($isActive) x-ref="activeWorkspace" aria-current="true" @endif
    class="relative flex min-h-20 w-full items-center justify-between gap-3 rounded-lg border px-3 py-3 text-start text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
    x-bind:class="isSelected(@js($workspaceKey)) ? 'border-brand-primary bg-brand-primary/10 text-text-primary shadow-sm ring-2 ring-brand-primary/20' : 'border-border-subtle bg-bg-primary text-text-secondary hover:border-border-hover hover:bg-bg-tertiary hover:text-text-primary'"
>
    <span class="min-w-0 flex-1">
        <span class="flex flex-wrap items-center gap-1.5">
            <span class="block truncate font-semibold">{{ $context['role_label'] ?? $context['label'] }}</span>
            @if ($isActive)
                <span class="rounded-full border border-brand-primary/30 bg-brand-primary/10 px-2 py-0.5 text-[0.68rem] font-semibold uppercase tracking-normal text-brand-primary">{{ __('ui.current_context') }}</span>
            @endif
            <span x-cloak x-show="isSelected(@js($workspaceKey))" class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[0.68rem] font-semibold uppercase tracking-normal text-emerald-700 dark:text-emerald-300">{{ __('ui.selected_workspace') }}</span>
        </span>
        @if (($context['type'] ?? null) === \App\Services\TenantContext::WORKSPACE_SCHOOL)
            <span class="mt-1 block truncate text-xs text-text-tertiary">{{ $context['school_name'] }}</span>
        @else
            <span class="mt-1 block text-xs text-text-tertiary">{{ __('ui.installation_level_workspace') }}</span>
        @endif
    </span>
    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-border-subtle bg-bg-secondary text-text-tertiary">
        <svg x-cloak x-show="isSelected(@js($workspaceKey))" aria-hidden="true" class="h-4 w-4 shrink-0 text-brand-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 4 4L19 6"></path></svg>
        <span x-show="!isSelected(@js($workspaceKey))" class="h-2.5 w-2.5 rounded-full bg-border-hover" aria-hidden="true"></span>
        @if ($isActive)
            <span class="sr-only">{{ __('ui.current_workspace') }}</span>
        @endif
    </span>
</button>
