<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $material->title }}</h2>
                    <x-ui.badge :status="$material->status" />
                    <x-ui.badge tone="outline">{{ str($material->type)->title() }}</x-ui.badge>
                </div>
                <p class="mt-1 text-sm text-text-secondary">{{ $material->classroom?->title }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.lms.classrooms.show', $material->classroom) }}" class="ui-button-secondary">Back to Classroom</a>
                @if ($canManage && $material->status !== \App\Models\LmsMaterial::STATUS_PUBLISHED)
                    <form method="POST" action="{{ route('school.lms.materials.publish', $material) }}">
                        @csrf
                        <button class="ui-button-primary">Publish</button>
                    </form>
                @endif
                @if ($canManage && $material->status === \App\Models\LmsMaterial::STATUS_PUBLISHED)
                    <form method="POST" action="{{ route('school.lms.materials.unpublish', $material) }}">
                        @csrf
                        <button class="ui-button-secondary">Unpublish</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success" :body="session('success')" />
        @endif

        @if ($errors->any())
            <x-ui.alert tone="danger" body="Review the LMS material fields and try again." />
        @endif

        <section class="grid gap-4 lg:grid-cols-[1fr_24rem]">
            <x-ui.panel title="Material">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <p class="text-xs uppercase tracking-normal text-text-tertiary">Class</p>
                        <p class="mt-1 text-sm font-semibold text-text-primary">{{ $material->classroom?->schoolClass?->name }} {{ $material->classroom?->schoolClass?->section }}</p>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <p class="text-xs uppercase tracking-normal text-text-tertiary">Subject</p>
                        <p class="mt-1 text-sm font-semibold text-text-primary">{{ $material->classroom?->subject?->name }}</p>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <p class="text-xs uppercase tracking-normal text-text-tertiary">Topic</p>
                        <p class="mt-1 text-sm font-semibold text-text-primary">{{ $material->topic?->title ?? 'No topic' }}</p>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <p class="text-xs uppercase tracking-normal text-text-tertiary">Published</p>
                        <p class="mt-1 text-sm font-semibold text-text-primary">{{ $material->published_at?->format('d M Y H:i') ?? 'Not published' }}</p>
                    </div>
                </div>

                <div class="prose mt-5 max-w-none text-sm leading-7 text-text-secondary">
                    {!! nl2br(e($material->body ?: 'No lesson body has been added yet.')) !!}
                </div>
            </x-ui.panel>

            <x-ui.panel title="Resources" description="Private files are streamed through authorization.">
                @if ($canManage)
                    <form method="POST" action="{{ route('school.lms.resources.store', $material) }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input name="resource" type="file" class="block w-full rounded-md border border-border-subtle bg-bg-primary text-sm text-text-secondary file:me-3 file:border-0 file:bg-bg-tertiary file:px-3 file:py-2 file:text-sm file:font-semibold file:text-text-primary" required>
                        @error('resource') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                        <button class="ui-button-secondary w-full">Upload Resource</button>
                    </form>
                    <p class="mt-2 text-xs leading-5 text-text-tertiary">{{ implode(', ', $allowedExtensions) }} up to {{ $maxUploadMb }} MB.</p>
                @endif

                <div class="mt-4 space-y-2">
                    @forelse ($material->resources as $resource)
                        <a href="{{ route('school.lms.resources.download', $resource) }}" class="block rounded-md border border-border-subtle bg-bg-primary p-3 text-sm hover:bg-bg-tertiary">
                            <span class="block font-semibold text-text-primary">{{ $resource->original_name }}</span>
                            <span class="mt-1 block text-xs text-text-secondary">{{ $resource->mime_type }} / {{ number_format($resource->size / 1024, 1) }} KB</span>
                        </a>
                    @empty
                        <p class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm text-text-secondary">No resources uploaded yet.</p>
                    @endforelse
                </div>
            </x-ui.panel>
        </section>

        @include('school.lms.partials.cbt-activities')

        @if ($canManage)
            <x-ui.panel title="Edit Draft Details" description="Editing content does not publish it. Use publish/unpublish controls for visibility.">
                <form method="POST" action="{{ route('school.lms.materials.update', $material) }}" class="space-y-5">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="edit-title" class="block text-sm font-medium text-text-primary">Title</label>
                            <input id="edit-title" name="title" value="{{ old('title', $material->title) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                        </div>
                        <div>
                            <label for="edit-type" class="block text-sm font-medium text-text-primary">Type</label>
                            <select id="edit-type" name="type" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                                @foreach (\App\Models\LmsMaterial::TYPES as $type)
                                    <option value="{{ $type }}" @selected(old('type', $material->type) === $type)>{{ str($type)->title() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="edit-topic" class="block text-sm font-medium text-text-primary">Topic</label>
                        <select id="edit-topic" name="lms_topic_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                            <option value="">No topic</option>
                            @foreach ($material->classroom?->topics()->orderBy('sort_order')->orderBy('id')->get() ?? collect() as $topic)
                                <option value="{{ $topic->id }}" @selected((int) old('lms_topic_id', $material->lms_topic_id) === (int) $topic->id)>{{ $topic->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="edit-body" class="block text-sm font-medium text-text-primary">Body</label>
                        <textarea id="edit-body" name="body" rows="8" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('body', $material->body) }}</textarea>
                    </div>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label for="edit-visible-from" class="block text-sm font-medium text-text-primary">Visible From</label>
                            <input id="edit-visible-from" name="visible_from" type="datetime-local" value="{{ old('visible_from', optional($material->visible_from)->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        </div>
                        <div>
                            <label for="edit-visible-until" class="block text-sm font-medium text-text-primary">Visible Until</label>
                            <input id="edit-visible-until" name="visible_until" type="datetime-local" value="{{ old('visible_until', optional($material->visible_until)->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        </div>
                        <div>
                            <label for="edit-due-at" class="block text-sm font-medium text-text-primary">Due At</label>
                            <input id="edit-due-at" name="due_at" type="datetime-local" value="{{ old('due_at', optional($material->due_at)->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2">
                        <button class="ui-button-primary">Save Changes</button>
                    </div>
                </form>
            </x-ui.panel>

            <x-ui.panel tone="warning" title="Archive Material">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm leading-6 text-text-secondary">Archived materials stay in the audit trail and are removed from published visibility.</p>
                    <form method="POST" action="{{ route('school.lms.materials.archive', $material) }}">
                        @csrf
                        <button class="ui-button-danger">Archive</button>
                    </form>
                </div>
            </x-ui.panel>
        @endif

        <x-ui.panel tone="info" title="Stage 15 Boundary">
            <p class="text-sm leading-6 text-text-secondary">
                This material can be an assignment post, but student submissions and grading are deferred. Existing CBT items can be linked here without changing CBT attempt or result rules. Live classes, offline LMS, discussions, analytics, video hosting, and payment-gated content are not implemented here.
            </p>
        </x-ui.panel>
    </div>
</x-app-layout>
