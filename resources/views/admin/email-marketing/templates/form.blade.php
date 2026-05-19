<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">{{ $template->exists ? 'Edit Template' : 'Create Template' }}</h2>
            <p class="mt-1 text-sm text-text-secondary">Templates are used only by marketing campaigns, not transactional mail.</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ $template->exists ? route('admin.email-marketing.templates.update', $template) : route('admin.email-marketing.templates.store') }}" class="space-y-6">
        @csrf
        @if ($template->exists)
            @method('PUT')
        @endif

        <section class="ui-card p-5">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-text-primary" for="name">Name</label>
                    <input id="name" name="name" value="{{ old('name', $template->name) }}" class="ui-input mt-1" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-text-primary" for="status">Status</label>
                    <select id="status" name="status" class="ui-input mt-1">
                        <option value="active" @selected(old('status', $template->status) === 'active')>Active</option>
                        <option value="archived" @selected(old('status', $template->status) === 'archived')>Archived</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-text-primary" for="subject">Subject</label>
                    <input id="subject" name="subject" value="{{ old('subject', $template->subject) }}" class="ui-input mt-1" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-text-primary" for="preview_text">Preview text</label>
                    <input id="preview_text" name="preview_text" value="{{ old('preview_text', $template->preview_text) }}" class="ui-input mt-1">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-text-primary" for="body">Body</label>
                    <textarea id="body" name="body" rows="14" class="ui-input mt-1 font-mono" required>{{ old('body', $template->body) }}</textarea>
                </div>
            </div>
        </section>

        <div class="flex flex-wrap gap-2">
            <button class="ui-button-primary">{{ $template->exists ? 'Update Template' : 'Save Template' }}</button>
            <a href="{{ route('admin.email-marketing.templates.index') }}" class="ui-button-secondary">Cancel</a>
        </div>
    </form>
</x-app-layout>
