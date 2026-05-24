@extends('installer.layout')

@section('content')
    <form method="POST" action="{{ route('installer.smtp.store') }}" class="space-y-5">
        @csrf
        <div>
            <h2 class="text-xl font-semibold text-text-primary">SMTP Setup Placeholder</h2>
            <p class="mt-2 text-sm text-text-secondary">Capture non-sensitive mail intent for review. This step does not send test email and does not persist SMTP passwords.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Mailer</span>
                <select name="mailer" class="mt-1 w-full rounded-md border-border-subtle">
                    @foreach (['log' => 'Log', 'smtp' => 'SMTP', 'array' => 'Array'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('mailer', $smtp['mailer'] ?? 'log') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Host</span>
                <input name="host" value="{{ old('host', $smtp['host'] ?? '') }}" class="mt-1 w-full rounded-md border-border-subtle">
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Port</span>
                <input type="number" name="port" value="{{ old('port', $smtp['port'] ?? '') }}" class="mt-1 w-full rounded-md border-border-subtle">
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Encryption</span>
                <select name="encryption" class="mt-1 w-full rounded-md border-border-subtle">
                    <option value="">Auto</option>
                    @foreach (['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('encryption', $smtp['encryption'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Username</span>
                <input name="username" value="" autocomplete="off" class="mt-1 w-full rounded-md border-border-subtle">
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Password</span>
                <input type="password" name="password" autocomplete="new-password" class="mt-1 w-full rounded-md border-border-subtle">
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">From address</span>
                <input type="email" name="from_address" value="{{ old('from_address', $smtp['from_address'] ?? '') }}" class="mt-1 w-full rounded-md border-border-subtle">
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">From name</span>
                <input name="from_name" value="{{ old('from_name', $smtp['from_name'] ?? '') }}" class="mt-1 w-full rounded-md border-border-subtle">
            </label>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.school') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <button type="submit" class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Save placeholder</button>
        </div>
    </form>
@endsection
