@component('emails.layout', ['subject' => $subjectLine ?? 'Platform Notification'])
    <p style="margin:0 0 10px 0;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#475569;">Platform Transactional Message</p>
    <h1 style="margin:0 0 16px 0;font-size:22px;line-height:1.3;color:#0f172a;">{{ $headline }}</h1>
    <div style="font-size:15px;line-height:1.7;color:#334155;white-space:pre-line;">{{ $body }}</div>
@endcomponent
