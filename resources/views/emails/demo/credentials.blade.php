<div style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <h1 style="font-size: 22px; margin-bottom: 12px;">Your Sanfaani Schools demo is ready</h1>
    <p>A scoped demo environment has been prepared for {{ $demoSession->school?->name ?? 'your evaluation' }}.</p>
    <p>For security, temporary passwords are available for one-time display inside the admin demo screen. Use the listed demo emails with the temporary credentials provided by your Sanfaani contact.</p>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr>
                <th align="left" style="border-bottom: 1px solid #e5e7eb; padding: 8px;">Role</th>
                <th align="left" style="border-bottom: 1px solid #e5e7eb; padding: 8px;">Email</th>
                <th align="left" style="border-bottom: 1px solid #e5e7eb; padding: 8px;">Expires</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($demoSession->credentials as $credential)
                <tr>
                    <td style="border-bottom: 1px solid #f3f4f6; padding: 8px;">{{ $credential->label }}</td>
                    <td style="border-bottom: 1px solid #f3f4f6; padding: 8px;">{{ $credential->email }}</td>
                    <td style="border-bottom: 1px solid #f3f4f6; padding: 8px;">{{ $credential->expires_at?->toDayDateTimeString() ?? 'Not set' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px;">This email does not include server paths, internal errors, or application secrets.</p>
</div>
