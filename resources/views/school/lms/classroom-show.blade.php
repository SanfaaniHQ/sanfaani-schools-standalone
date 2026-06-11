<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-text-primary">{{ $classroom->title }}</h2>
                <p class="mt-1 text-sm text-text-secondary">
                    {{ $classroom->schoolClass?->name }} {{ $classroom->schoolClass?->section }} / {{ $classroom->subject?->name }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('school.lms.index') }}" class="ui-button-secondary">LMS Home</a>
                <a href="{{ route('school.lms.materials.create', $classroom) }}" class="ui-button-primary">New Material</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert tone="success" :body="session('success')" />
        @endif

        @if ($errors->any())
            <x-ui.alert tone="danger" body="Review the classroom fields and try again." />
        @endif

        <section class="grid gap-4 lg:grid-cols-[1fr_24rem]">
            <x-ui.panel title="Classroom Scope" description="All LMS work here remains tied to existing school academic records.">
                <dl class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <dt class="text-xs uppercase tracking-normal text-text-tertiary">Class</dt>
                        <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $classroom->schoolClass?->name }} {{ $classroom->schoolClass?->section }}</dd>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <dt class="text-xs uppercase tracking-normal text-text-tertiary">Subject</dt>
                        <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $classroom->subject?->name }}</dd>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <dt class="text-xs uppercase tracking-normal text-text-tertiary">Session</dt>
                        <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $classroom->academicSession?->name ?? 'Any session' }}</dd>
                    </div>
                    <div class="rounded-md border border-border-subtle bg-bg-primary p-3">
                        <dt class="text-xs uppercase tracking-normal text-text-tertiary">Term</dt>
                        <dd class="mt-1 text-sm font-semibold text-text-primary">{{ $classroom->term?->name ?? 'Any term' }}</dd>
                    </div>
                </dl>
                @if ($classroom->description)
                    <p class="mt-4 text-sm leading-6 text-text-secondary">{{ $classroom->description }}</p>
                @endif
            </x-ui.panel>

            <x-ui.panel title="Topics" description="Optional modules for grouping materials.">
                <form method="POST" action="{{ route('school.lms.topics.store', $classroom) }}" class="space-y-3">
                    @csrf
                    <input name="title" placeholder="Topic title" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                    <input name="sort_order" type="number" min="0" max="9999" placeholder="Sort order" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900">
                    <button class="ui-button-secondary w-full">Add Topic</button>
                </form>
                <div class="mt-4 space-y-2">
                    @forelse ($classroom->topics as $topic)
                        <div class="rounded-md border border-border-subtle bg-bg-primary px-3 py-2 text-sm">
                            <span class="font-semibold text-text-primary">{{ $topic->title }}</span>
                            <span class="float-right text-xs text-text-tertiary">#{{ $topic->sort_order }}</span>
                        </div>
                    @empty
                        <p class="rounded-md border border-border-subtle bg-bg-primary p-3 text-sm text-text-secondary">No topics yet.</p>
                    @endforelse
                </div>
            </x-ui.panel>
        </section>

        <x-ui.panel title="Materials" description="Lessons, notes, resources, and assignment posts. Assignment submissions and grading are not part of this stage.">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border-subtle text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-normal text-text-tertiary">
                            <th class="px-3 py-2">Title</th>
                            <th class="px-3 py-2">Type</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2">Topic</th>
                            <th class="px-3 py-2">Resources</th>
                            <th class="px-3 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @forelse ($materials as $material)
                            <tr>
                                <td class="px-3 py-3 font-semibold text-text-primary">{{ $material->title }}</td>
                                <td class="px-3 py-3 text-text-secondary">{{ str($material->type)->title() }}</td>
                                <td class="px-3 py-3"><x-ui.badge :status="$material->status" /></td>
                                <td class="px-3 py-3 text-text-secondary">{{ $material->topic?->title ?? '-' }}</td>
                                <td class="px-3 py-3 text-text-secondary">{{ $material->resources->count() }}</td>
                                <td class="px-3 py-3 text-right">
                                    <a href="{{ route('school.lms.materials.show', $material) }}" class="ui-button-secondary">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8">
                                    <x-ui.empty-state
                                        title="No materials yet"
                                        body="Create a draft lesson, note, resource, or assignment post for this class and subject."
                                        :action-href="route('school.lms.materials.create', $classroom)"
                                        action-label="New Material"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $materials->links() }}</div>
        </x-ui.panel>

        <x-ui.panel tone="info" title="Resource Security">
            <p class="text-sm leading-6 text-text-secondary">
                Files are stored under private local LMS storage and downloaded only after authorization. Allowed resources: {{ implode(', ', $allowedExtensions) }}. Maximum size: {{ $maxUploadMb }} MB. Raw storage paths are not shown.
            </p>
        </x-ui.panel>
    </div>
</x-app-layout>
