<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-normal text-brand-primary">Notification Template</p>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $template->exists ? 'Edit Template' : 'Create Template' }}</h2>
                <p class="mt-1 text-sm text-text-secondary">Reusable operational notification text for {{ $school->name }}.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.communications.templates') }}" class="ui-button-secondary">Templates</a>
                <a href="{{ route('school.communications.index') }}" class="ui-button-secondary">Communication Center</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success" :body="session('success')" />
        @endif

        @if ($errors->any())
            <x-ui.alert tone="danger" body="Review the template fields and try again." />
        @endif

        @php
            $selectedKey = old('template_key', $template->template_key);
            $selectedTemplateOption = collect($templateOptions)->firstWhere('key', $selectedKey);
            $customKey = $selectedKey && ! $selectedTemplateOption ? $selectedKey : old('custom_template_key');
            $templateOptionPayload = collect($templateOptions)->keyBy('key')->all();
            $channelPayload = collect($channelOptions)->all();
        @endphp

        <form method="POST" action="{{ $action }}" class="space-y-6">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <x-ui.panel title="Template Details" description="Choose a supported template key and delivery channel. Advanced custom keys remain available for existing workflows.">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="template_key" class="block text-sm font-medium text-text-primary">Template Key</label>
                        <select id="template_key" name="template_key" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">Select a template key</option>
                            @foreach ($templateOptions as $option)
                                <option value="{{ $option['key'] }}" @selected($selectedKey === $option['key'])>{{ $option['label'] }}</option>
                            @endforeach
                            @if ($customKey)
                                <option value="{{ $customKey }}" @selected($selectedKey === $customKey)>Custom: {{ $customKey }}</option>
                            @endif
                        </select>
                        @error('template_key')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        <details class="mt-2">
                            <summary class="cursor-pointer text-xs font-semibold text-brand-primary">Advanced custom key</summary>
                            <input name="custom_template_key" value="{{ $customKey }}" class="mt-2 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" placeholder="custom.school.notice">
                            <p class="mt-1 text-xs text-text-tertiary">Use this only when your school already depends on a custom key.</p>
                            @error('custom_template_key')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </details>
                    </div>
                    <div>
                        <label for="title" class="block text-sm font-medium text-text-primary">Title</label>
                        <input id="title" name="title" value="{{ old('title', $template->title) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('title')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="channel" class="block text-sm font-medium text-text-primary">Channel</label>
                        <select id="channel" name="channel" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @foreach ($channelOptions as $channel => $option)
                                <option value="{{ $channel }}" @selected(old('channel', $template->channel) === $channel)>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                        <p id="channel-help" class="mt-1 text-xs text-text-tertiary">{{ data_get($channelOptions, old('channel', $template->channel).'.description') }}</p>
                        @error('channel')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="audience_type" class="block text-sm font-medium text-text-primary">Audience</label>
                        <select id="audience_type" name="audience_type" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            @foreach ($audienceTypes as $audienceType)
                                <option value="{{ $audienceType }}" @selected(old('audience_type', $template->audience_type) === $audienceType)>{{ str($audienceType)->replace('_', ' ')->title() }}</option>
                            @endforeach
                        </select>
                        @error('audience_type')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="subject" class="block text-sm font-medium text-text-primary">Subject</label>
                        <input id="subject" name="subject" value="{{ old('subject', $template->subject) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        @error('subject')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="body" class="block text-sm font-medium text-text-primary">Body</label>
                        <textarea id="body" name="body" rows="8" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('body', $template->body) }}</textarea>
                        <p class="mt-1 text-xs text-text-tertiary">Variables such as @{{ school_name }}, @{{ student_name }}, and @{{ login_url }} are rendered as plain text by the service.</p>
                        @error('body')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <input type="hidden" name="is_active" value="0">
                        <label class="inline-flex items-center gap-2 text-sm text-text-secondary">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" @checked(old('is_active', $template->is_active) == 1)>
                            Active template
                        </label>
                    </div>
                </div>
            </x-ui.panel>

            <x-ui.panel tone="info" title="Template Help">
                <div id="template-help" class="space-y-2 text-sm leading-6 text-text-secondary">
                    @if ($selectedTemplateOption)
                        <p class="font-semibold text-text-primary">{{ $selectedTemplateOption['label'] }}</p>
                        <p>{{ $selectedTemplateOption['description'] }}</p>
                        <p class="text-xs text-text-tertiary">Placeholders: {{ collect($selectedTemplateOption['placeholders'])->map(fn ($placeholder) => '{'.'{'.$placeholder.'}'.'}')->implode(', ') }}</p>
                    @else
                        <p>Select a template key to see what it is used for and which placeholders it supports.</p>
                    @endif
                </div>
            </x-ui.panel>

            <x-ui.panel tone="info" title="Provider Boundary">
                <p class="text-sm leading-6 text-text-secondary">
                    Database/logged notifications are local to the Laravel portal. Email-ready, SMS-ready, and WhatsApp-ready templates do not activate provider APIs, do not store credentials, and do not send real external messages from this form.
                </p>
            </x-ui.panel>

            <div class="flex justify-end">
                <button class="ui-button-primary">{{ $template->exists ? 'Update Template' : 'Create Template' }}</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const templateOptions = @json($templateOptionPayload);
            const channelOptions = @json($channelPayload);
            const keySelect = document.getElementById('template_key');
            const channelSelect = document.getElementById('channel');
            const subjectInput = document.getElementById('subject');
            const help = document.getElementById('template-help');
            const channelHelp = document.getElementById('channel-help');

            const renderTemplateHelp = () => {
                const option = templateOptions[keySelect?.value || ''];

                if (!help) {
                    return;
                }

                if (!option) {
                    help.innerHTML = '<p>Select a template key to see what it is used for and which placeholders it supports.</p>';
                    return;
                }

                const placeholders = (option.placeholders || []).map((placeholder) => '{' + '{' + placeholder + '}' + '}').join(', ');
                help.innerHTML = `
                    <p class="font-semibold text-text-primary">${option.label}</p>
                    <p>${option.description}</p>
                    <p class="text-xs text-text-tertiary">Placeholders: ${placeholders || 'None listed'}</p>
                `;

                if (subjectInput && !subjectInput.value && option.subject) {
                    subjectInput.value = option.subject;
                }

                if (channelSelect && option.channel && channelOptions[option.channel]) {
                    channelSelect.value = option.channel;
                    renderChannelHelp();
                }
            };

            const renderChannelHelp = () => {
                if (!channelHelp || !channelSelect) {
                    return;
                }

                channelHelp.textContent = channelOptions[channelSelect.value]?.description || '';
            };

            keySelect?.addEventListener('change', renderTemplateHelp);
            channelSelect?.addEventListener('change', renderChannelHelp);
        });
    </script>
</x-app-layout>
