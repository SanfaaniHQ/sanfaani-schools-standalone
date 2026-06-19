<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="School Support Center"
            description="Create an internal school support request with enough detail for the right team member to respond."
        />
    </x-slot>

    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('school.support.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <x-ui.form-section
                title="Request details"
                description="Keep the subject short, then choose the category and priority used for routing inside the school."
            >
                <div>
                    <label for="subject" class="block text-sm font-medium text-text-primary">Subject</label>
                    <input id="subject" name="subject" value="{{ old('subject') }}" class="ui-input mt-2 @error('subject') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                    @error('subject')
                        <p class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="category" class="block text-sm font-medium text-text-primary">Category</label>
                        <select id="category" name="category" class="ui-input mt-2 @error('category') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" @selected(old('category') === $category)>{{ ucfirst(str_replace('_', ' ', $category)) }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-text-primary">Priority</label>
                        <select id="priority" name="priority" class="ui-input mt-2 @error('priority') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority }}" @selected(old('priority', 'normal') === $priority)>{{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                        @error('priority')
                            <p class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if ($canDirectEscalate)
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="route_to" class="block text-sm font-medium text-text-primary">Route to</label>
                            <select id="route_to" name="route_to" class="ui-input mt-2 @error('route_to') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                                <option value="school_admin" @selected(old('route_to', 'school_admin') === 'school_admin')>School Admin</option>
                                <option value="super_admin" @selected(old('route_to') === 'super_admin')>Installation Admin</option>
                            </select>
                            @error('route_to')
                                <p class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="escalation_reason" class="block text-sm font-medium text-text-primary">Escalation reason</label>
                            <input id="escalation_reason" name="escalation_reason" value="{{ old('escalation_reason') }}" class="ui-input mt-2 @error('escalation_reason') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">
                            @error('escalation_reason')
                                <p class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endif
            </x-ui.form-section>

            <x-ui.form-section
                title="Message and attachments"
                description="Add the full request and optional files. Attachments stay scoped to this school."
            >
                <div>
                    <label for="message" class="block text-sm font-medium text-text-primary">Message</label>
                    <textarea id="message" name="message" rows="6" class="ui-input mt-2 @error('message') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="attachments" class="block text-sm font-medium text-text-primary">Attachments</label>
                    <input id="attachments" type="file" name="attachments[]" multiple class="mt-2 block w-full rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-sm text-text-secondary file:me-3 file:rounded-md file:border-0 file:bg-bg-tertiary file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-text-primary">
                    <p class="mt-2 text-xs text-text-secondary">Up to 3 files, 5 MB each.</p>
                    @error('attachments')
                        <p class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                    @error('attachments.*')
                        <p class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
            </x-ui.form-section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ route('school.support.index') }}" class="ui-button-secondary inline-flex min-h-10 items-center justify-center">
                    Cancel
                </a>
                <button type="submit" class="ui-button-primary min-h-10" data-loading-text="Submitting...">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
