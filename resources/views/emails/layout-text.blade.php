{{ $subject ?? $subjectLine ?? 'Notification' }}

{{ $slot }}

{{ data_get($schoolBranding ?? null, 'name') ?: data_get($school ?? null, 'name') ?: data_get($platformSettings ?? null, 'platform_name', config('app.name', 'Sanfaani Schools')) }}
