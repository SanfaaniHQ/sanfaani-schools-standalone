@php
    $resolvedBranding = app(\App\Services\Branding\BrandingService::class)->current($school ?? null);
    $footerText = data_get($resolvedBranding, 'email_footer_text');
    $brandName = data_get($resolvedBranding, 'brand_name', config('app.name'));
@endphp

<p style="margin-top: 24px; color: #64748b; font-size: 12px; line-height: 1.6;">
    {{ $footerText ?: 'Powered by '.$brandName.'.' }}
</p>
