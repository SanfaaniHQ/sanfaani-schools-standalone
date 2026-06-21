<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            :title="__('ui.messages')"
            :description="__('ui.messages_intro')"
        />
    </x-slot>

    @php
        $conversationTypes = [
            'general' => __('ui.category_general'),
            'academic' => __('ui.category_academic'),
            'finance' => __('ui.category_finance'),
            'result' => __('ui.category_result'),
            'attendance' => __('ui.category_attendance'),
        ];
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1">
                <x-ui.form-section :title="__('ui.start_conversation')" :description="__('ui.start_conversation_intro')">
                    @if ($errors->any())
                        <x-ui.alert tone="danger">{{ $errors->first() }}</x-ui.alert>
                    @endif

                    <form method="POST" action="{{ route('portal.conversations.store') }}" class="space-y-4">
                        @csrf

                        <x-ui.input
                            :label="__('ui.conversation_subject')"
                            name="subject"
                            :value="old('subject')"
                            required
                        />

                        <label class="block space-y-1.5 text-sm">
                            <span class="block font-medium text-text-primary">{{ __('ui.conversation_category') }}</span>
                            <select name="conversation_type" class="ui-input">
                                @foreach ($conversationTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('conversation_type', 'general') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block space-y-1.5 text-sm">
                            <span class="block font-medium text-text-primary">{{ __('ui.recipients') }}</span>
                            <select name="recipient_user_ids[]" multiple size="6" class="ui-input min-h-36">
                                <option value="">{{ __('ui.auto_assign_school_team') }}</option>
                                @foreach ($recipients as $recipient)
                                    <option value="{{ $recipient->id }}" @selected(in_array((string) $recipient->id, array_map('strval', old('recipient_user_ids', [])), true))>
                                        {{ $recipient->name }} - {{ $recipient->email }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="block text-xs text-text-secondary">{{ __('ui.recipients_help') }}</span>
                        </label>

                        <label class="block space-y-1.5 text-sm">
                            <span class="block font-medium text-text-primary">{{ __('ui.message') }}</span>
                            <textarea name="body" rows="5" class="ui-input" required>{{ old('body') }}</textarea>
                        </label>

                        <x-ui.button type="submit" class="w-full">
                            {{ __('ui.send_message') }}
                        </x-ui.button>
                    </form>
                </x-ui.form-section>
            </div>

            <div class="lg:col-span-2">
                <x-ui.table-card :title="__('ui.conversations')">
                    @if ($conversations->isEmpty())
                        <div class="p-5">
                            <x-ui.empty-state
                                :title="__('ui.no_conversations')"
                                :body="__('ui.no_conversations_help')"
                            />
                        </div>
                    @else
                        <div class="divide-y">
                            @foreach ($conversations as $conversation)
                                <a href="{{ route('portal.conversations.show', ['conversationId' => $conversation->id]) }}" class="block p-5 transition hover:bg-bg-tertiary">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <h4 class="truncate font-semibold text-text-primary">{{ $conversation->subject }}</h4>
                                            <p class="mt-1 text-sm text-text-secondary">
                                                {{ $conversation->typeLabel() }} - {{ trans_choice('ui.messages_count', $conversation->messages_count, ['count' => $conversation->messages_count]) }}
                                            </p>
                                            <p class="mt-1 text-xs text-text-tertiary">
                                                {{ __('ui.last_activity') }}: {{ $conversation->last_message_at?->diffForHumans() ?? __('ui.no_activity') }}
                                            </p>
                                        </div>

                                        <x-ui.badge :status="$conversation->statusLabel()" />
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <x-slot name="footer">
                            {{ $conversations->links() }}
                        </x-slot>
                    @endif
                </x-ui.table-card>
            </div>
    </div>
</x-app-layout>
