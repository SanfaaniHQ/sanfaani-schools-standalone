@php($emailMeta = $metadata ?? [])

@component('emails.layout', ['subject' => $subjectLine ?? 'Student Notification', 'school' => $school ?? null])
    <h1 style="margin:0 0 16px 0;font-size:22px;line-height:1.3;color:#0f172a;">{{ $headline }}</h1>
    <div style="font-size:15px;line-height:1.7;color:#334155;white-space:pre-line;">{{ $body }}</div>

    @if (filled(data_get($emailMeta, 'action_url')))
        <div style="margin-top:24px;">
            <a href="{{ data_get($emailMeta, 'action_url') }}" style="display:inline-block;border-radius:8px;background:#4f46e5;color:#ffffff;padding:12px 18px;text-decoration:none;font-size:14px;font-weight:700;">
                {{ data_get($emailMeta, 'action_label', 'Open') }}
            </a>
        </div>
    @endif
@endcomponent
