<?php

return [
    'component_defaults' => [
        'panel_padding' => 'p-5 sm:p-6',
        'card_radius' => 'rounded-md',
        'focus_ring' => 'focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg-primary',
        'surface' => 'border border-border-subtle bg-bg-secondary shadow-sm',
    ],

    'status_badge_map' => [
        'active' => 'success',
        'approved' => 'success',
        'completed' => 'success',
        'generated' => 'info',
        'info' => 'info',
        'manual_pending' => 'warning',
        'pending' => 'warning',
        'pending_payment' => 'warning',
        'returned' => 'warning',
        'submitted' => 'warning',
        'warning' => 'warning',
        'cancelled' => 'neutral',
        'draft' => 'neutral',
        'inactive' => 'neutral',
        'archived' => 'neutral',
        'danger' => 'danger',
        'expired' => 'danger',
        'failed' => 'danger',
        'invalid' => 'danger',
        'revoked' => 'danger',
    ],

    'dashboard_density' => [
        'default' => 'comfortable',
        'stat_grid' => 'grid gap-4 sm:grid-cols-2 xl:grid-cols-4',
        'section_gap' => 'space-y-6',
    ],

    'responsive_breakpoints' => [
        'mobile' => 'default',
        'tablet' => 'sm',
        'desktop' => 'lg',
        'wide' => 'xl',
    ],

    'accessibility_minimums' => [
        'text_contrast' => 'WCAG AA for normal text where branding colors are used',
        'focus_visible' => true,
        'minimum_touch_target' => '44px on mobile actions',
        'table_readability' => 'headers, horizontal overflow, and readable row spacing',
    ],
];
