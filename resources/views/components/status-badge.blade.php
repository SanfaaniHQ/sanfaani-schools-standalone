@props(['status'])

@php
    $normalized = strtolower(str_replace(' ', '_', (string) $status));

    $classes = [
        'active' => 'bg-green-50 text-green-700 ring-green-600/20',
        'valid' => 'bg-green-50 text-green-700 ring-green-600/20',
        'inactive' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
        'invalid' => 'bg-red-50 text-red-700 ring-red-600/20',
        'draft' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
        'submitted' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'returned' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
        'reviewed' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'approved' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
        'published' => 'bg-green-50 text-green-700 ring-green-600/20',
        'unpublished' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
        'voided' => 'bg-red-50 text-red-700 ring-red-600/20',
        'missing' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'pending' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
        'pending_payment' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
        'manual_pending' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
        'generated' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
        'revoked' => 'bg-red-50 text-red-700 ring-red-600/20',
        'expired' => 'bg-red-50 text-red-700 ring-red-600/20',
        'used' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
        'unused' => 'bg-green-50 text-green-700 ring-green-600/20',
        'paid' => 'bg-green-50 text-green-700 ring-green-600/20',
        'failed' => 'bg-red-50 text-red-700 ring-red-600/20',
        'cancelled' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
        'refunded' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'archived' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
    ][$normalized] ?? 'bg-gray-100 text-gray-700 ring-gray-500/20';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-3 py-1 text-xs font-medium ring-1 ring-inset {$classes}"]) }}>
    {{ ucfirst(str_replace('_', ' ', $normalized)) }}
</span>
