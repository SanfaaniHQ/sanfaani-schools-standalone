@props(['context', 'activeKey' => null])

@php($isActive = ($context['key'] ?? null) === $activeKey)

<form method="POST" action="{{ route('workspace.store') }}" data-workspace-switch-form data-loading-text="{{ __('ui.switching_workspace') }}" x-on:submit="markSubmitting(@js($context['key']))">
    @csrf
    <input type="hidden" name="workspace" value="{{ $context['key'] }}">
    <button
        type="{{ $isActive ? 'button' : 'submit' }}"
        data-workspace-type="{{ $context['type'] }}"
        data-role-name="{{ $context['role_name'] }}"
        data-workspace-option
        data-workspace-label="{{ $context['label'] }}"
        data-search-text="{{ str($context['label'].' '.$context['role_label'].' '.$context['school_name'].' '.$context['role_name'])->lower() }}"
        data-active="{{ $isActive ? 'true' : 'false' }}"
        x-show="matches($el)"
        x-on:keydown.arrow-down.prevent="focusNext($el)"
        x-on:keydown.arrow-right.prevent="focusNext($el)"
        x-on:keydown.arrow-up.prevent="focusPrevious($el)"
        x-on:keydown.arrow-left.prevent="focusPrevious($el)"
        x-on:keydown.home.prevent="focusFirst()"
        x-on:keydown.end.prevent="focusLast()"
        @if ($isActive) x-on:click="close()" @endif
        @if ($isActive) x-ref="activeWorkspace" aria-current="true" @endif
        @class([
            'flex min-h-12 w-full items-center justify-between gap-3 rounded-lg border px-3 py-2 text-start text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary',
            'border-brand-primary bg-brand-primary/10 text-brand-primary' => $isActive,
            'border-border-subtle bg-bg-primary text-text-secondary hover:border-border-hover hover:bg-bg-tertiary hover:text-text-primary' => ! $isActive,
        ])
    >
        <span class="min-w-0">
            <span class="block truncate font-semibold">{{ $context['role_label'] ?? $context['label'] }}</span>
            @if (($context['type'] ?? null) === \App\Services\TenantContext::WORKSPACE_SCHOOL)
                <span class="block truncate text-xs text-text-tertiary">{{ $context['school_name'] }}</span>
            @else
                <span class="block text-xs text-text-tertiary">{{ __('ui.installation_level_workspace') }}</span>
            @endif
        </span>
        <span class="flex shrink-0 items-center gap-2">
            <span x-cloak x-show="submittingKey === @js($context['key'])" class="text-xs font-semibold">{{ __('ui.switching_workspace') }}</span>
            @if ($isActive)
                <svg aria-hidden="true" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 4 4L19 6"></path></svg>
                <span class="sr-only">{{ __('ui.current_context') }}</span>
            @else
                <svg x-cloak x-show="submittingKey === @js($context['key'])" aria-hidden="true" class="h-4 w-4 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3a9 9 0 1 0 9 9"></path>
                </svg>
            @endif
        </span>
    </button>
</form>
