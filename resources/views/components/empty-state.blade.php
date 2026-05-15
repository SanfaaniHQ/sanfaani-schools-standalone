@props(['title' => 'No data found', 'description' => 'Get started by creating a new item.', 'action' => null])

<div class="px-4 py-12 text-center">
    <div class="mx-auto mb-4 h-12 w-12 text-slate-400">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7m16 0v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-5m16 0h-2.586a1 1 0 0 0-.707.293l-2.414 2.414a1 1 0 0 1-.707.293h-3.172a1 1 0 0 1-.707-.293l-2.414-2.414A1 1 0 0 0 6.586 13H4" />
        </svg>
    </div>
    <h3 class="mt-2 text-sm font-semibold text-slate-900">{{ $title }}</h3>
    <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
    @if ($action)
        <div class="mt-6">{{ $action }}</div>
    @endif
</div>
