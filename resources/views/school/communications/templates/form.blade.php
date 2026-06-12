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

        <form method="POST" action="{{ $action }}" class="space-y-6">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <x-ui.panel title="Template Details" description="Template keys are school-scoped and can be used by future operational hooks.">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="template_key" class="block text-sm font-medium text-text-primary">Template Key</label>
                        <input id="template_key" name="template_key" value="{{ old('template_key', $template->template_key) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" placeholder="live_class.reminder">
                        @error('template_key')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
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
                            @foreach ($channels as $channel)
                                <option value="{{ $channel }}" @selected(old('channel', $template->channel) === $channel)>{{ str($channel)->replace('_', ' ')->title() }}</option>
                            @endforeach
                        </select>
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
                        <p class="mt-1 text-xs text-text-tertiary">Variables such as @{{ student_name }} are rendered as plain text by the service.</p>
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
</x-app-layout>
