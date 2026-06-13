@php
    $cardClasses = $class ?? '';
@endphp

<x-ui.stat-card
    :label="$card['label']"
    :value="$card['value']"
    :meta="$card['meta'] ?? null"
    :tone="$card['tone'] ?? 'neutral'"
    :href="$card['href'] ?? null"
    class="{{ $cardClasses }}"
/>
