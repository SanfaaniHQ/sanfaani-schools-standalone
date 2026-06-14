@php
    $admissionBranding = app(\App\Services\Branding\BrandingService::class)->forSchool($school);
    $admissionTokens = app(\App\Support\Ui\BrandingUiTokens::class);
    $admissionBrandName = data_get($admissionBranding, 'brand_name', $school->name);
    $admissionLogo = data_get($admissionBranding, 'logo_url');
    $admissionInitials = data_get($admissionBranding, 'initials', $school->initials());
    $admissionPrimary = $admissionTokens->color(data_get($admissionBranding, 'primary_color'), '#047857');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admissions') - {{ $admissionBrandName }}</title>
    <style>
        :root { color-scheme: light; --brand: {{ $admissionPrimary }}; --ink: #172033; --muted: #667085; --line: #dfe4ea; --paper: #ffffff; --wash: #f4f7f6; }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--wash); color: var(--ink); font-family: Inter, ui-sans-serif, system-ui, -apple-system, sans-serif; line-height: 1.5; }
        a { color: var(--brand); }
        .shell { width: min(960px, calc(100% - 32px)); margin: 0 auto; padding: 28px 0 48px; }
        .top { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 24px; }
        .brand { align-items: center; color: var(--ink); display: inline-flex; font-size: 20px; font-weight: 800; gap: 10px; text-decoration: none; }
        .brand-logo { align-items: center; background: var(--brand); border-radius: 8px; color: #fff; display: inline-flex; font-size: 12px; font-weight: 800; height: 36px; justify-content: center; overflow: hidden; width: 36px; }
        .brand-logo img { background: #fff; height: 100%; object-fit: contain; padding: 3px; width: 100%; }
        .nav { display: flex; flex-wrap: wrap; gap: 16px; font-size: 14px; }
        .card { background: var(--paper); border: 1px solid var(--line); border-radius: 8px; padding: clamp(20px, 4vw, 40px); box-shadow: 0 14px 40px rgba(20, 50, 40, .07); }
        .eyebrow { color: var(--brand); font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
        h1 { margin: 8px 0 12px; font-size: clamp(30px, 5vw, 48px); line-height: 1.08; }
        h2 { margin: 0 0 14px; font-size: 22px; }
        p { color: var(--muted); }
        .grid { display: grid; gap: 18px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .field { display: grid; gap: 7px; }
        .field.full { grid-column: 1 / -1; }
        label { font-size: 14px; font-weight: 700; }
        input, select, textarea { width: 100%; border: 1px solid #cbd5df; border-radius: 8px; background: white; padding: 11px 12px; color: var(--ink); font: inherit; }
        input:focus, select:focus, textarea:focus { border-color: var(--brand); outline: 3px solid rgba(4, 120, 87, .12); }
        .button { display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 8px; background: var(--brand); color: white; padding: 12px 18px; font-weight: 800; text-decoration: none; cursor: pointer; }
        .button:disabled { cursor: wait; opacity: .72; }
        .button.secondary { background: #e8f3ef; color: #075e46; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 24px; }
        .notice { border-radius: 8px; background: #ecfdf3; color: #08643f; padding: 14px 16px; }
        .error { border-radius: 8px; background: #fff1f2; color: #9f1239; padding: 12px 14px; }
        .details { display: grid; gap: 12px; margin: 24px 0; }
        .detail { display: flex; justify-content: space-between; gap: 20px; border-bottom: 1px solid var(--line); padding-bottom: 10px; }
        .token { overflow-wrap: anywhere; border-radius: 8px; background: #172033; color: white; padding: 12px; font-family: ui-monospace, SFMono-Regular, monospace; }
        @media (max-width: 680px) { .grid { grid-template-columns: 1fr; } .top { align-items: flex-start; flex-direction: column; } .field.full { grid-column: auto; } }
    </style>
</head>
<body>
<main class="shell">
    @unless($embed ?? false)
        <header class="top">
            <a class="brand" href="{{ route('admissions.index') }}">
                <span class="brand-logo">
                    @if ($admissionLogo)
                        <img src="{{ $admissionLogo }}" alt="{{ $admissionBrandName }} logo">
                    @else
                        {{ $admissionInitials }}
                    @endif
                </span>
                <span>{{ $admissionBrandName }}</span>
            </a>
            <nav class="nav" aria-label="Admission navigation">
                <a href="{{ route('admissions.index') }}">Admissions</a>
                <a href="{{ route('admissions.apply') }}">Apply</a>
                <a href="{{ route('admissions.track') }}">Track application</a>
            </nav>
        </header>
    @endunless
    @yield('content')
</main>
<script>
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');

            if (!button || button.disabled) {
                return;
            }

            button.disabled = true;
            button.textContent = button.dataset.loadingText || form.dataset.loadingText || 'Submitting...';
        });
    });
</script>
</body>
</html>
