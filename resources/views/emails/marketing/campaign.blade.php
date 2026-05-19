@php
    $platformName = data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools'));
    $unsubscribeUrl = $trackingUrls['unsubscribe_url'] ?? '#';
    $openUrl = $trackingUrls['open_url'] ?? null;
@endphp

@component('emails.layout', ['subject' => $campaign->subject])
    <p style="margin:0 0 10px 0;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#047857;">{{ $platformName }}</p>
    <h1 style="margin:0 0 16px 0;font-size:22px;line-height:1.3;color:#0f172a;">{{ $campaign->preview_text ?: $campaign->name }}</h1>

    <div style="font-size:15px;line-height:1.7;color:#334155;">
        {!! $renderedBody !!}
    </div>

    <p style="margin:24px 0 0 0;font-size:12px;line-height:1.6;color:#64748b;">
        You are receiving this because you contacted {{ $platformName }} or requested product information.
        <a href="{{ $unsubscribeUrl }}" style="color:#047857;">Unsubscribe</a>
    </p>

    @if ($openUrl)
        <img src="{{ $openUrl }}" alt="" width="1" height="1" style="display:none;width:1px;height:1px;border:0;">
    @endif
@endcomponent
