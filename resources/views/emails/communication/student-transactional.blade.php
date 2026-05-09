<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;color:#111827;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 0;background:#f3f4f6;">
        <tr><td align="center">
            <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border-radius:12px;overflow:hidden;">
                <tr><td style="background:#1d4ed8;color:#ffffff;padding:20px 24px;"><div style="font-size:18px;font-weight:700;">{{ $school?->name ?? ($platformSettings->platform_name ?? 'Sanfaani Schools') }}</div><div style="margin-top:6px;font-size:13px;color:#dbeafe;">Student Communication</div></td></tr>
                <tr><td style="padding:24px;"><h1 style="margin:0 0 14px 0;font-size:20px;color:#111827;">{{ $headline }}</h1><div style="font-size:15px;line-height:1.7;color:#1f2937;white-space:pre-line;">{{ $body }}</div></td></tr>
                <tr><td style="padding:16px 24px;background:#eff6ff;border-top:1px solid #bfdbfe;font-size:12px;color:#1e3a8a;">{{ $platformSettings->support_email ?? 'support@sanfaani.com' }}</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
@include('emails.communication.default')
