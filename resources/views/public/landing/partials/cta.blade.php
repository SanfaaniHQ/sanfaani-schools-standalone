<x-marketing.cta-panel
    :title="$title ?? 'Ready to make result management easier?'"
    :body="$body ?? 'Start with a guided demo and see how ' . config('sanfaani.platform_name', 'Sanfaani Schools') . ' can support your school operations.'"
    :primary-href="$primaryHref ?? route('landing.demo')"
    :primary-label="$primaryLabel ?? 'Request Demo'"
    :secondary-href="$secondaryHref ?? route('public.results.index')"
    :secondary-label="$secondaryLabel ?? 'Check Result'"
/>
