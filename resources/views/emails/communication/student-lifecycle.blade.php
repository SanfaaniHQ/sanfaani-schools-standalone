<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;color:#111827;">
    @php
        $schoolLogoUrl = $school?->logoUrl();
        $schoolLogoUrl = $schoolLogoUrl && ! \Illuminate\Support\Str::startsWith($schoolLogoUrl, ['http://', 'https://'])
            ? url($schoolLogoUrl)
            : $schoolLogoUrl;
    @endphp
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 0;background:#f3f4f6;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="background:#0f766e;color:#ffffff;padding:20px 24px;">
                            @if ($schoolLogoUrl)
                                <img src="{{ $schoolLogoUrl }}" alt="{{ $school->name }}" style="display:block;max-height:44px;margin-bottom:12px;">
                            @endif
                            <div style="font-size:18px;font-weight:700;">{{ $school?->name ?? ($platformSettings->platform_name ?? 'Sanfaani Schools') }}</div>
                            <div style="margin-top:6px;font-size:13px;color:#ccfbf1;">{{ $headline }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <h1 style="margin:0 0 14px 0;font-size:20px;color:#111827;">{{ $headline }}</h1>
                            <div style="font-size:15px;line-height:1.7;color:#1f2937;white-space:pre-line;">{{ $body }}</div>

                            @if (filled(data_get($metadata, 'action_url')))
                                <div style="margin-top:24px;">
                                    <a href="{{ data_get($metadata, 'action_url') }}" style="display:inline-block;border-radius:6px;background:#0f766e;color:#ffffff;padding:12px 18px;text-decoration:none;font-size:14px;font-weight:700;">
                                        {{ data_get($metadata, 'action_label', 'Open') }}
                                    </a>
                                </div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;background:#f9fafb;border-top:1px solid #e5e7eb;font-size:12px;color:#6b7280;">
                            {{ $platformSettings->support_email ?? 'support@sanfaani.com' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
