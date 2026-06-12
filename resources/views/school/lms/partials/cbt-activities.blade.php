@php
    $cbtActivities = $cbtActivities ?? collect();
    $cbtActivityManagement = collect($cbtActivityManagement ?? []);
    $eligibleCbtExams = $eligibleCbtExams ?? collect();
    $canManageCbtLinks = (bool) ($canManageCbtLinks ?? false);
    $cbtAttachAction = $cbtAttachAction ?? null;
@endphp

<x-ui.panel title="CBT Activities" description="CBT integration only links existing CBT items; CBT attempt and result rules remain unchanged.">
    <div class="space-y-3">
        @forelse ($cbtActivities as $activity)
            @php
                $exam = $activity->exam;
                $examOpen = $exam?->isOpenForEntry();
                $canManageThisCbtActivity = (bool) $cbtActivityManagement->get($activity->id, false);
            @endphp
            <div class="rounded-md border border-border-subtle bg-bg-primary p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base font-semibold text-text-primary">{{ $activity->title ?: $exam?->title }}</h3>
                            @if ($exam)
                                <x-ui.badge :status="$exam->status" />
                                <x-ui.badge tone="outline">{{ str($exam->exam_type)->replace('_', ' ')->title() }}</x-ui.badge>
                            @endif
                        </div>
                        @if ($activity->description)
                            <p class="mt-2 text-sm leading-6 text-text-secondary">{{ $activity->description }}</p>
                        @endif
                        <p class="mt-2 text-xs leading-5 text-text-tertiary">
                            {{ $exam?->schoolClass?->name ?? 'School-wide class scope' }}
                            / {{ $exam?->subject?->name ?? 'General subject scope' }}
                            / {{ $exam?->academicSession?->name ?? 'Any session' }}
                            / {{ $exam?->term?->name ?? 'Any term' }}
                        </p>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-2">
                        @if ($exam)
                            @if ($examOpen)
                                <a href="{{ route('public.cbt.entry', ['school' => $school->slug, 'exam' => $exam->slug]) }}" target="_blank" class="ui-button-secondary">Open CBT Entry</a>
                            @endif
                            @if ($canManageThisCbtActivity)
                                @schoolFeature('cbt.manage', 'cbt.question_bank')
                                    <a href="{{ route('school.cbt.exams.show', $exam) }}" class="ui-button-secondary">Manage CBT</a>
                                @endschoolFeature
                            @endif
                        @endif
                        @if ($canManageThisCbtActivity)
                            <form method="POST" action="{{ route('school.lms.cbt-links.destroy', $activity) }}">
                                @csrf
                                @method('DELETE')
                                <button class="ui-button-danger">Unlink</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <x-ui.empty-state
                title="No CBT activities linked"
                body="Attach an existing CBT exam, quiz, or assessment when this LMS space should point learners to the assessment engine."
            />
        @endforelse
    </div>

    <p class="mt-4 rounded-md border border-border-subtle bg-bg-primary p-3 text-xs leading-5 text-text-tertiary">
        LMS links do not expose raw CBT questions, answers, scores, access codes, tokens, or private attempt payloads.
    </p>

    @if ($canManageCbtLinks && $cbtAttachAction)
        <form method="POST" action="{{ $cbtAttachAction }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <label for="cbt-exam-link-{{ md5($cbtAttachAction) }}" class="block text-sm font-medium text-text-primary">Existing CBT item</label>
                <select id="cbt-exam-link-{{ md5($cbtAttachAction) }}" name="cbt_exam_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" required>
                    <option value="">Select CBT exam, quiz, or assessment</option>
                    @foreach ($eligibleCbtExams as $exam)
                        <option value="{{ $exam->id }}" @selected((int) old('cbt_exam_id') === (int) $exam->id)>
                            {{ $exam->title }} / {{ $exam->schoolClass?->name ?? 'School-wide' }} / {{ $exam->subject?->name ?? 'General' }} / {{ str($exam->status)->title() }}
                        </option>
                    @endforeach
                </select>
                @error('cbt_exam_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="cbt-title-{{ md5($cbtAttachAction) }}" class="block text-sm font-medium text-text-primary">Display title</label>
                    <input id="cbt-title-{{ md5($cbtAttachAction) }}" name="title" value="{{ old('title') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" placeholder="Use CBT title if blank">
                    @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="cbt-description-{{ md5($cbtAttachAction) }}" class="block text-sm font-medium text-text-primary">Short note</label>
                    <input id="cbt-description-{{ md5($cbtAttachAction) }}" name="description" value="{{ old('description') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900" placeholder="Optional learner-facing note">
                    @error('description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <button class="ui-button-primary" @disabled($eligibleCbtExams->isEmpty())>Link CBT Activity</button>
            @if ($eligibleCbtExams->isEmpty())
                <p class="text-xs leading-5 text-text-tertiary">No eligible CBT item is available for this LMS scope.</p>
            @endif
        </form>
    @endif
</x-ui.panel>
