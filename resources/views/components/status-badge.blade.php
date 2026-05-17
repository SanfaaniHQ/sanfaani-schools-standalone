@props(['status'])

@php
    $normalized = strtolower(str_replace(' ', '_', (string) $status));

    $classes = [
        'active' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        'valid' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        'inactive' => 'border-border-subtle bg-bg-secondary text-text-secondary',
        'invalid' => 'border-rose-500/20 bg-rose-500/10 text-rose-700 dark:text-rose-300',
        'completed' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        'repeating' => 'border-amber-500/20 bg-amber-500/10 text-amber-700 dark:text-amber-300',
        'graduated' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        'transferred' => 'border-border-subtle bg-bg-secondary text-text-secondary',
        'withdrawn' => 'border-rose-500/20 bg-rose-500/10 text-rose-700 dark:text-rose-300',
        'draft' => 'border-border-subtle bg-bg-secondary text-text-secondary',
        'submitted' => 'border-amber-500/20 bg-amber-500/10 text-amber-700 dark:text-amber-300',
        'returned' => 'border-amber-500/20 bg-amber-500/10 text-amber-700 dark:text-amber-300',
        'reviewed' => 'border-indigo-500/20 bg-indigo-500/10 text-teal-700 dark:text-teal-300',
        'approved' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        'published' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        'unpublished' => 'border-border-subtle bg-bg-secondary text-text-secondary',
        'voided' => 'border-rose-500/20 bg-rose-500/10 text-rose-700 dark:text-rose-300',
        'missing' => 'border-amber-500/20 bg-amber-500/10 text-amber-700 dark:text-amber-300',
        'locked' => 'border-border-active bg-bg-tertiary text-text-primary',
        'pending' => 'border-amber-500/20 bg-amber-500/10 text-amber-700 dark:text-amber-300',
        'pending_payment' => 'border-amber-500/20 bg-amber-500/10 text-amber-700 dark:text-amber-300',
        'manual_pending' => 'border-amber-500/20 bg-amber-500/10 text-amber-700 dark:text-amber-300',
        'generated' => 'border-indigo-500/20 bg-indigo-500/10 text-teal-700 dark:text-teal-300',
        'revoked' => 'border-rose-500/20 bg-rose-500/10 text-rose-700 dark:text-rose-300',
        'expired' => 'border-rose-500/20 bg-rose-500/10 text-rose-700 dark:text-rose-300',
        'used' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        'unused' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        'paid' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        'failed' => 'border-rose-500/20 bg-rose-500/10 text-rose-700 dark:text-rose-300',
        'cancelled' => 'border-border-subtle bg-bg-secondary text-text-secondary',
        'refunded' => 'border-indigo-500/20 bg-indigo-500/10 text-indigo-400',
        'archived' => 'border-border-subtle bg-bg-secondary text-text-secondary',
    ][$normalized] ?? 'border-border-subtle bg-bg-secondary text-text-secondary';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full border px-2.5 py-1 text-xs font-medium {$classes}"]) }}>
    {{ ucfirst(str_replace('_', ' ', $normalized)) }}
</span>
