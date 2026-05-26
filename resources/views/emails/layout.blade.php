@php
    $resolvedBranding = app(\App\Services\Branding\BrandingService::class)->current($school ?? null);
    $brandName = data_get($schoolBranding ?? null, 'name') ?: data_get($school ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $brandName = data_get($resolvedBranding, 'brand_name', $brandName);
    $brandColor = data_get($schoolBranding ?? null, 'primary_color') ?: data_get($school ?? null, 'primary_color') ?: '#047857';
    $brandColor = data_get($resolvedBranding, 'primary_color', $brandColor);
    $brandLogo = data_get($schoolBranding ?? null, 'logo_url') ?: (isset($school) && method_exists($school, 'logoUrl') ? $school->logoUrl() : null);
    $brandLogo = data_get($resolvedBranding, 'logo_url') ?: $brandLogo;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? $subjectLine ?? 'Notification' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="padding:32px 24px;text-align:center;background:{{ $brandColor }};">
                            @if ($brandLogo)
                                <img src="{{ $brandLogo }}" alt="{{ $brandName }}" style="max-height:48px;max-width:180px;display:inline-block;">
                            @else
                                <div style="color:#ffffff;font-size:20px;font-weight:700;">{{ $brandName }}</div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 24px;color:#1e293b;font-size:15px;line-height:1.7;">
                            {{ $slot }}
                            @include('emails.partials.brand-footer', ['school' => $school ?? null])
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;text-align:center;font-size:12px;color:#64748b;border-top:1px solid #e2e8f0;">
                            &copy; {{ date('Y') }} {{ $brandName }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
