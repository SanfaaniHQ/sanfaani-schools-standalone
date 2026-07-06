@props(['context', 'activeKey' => null])

@php($isActive = ($context['key'] ?? null) === $activeKey)

<form method="POST" action="{{ route('workspace.store') }}">
    @csrf
    <input type="hidden" name="workspace" value="{{ $context['key'] }}">
    <button
        type="submit"
        data-workspace-key="{{ $context['key'] }}"
        data-workspace-type="{{ $context['type'] }}"
        data-role-name="{{ $context['role_name'] }}"
        @if ($isActive) x-ref="activeWorkspace" aria-current="true" disabled @endif
        @class([
            'flex min-h-12 w-full items-center justify-between gap-3 rounded-xl border px-3 py-2 text-start text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary',
            'border-brand-primary bg-brand-primary/10 text-brand-primary' => $isActive,
            'border-border-subtle bg-bg-primary text-text-secondary hover:border-border-hover hover:bg-bg-tertiary hover:text-text-primary' => ! $isActive,
        ])
    >
        <span class="min-w-0">
            <span class="block truncate font-semibold">{{ $context['role_label'] ?? $context['label'] }}</span>
            @if (($context['type'] ?? null) === \App\Services\TenantContext::WORKSPACE_SCHOOL)
                <span class="block truncate text-xs text-text-tertiary">{{ $context['school_name'] }}</span>
            @else
                <span class="block text-xs text-text-tertiary">Installation-level workspace</span>
            @endif
        </span>
        @if ($isActive)
            <svg aria-hidden="true" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 4 4L19 6"></path></svg>
            <span class="sr-only">{{ __('ui.current_context') }}</span>
        @endif
    </button>
</form>
