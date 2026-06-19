<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            :title="__('ui.switch_role')"
            :description="__('ui.switch_role_intro')"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
            @endif

            <x-ui.table-card :title="__('ui.available_workspaces')" :description="__('ui.choose_workspace_help')">
                @if (empty($contexts))
                    <div class="p-5">
                        <x-ui.empty-state
                            :title="__('ui.no_role_contexts')"
                            :body="__('ui.no_role_contexts_help')"
                        />
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
                                    <h3 class="font-semibold text-text-primary">{{ $context['label'] }}</h3>
                                    <p class="mt-1 break-words text-sm text-text-secondary">{{ $context['school_name'] }}</p>
                                </div>

                                @if ($isActive)
                                    <x-ui.badge tone="success">{{ __('ui.current_context') }}</x-ui.badge>
                                @else
                                    <form method="POST" action="{{ route('role-context.switch') }}" class="w-full sm:w-auto">
                                        @csrf
                                        <input type="hidden" name="school_id" value="{{ $context['school_id'] }}">
                                        <input type="hidden" name="role_name" value="{{ $context['role_name'] }}">
                                        <x-ui.button type="submit" class="w-full sm:w-auto">
                                            {{ __('ui.switch') }}
                                        </x-ui.button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.table-card>
        </div>
    </div>
</x-app-layout>
