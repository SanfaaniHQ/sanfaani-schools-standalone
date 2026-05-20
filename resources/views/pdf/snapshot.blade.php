<!DOCTYPE html>
<html lang="{{ $snapshot->locale }}" dir="{{ $snapshot->direction }}">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: dejavusans, sans-serif;
            color: #111827;
            font-size: 12px;
            line-height: 1.6;
        }
        .header {
            border-bottom: 2px solid {{ data_get($snapshot->branding_snapshot, 'primary_color', '#047857') }};
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            color: {{ data_get($snapshot->branding_snapshot, 'secondary_color', '#0f172a') }};
        }
        .muted {
            color: #64748b;
        }
        .meta {
            margin-top: 8px;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            vertical-align: top;
        }
        th {
            background: #f8fafc;
            font-weight: bold;
        }
        .section {
            margin-top: 18px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: {{ data_get($snapshot->branding_snapshot, 'primary_color', '#047857') }};
        }
        .footer {
            margin-top: 24px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            font-size: 10px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $snapshot->title }}</div>
        <div class="muted">{{ data_get($snapshot->branding_snapshot, 'name', config('app.name')) }}</div>
        <div class="meta">
            {{ __('cbt.reference') }}: {{ $snapshot->reference_code ?? $snapshot->snapshot_uuid }} |
            {{ __('cbt.verification_code') }}: {{ $snapshot->verification_code }} |
            {{ __('cbt.generated_at') }}: {{ optional($snapshot->generated_at)->format('d M Y, h:i A') }}
        </div>
    </div>

    @foreach ($snapshot->payload as $section => $value)
        <div class="section">
            <div class="section-title">{{ str($section)->replace('_', ' ')->title() }}</div>
            @if (is_array($value))
                <table>
                    <tbody>
                        @foreach ($value as $key => $item)
                            <tr>
                                <th>{{ is_numeric($key) ? $loop->iteration : str($key)->replace('_', ' ')->title() }}</th>
                                <td>
                                    @if (is_array($item))
                                        <pre>{{ json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                                    @else
                                        {{ $item }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>{{ $value }}</p>
            @endif
        </div>
    @endforeach

    <div class="footer">
        {{ __('cbt.immutable_snapshot_notice') }}
    </div>
</body>
</html>
