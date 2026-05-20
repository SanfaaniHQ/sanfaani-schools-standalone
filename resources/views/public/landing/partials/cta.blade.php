<x-marketing.cta-panel
    :title="$title ?? __('marketing.cta_panel.title')"
    :body="$body ?? __('marketing.cta_panel.body', ['platform' => config('sanfaani.platform_name', 'Sanfaani Schools')])"
    :primary-href="$primaryHref ?? route('landing.demo')"
    :primary-label="$primaryLabel ?? __('ui.request_demo')"
    :secondary-href="$secondaryHref ?? route('public.results.index')"
    :secondary-label="$secondaryLabel ?? __('ui.check_result')"
/>
