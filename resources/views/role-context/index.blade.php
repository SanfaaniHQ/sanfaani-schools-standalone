<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">{{ __('ui.switch_role') }}</h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ __('ui.switch_role_intro') }}
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="rounded-lg border bg-white shadow-sm">
                @if (empty($contexts))
                    <div class="p-8 text-center text-sm text-gray-500">
                        {{ __('ui.no_role_contexts') }}
                    </div>
                @else
                    <div class="divide-y">
                        @foreach ($contexts as $context)
                            @php
                                $isActive = (string) ($activeSchoolId ?? '') === (string) ($context['school_id'] ?? '')
                                    && (string) ($activeRoleName ?? '') === (string) $context['role_name'];
                            @endphp

                            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-gray-900">{{ $context['label'] }}</h3>
                                    <p class="mt-1 break-words text-sm text-gray-500">{{ $context['school_name'] }}</p>
                                </div>

                                @if ($isActive)
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold uppercase text-green-700">
                                        {{ __('ui.current_context') }}
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('role-context.switch') }}" class="w-full sm:w-auto">
                                        @csrf
                                        <input type="hidden" name="school_id" value="{{ $context['school_id'] }}">
                                        <input type="hidden" name="role_name" value="{{ $context['role_name'] }}">
                                        <button type="submit" class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 sm:w-auto">
                                            {{ __('ui.switch') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
