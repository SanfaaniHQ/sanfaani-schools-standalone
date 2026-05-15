@props(['status'])

@php
    $normalized = strtolower(str_replace(' ', '_', (string) $status));

    $classes = [
        'active' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        'valid' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        'inactive' => 'border-border-subtle bg-bg-secondary text-text-secondary',
        'invalid' => 'border-rose-500/20 bg-rose-500/10 text-rose-400',
        'completed' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        'repeating' => 'border-amber-500/20 bg-amber-500/10 text-amber-400',
        'graduated' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        'transferred' => 'border-border-subtle bg-bg-secondary text-text-secondary',
        'withdrawn' => 'border-rose-500/20 bg-rose-500/10 text-rose-400',
        'draft' => 'border-border-subtle bg-bg-secondary text-text-secondary',
        'submitted' => 'border-amber-500/20 bg-amber-500/10 text-amber-400',
        'returned' => 'border-amber-500/20 bg-amber-500/10 text-amber-400',
        'reviewed' => 'border-indigo-500/20 bg-indigo-500/10 text-indigo-400',
        'approved' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        'published' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        'unpublished' => 'border-border-subtle bg-bg-secondary text-text-secondary',
        'voided' => 'border-rose-500/20 bg-rose-500/10 text-rose-400',
        'missing' => 'border-amber-500/20 bg-amber-500/10 text-amber-400',
        'pending' => 'border-amber-500/20 bg-amber-500/10 text-amber-400',
        'pending_payment' => 'border-amber-500/20 bg-amber-500/10 text-amber-400',
        'manual_pending' => 'border-amber-500/20 bg-amber-500/10 text-amber-400',
        'generated' => 'border-indigo-500/20 bg-indigo-500/10 text-indigo-400',
        'revoked' => 'border-rose-500/20 bg-rose-500/10 text-rose-400',
        'expired' => 'border-rose-500/20 bg-rose-500/10 text-rose-400',
        'used' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        'unused' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        'paid' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400',
        'failed' => 'border-rose-500/20 bg-rose-500/10 text-rose-400',
        'cancelled' => 'border-border-subtle bg-bg-secondary text-text-secondary',
        'refunded' => 'border-indigo-500/20 bg-indigo-500/10 text-indigo-400',
        'archived' => 'border-border-subtle bg-bg-secondary text-text-secondary',
    ][$normalized] ?? 'border-border-subtle bg-bg-secondary text-text-secondary';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full border px-2.5 py-1 text-xs font-medium {$classes}"]) }}>
    {{ ucfirst(str_replace('_', ' ', $normalized)) }}
</span>
