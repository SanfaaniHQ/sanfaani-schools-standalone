<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Verify Result - {{ config('app.name', 'Sanfaani Schools') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 font-sans text-gray-900 antialiased">
        <main class="min-h-screen px-4 py-10">
            <div class="mx-auto max-w-2xl rounded-lg bg-white p-6 shadow-sm">
                <div class="border-b border-gray-200 pb-5">
                    <p class="text-sm font-medium uppercase tracking-wide text-gray-500">
                        Result Verification
                    </p>
                    <h1 class="mt-2 text-2xl font-semibold text-gray-900">
                        {{ $isValid ? 'Valid Result' : 'Invalid Result' }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        This page confirms authenticity only. It does not show full scores.
                    </p>
                </div>

                @if (! $verification)
                    <div class="mt-6 rounded-lg bg-red-50 p-4 text-sm font-medium text-red-700">
                        Verification code was not found.
                    </div>
                @else
                    <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="font-medium text-gray-500">Verification Code</dt>
                            <dd class="mt-1 text-gray-900">{{ $verification->verification_code }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <x-status-badge :status="$isValid ? 'valid' : 'invalid'" />
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">School</dt>
                            <dd class="mt-1 text-gray-900">{{ $verification->school->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Student</dt>
                            <dd class="mt-1 text-gray-900">{{ $maskedStudentName }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Admission Number</dt>
                            <dd class="mt-1 text-gray-900">{{ $maskedAdmissionNumber }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Session / Term</dt>
                            <dd class="mt-1 text-gray-900">
                                {{ $verification->academicSession->name ?? 'N/A' }} /
                                {{ $verification->term->name ?? 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Result Type</dt>
                            <dd class="mt-1 text-gray-900">{{ ucfirst(str_replace('_', ' ', $verification->result_type)) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Date Issued</dt>
                            <dd class="mt-1 text-gray-900">{{ $verification->issued_at?->format('d M Y, h:i A') ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                @endif

                <div class="mt-6">
                    <a href="{{ route('public.results.index') }}"
                       class="inline-flex rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Check Result
                    </a>
                </div>
            </div>
        </main>
    </body>
</html>
