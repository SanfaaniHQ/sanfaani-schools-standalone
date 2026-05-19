{{ $campaign->preview_text ?: $campaign->name }}

{{ strip_tags($renderedBody) }}

You are receiving this because you contacted {{ data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools')) }} or requested product information.
Unsubscribe: {{ $trackingUrls['unsubscribe_url'] ?? '#' }}
