@extends('installer.layout')

@section('content')
    <form method="POST" action="{{ route('installer.smtp.store') }}" data-loading-text="Saving email settings..." class="space-y-5">
        @csrf
        <div>
            <h2 class="text-xl font-semibold text-text-primary">Email settings</h2>
            <p class="mt-2 text-sm text-text-secondary">Review the email account the school will use for password resets, admission messages, and portal notifications. Passwords are not displayed after this step.</p>
        </div>

        <div class="rounded-md border border-border-subtle bg-bg-secondary p-4 text-sm text-text-secondary">
            Ask your hosting provider or email provider for the mail host, port, encryption, username, password, sender email, and sender name. You can continue with a safe log setting and configure live email after login if needed.
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Email delivery</span>
                <select name="mailer" class="mt-1 w-full rounded-md border-border-subtle">
                    @foreach (['log' => 'Save email log only', 'smtp' => 'Send through mail provider', 'array' => 'Testing only'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('mailer', $smtp['mailer'] ?? 'log') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Mail host</span>
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
                <span class="font-semibold text-text-primary">Sender email</span>
                <input type="email" name="from_address" value="{{ old('from_address', $smtp['from_address'] ?? '') }}" class="mt-1 w-full rounded-md border-border-subtle">
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-text-primary">Sender name</span>
                <input name="from_name" value="{{ old('from_name', $smtp['from_name'] ?? '') }}" class="mt-1 w-full rounded-md border-border-subtle">
            </label>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('installer.school') }}" class="rounded-md border border-border-subtle px-4 py-2 text-sm font-semibold text-text-secondary hover:bg-bg-secondary">Back</a>
            <button type="submit" data-loading-text="Saving email settings..." class="rounded-md bg-brand-primary px-4 py-2 text-sm font-semibold text-white hover:bg-brand-primary/90">Save and continue</button>
        </div>
    </form>
@endsection
