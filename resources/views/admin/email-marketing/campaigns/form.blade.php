<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-text-primary">{{ $campaign->exists ? 'Edit Campaign' : 'Create Campaign' }}</h2>
            <p class="mt-1 text-sm text-text-secondary">Marketing mail is queued separately from transactional school mail.</p>
        </div>
    </x-slot>

    @php
        $filters = $campaign->target_filters ?? [];
    @endphp

    <form method="POST" action="{{ $campaign->exists ? route('admin.email-marketing.campaigns.update', $campaign) : route('admin.email-marketing.campaigns.store') }}" class="space-y-6">
        @csrf
        @if ($campaign->exists)
            @method('PUT')
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
            <section class="ui-card p-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-text-primary" for="name">Campaign name</label>
                        <input id="name" name="name" value="{{ old('name', $campaign->name) }}" class="ui-input mt-1" required>
                        @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-text-primary" for="template">Template</label>
                        <select id="template" name="marketing_email_template_id" class="ui-input mt-1">
                            <option value="">No template</option>
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}" @selected((int) old('marketing_email_template_id', $campaign->marketing_email_template_id) === (int) $template->id)>{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-text-primary" for="status">Status</label>
                        <select id="status" name="status" class="ui-input mt-1">
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', $campaign->status ?: 'draft') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-text-primary" for="subject">Subject</label>
                        <input id="subject" name="subject" value="{{ old('subject', $campaign->subject) }}" class="ui-input mt-1" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-text-primary" for="preview_text">Preview text</label>
                        <input id="preview_text" name="preview_text" value="{{ old('preview_text', $campaign->preview_text) }}" class="ui-input mt-1">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-text-primary" for="body">Email body</label>
                        <textarea id="body" name="body" rows="14" class="ui-input mt-1 font-mono text-sm" required>{{ old('body', $campaign->body) }}</textarea>
                        <p class="mt-2 text-xs text-text-tertiary">Placeholders: @{{school_name}}, @{{admin_name}}, @{{plan_name}}, @{{demo_link}}, @{{subscription_expiry}}, @{{platform_name}}.</p>
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="ui-card p-5">
                    <h3 class="font-semibold text-text-primary">Targeting</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-primary" for="target_type">Audience</label>
                            <select id="target_type" name="target_type" class="ui-input mt-1">
                                @foreach ($targetTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('target_type', $campaign->target_type ?: 'all_leads') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-text-primary">Lead stages</label>
                            <div class="mt-2 max-h-48 space-y-2 overflow-y-auto rounded-md border border-border-subtle bg-bg-primary p-3">
                                @foreach ($leadStatuses as $value => $label)
                                    <label class="flex items-center gap-2 text-sm text-text-secondary">
                                        <input type="checkbox" name="statuses[]" value="{{ $value }}" class="rounded border-border-subtle text-brand-primary" @checked(in_array($value, old('statuses', $filters['statuses'] ?? []), true))>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <input name="tags" value="{{ old('tags', implode(', ', $filters['tags'] ?? [])) }}" class="ui-input" placeholder="Tags, comma-separated">
                        <input name="source" value="{{ old('source', implode(', ', $filters['source'] ?? [])) }}" class="ui-input" placeholder="Sources, comma-separated">
                        <input name="country" value="{{ old('country', implode(', ', $filters['country'] ?? [])) }}" class="ui-input" placeholder="Countries, comma-separated">
                        <textarea name="school_names" rows="3" class="ui-input" placeholder="School names, comma-separated">{{ old('school_names', implode(', ', $filters['school_names'] ?? [])) }}</textarea>
                    </div>
                </section>

                <section class="ui-card p-5">
                    <h3 class="font-semibold text-text-primary">Delivery</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-text-primary" for="scheduled_at">Schedule</label>
                            <input id="scheduled_at" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $campaign->scheduled_at?->format('Y-m-d\\TH:i')) }}" class="ui-input mt-1">
                        </div>
                        <label class="flex items-center gap-2 text-sm font-semibold text-text-secondary">
                            <input type="checkbox" name="send_now" value="1" class="rounded border-border-subtle text-brand-primary">
                            <span>Queue immediately</span>
                        </label>
                    </div>
                </section>
            </aside>
        </div>

        <div class="flex flex-wrap gap-2">
            <button class="ui-button-primary" data-loading-text="Saving...">{{ $campaign->exists ? 'Update Campaign' : 'Save Campaign' }}</button>
            <a href="{{ route('admin.email-marketing.campaigns.index') }}" class="ui-button-secondary">Cancel</a>
        </div>
    </form>
</x-app-layout>
