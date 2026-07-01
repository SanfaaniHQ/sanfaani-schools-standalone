<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            :title="__('ui.teacher_review_moderation')"
            :description="__('ui.teacher_review_moderation_intro')"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
            @endif

            <form method="GET" class="rounded-md border border-border-subtle bg-bg-secondary p-4 shadow-sm">
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="space-y-1.5 text-sm">
                        <span class="block font-medium text-text-primary">{{ __('ui.status') }}</span>
                        <select name="status" class="ui-input">
                            <option value="">{{ __('ui.all_statuses') }}</option>
                            <option value="pending" @selected($filters['status'] === 'pending')>{{ __('ui.pending') }}</option>
                            <option value="approved" @selected($filters['status'] === 'approved')>{{ __('ui.approved') }}</option>
                            <option value="rejected" @selected($filters['status'] === 'rejected')>{{ __('ui.rejected') }}</option>
                        </select>
                    </label>

                    <div class="flex items-end">
                        <x-ui.button type="submit">{{ __('ui.filter') }}</x-ui.button>
                    </div>
                </div>
            </form>

            <x-ui.table-card :title="__('ui.teacher_reviews')">
                @if ($reviews->isEmpty())
                    <div class="p-5">
                        <x-ui.empty-state
                            :title="__('ui.no_teacher_review_found')"
                            :body="__('ui.no_teacher_review_found_help')"
                        />
                    </div>
                @else
                    <div class="divide-y">
                        @foreach ($reviews as $review)
                            <div class="p-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-text-primary">{{ $review->teacher?->name ?? __('ui.teacher') }}</h3>
                                        <p class="mt-1 text-sm text-text-secondary">
                                            {{ __('ui.submitted_by') }} {{ $review->reviewer?->name ?? __('ui.user') }}
                                            - {{ __('ui.review_rating') }} {{ $review->rating }}/5
                                            @if ($review->student)
                                                - {{ __('ui.review_student') }}: {{ $review->student->fullName() }}
                                            @endif
                                        </p>

                                        @if ($review->title)
                                            <p class="mt-3 font-medium text-text-primary">{{ $review->title }}</p>
                                        @endif

                                        @if ($review->comment)
                                            <p class="mt-1 text-sm text-text-secondary">{{ $review->comment }}</p>
                                        @endif

                                        @if ($review->categoryRatings() !== [])
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach ($review->categoryRatings() as $key => $rating)
                                                    <x-ui.badge tone="outline">
                                                        {{ __("ui.teacher_review_category_{$key}") }}: {{ $rating }}/5
                                                    </x-ui.badge>
                                                @endforeach
                                            </div>
                                        @endif

                                        <x-ui.badge class="mt-3" :status="$review->statusLabel()" />
                                    </div>

                                    @if ($review->status === 'pending')
                                        <div class="w-full max-w-sm space-y-3">
                                            <form method="POST" action="{{ route('school.teacher-reviews.approve', ['teacherReview' => $review->id]) }}" class="space-y-2">
                                                @csrf
                                                <input type="text" name="moderation_note" placeholder="{{ __('ui.approval_note') }}" class="ui-input">
                                                <x-ui.button type="submit" variant="success" size="sm">
                                                    {{ __('ui.approve') }}
                                                </x-ui.button>
                                            </form>

                                            <form method="POST" action="{{ route('school.teacher-reviews.reject', ['teacherReview' => $review->id]) }}" class="space-y-2">
                                                @csrf
                                                <input type="text" name="moderation_note" placeholder="{{ __('ui.rejection_note') }}" class="ui-input">
                                                <x-ui.button type="submit" variant="danger" size="sm">
                                                    {{ __('ui.reject') }}
                                                </x-ui.button>
                                            </form>
                                        </div>
                                    @else
                                        <p class="text-xs text-text-secondary">
                                            {{ __('ui.reviewed_by') }} {{ $review->moderator?->name ?? __('ui.school_team') }}
                                            @if ($review->reviewed_at)
                                                {{ __('ui.on_date', ['date' => $review->reviewed_at->format('M d, Y')]) }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <x-slot name="footer">
                        {{ $reviews->links() }}
                    </x-slot>
                @endif
            </x-ui.table-card>
        </div>
    </div>
</x-app-layout>
