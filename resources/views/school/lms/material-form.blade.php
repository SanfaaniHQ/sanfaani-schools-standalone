<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">New LMS Material</h2>
                <p class="mt-1 text-sm text-text-secondary">{{ $classroom->title }}</p>
            </div>
            <a href="{{ route('school.lms.classrooms.show', $classroom) }}" class="ui-button-secondary">Back to Classroom</a>
        </div>
    </x-slot>

    <x-ui.panel title="Material Details" description="Materials start as drafts. Publish when the content is ready for authorized viewers.">
        <form method="POST" action="{{ route('school.lms.materials.store', $classroom) }}" class="space-y-5">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="material-title" class="block text-sm font-medium text-text-primary">Title</label>
                    <input id="material-title" name="title" value="{{ old('title') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                    @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="material-type" class="block text-sm font-medium text-text-primary">Type</label>
                    <select id="material-type" name="type" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                        @foreach ($types as $type)
                            <option value="{{ $type }}" @selected(old('type', $material->type) === $type)>{{ str($type)->title() }}</option>
                        @endforeach
                    </select>
                    @error('type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="material-topic" class="block text-sm font-medium text-text-primary">Topic</label>
                <select id="material-topic" name="lms_topic_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <option value="">No topic</option>
                    @foreach ($classroom->topics as $topic)
                        <option value="{{ $topic->id }}" @selected((int) old('lms_topic_id') === (int) $topic->id)>{{ $topic->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="material-body" class="block text-sm font-medium text-text-primary">Body</label>
                <textarea id="material-body" name="body" rows="10" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('body') }}</textarea>
                @error('body') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label for="visible-from" class="block text-sm font-medium text-text-primary">Visible From</label>
                    <input id="visible-from" name="visible_from" type="datetime-local" value="{{ old('visible_from') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                </div>
                <div>
                    <label for="visible-until" class="block text-sm font-medium text-text-primary">Visible Until</label>
                    <input id="visible-until" name="visible_until" type="datetime-local" value="{{ old('visible_until') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                </div>
                <div>
                    <label for="due-at" class="block text-sm font-medium text-text-primary">Due At</label>
                    <input id="due-at" name="due_at" type="datetime-local" value="{{ old('due_at') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                </div>
            </div>

            <x-ui.panel tone="info" padding="p-4">
                <p class="text-sm leading-6 text-text-secondary">
                    Assignment type means an assignment post only. Student submissions and grading are deferred. Resources can be uploaded after saving the draft. Allowed resources: {{ implode(', ', $allowedExtensions) }} up to {{ $maxUploadMb }} MB.
                </p>
            </x-ui.panel>

            <div class="flex flex-wrap justify-end gap-2">
                <a href="{{ route('school.lms.classrooms.show', $classroom) }}" class="ui-button-secondary">Cancel</a>
                <button class="ui-button-primary">Save Draft</button>
            </div>
        </form>
    </x-ui.panel>
</x-app-layout>
