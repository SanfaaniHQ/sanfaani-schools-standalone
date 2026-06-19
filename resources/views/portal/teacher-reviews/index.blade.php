<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            :title="__('ui.teacher_reviews')"
            :description="__('ui.teacher_reviews_intro')"
        />
    </x-slot>

    @php
        $ratingOptions = [
            5 => __('ui.rating_excellent'),
            4 => __('ui.rating_good'),
            3 => __('ui.rating_average'),
            2 => __('ui.rating_needs_improvement'),
            1 => __('ui.rating_poor'),
        ];
    @endphp

    <div class="py-6">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="lg:col-span-1">
                <x-ui.form-section :title="__('ui.submit_review')" :description="__('ui.submit_review_intro')">
                    @if (session('success'))
                        <x-ui.alert tone="success">{{ session('success') }}</x-ui.alert>
                    @endif

                    @if ($errors->any())
                        <x-ui.alert tone="danger">{{ $errors->first() }}</x-ui.alert>
                    @endif

                    <form method="POST" action="{{ route('portal.teacher-reviews.store') }}" class="space-y-4">
                        @csrf

                        <label class="block space-y-1.5 text-sm">
                            <span class="block font-medium text-text-primary">{{ __('ui.teacher') }}</span>
                            <select name="teacher_user_id" class="ui-input" required>
                                <option value="">{{ __('ui.select_teacher') }}</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" @selected((string) old('teacher_user_id') === (string) $teacher->id)>{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        @if ($children->isNotEmpty())
                            <label class="block space-y-1.5 text-sm">
                                <span class="block font-medium text-text-primary">{{ __('ui.child') }}</span>
                                <select name="student_id" class="ui-input">
                                    <option value="">{{ __('ui.general_review') }}</option>
                                    @foreach ($children as $child)
                                        <option value="{{ $child->id }}" @selected((string) old('student_id') === (string) $child->id)>{{ $child->fullName() }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @elseif ($student)
                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                        @endif

                        <label class="block space-y-1.5 text-sm">
                            <span class="block font-medium text-text-primary">{{ __('ui.rating') }}</span>
                            <select name="rating" class="ui-input" required>
                                @foreach ($ratingOptions as $rating => $label)
                                    <option value="{{ $rating }}" @selected((int) old('rating', 5) === $rating)>{{ $rating }} - {{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div class="space-y-2">
                            <p class="text-sm font-medium text-text-primary">{{ __('ui.category_ratings') }}</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                            @foreach (\App\Models\TeacherReview::CATEGORY_RATINGS as $key => $label)
                                <label class="block space-y-1.5 text-sm">
                                    <span class="block font-medium text-text-primary">{{ __("ui.teacher_review_category_{$key}") }}</span>
                                    <select name="category_ratings[{{ $key }}]" class="ui-input">
                                        <option value="">{{ __('ui.no_rating') }}</option>
                                        @for ($rating = 5; $rating >= 1; $rating--)
                                            <option value="{{ $rating }}" @selected((string) old("category_ratings.$key") === (string) $rating)>{{ $rating }}</option>
                                        @endfor
                                    </select>
                                </label>
                            @endforeach
                            </div>
                        </div>

                        <x-ui.input
                            :label="__('ui.title')"
                            name="title"
                            :value="old('title')"
                        />

                        <label class="block space-y-1.5 text-sm">
                            <span class="block font-medium text-text-primary">{{ __('ui.comment') }}</span>
                            <textarea name="comment" rows="5" class="ui-input">{{ old('comment') }}</textarea>
                        </label>

                        <x-ui.button type="submit" class="w-full">
                            {{ __('ui.submit_teacher_review') }}
                        </x-ui.button>
                    </form>
                </x-ui.form-section>
            </div>

            <div class="lg:col-span-2">
                <x-ui.table-card :title="__('ui.my_reviews')">
                    @if ($reviews->isEmpty())
                        <div class="p-5">
                            <x-ui.empty-state
                                :title="__('ui.no_teacher_reviews')"
                                :body="__('ui.no_teacher_reviews_help')"
                            />
                        </div>
                    @else
                        <div class="divide-y">
                            @foreach ($reviews as $review)
                                <div class="p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <h4 class="font-semibold text-text-primary">{{ $review->teacher?->name ?? __('ui.teacher') }}</h4>
                                            <p class="mt-1 text-sm text-text-secondary">
                                                {{ __('ui.review_rating') }}: {{ $review->rating }}/5
                                                @if ($review->student)
                                                    - {{ __('ui.review_student') }}: {{ $review->student->fullName() }}
                                                @endif
                                            </p>
                                            @if ($review->title)
                                                <p class="mt-2 font-medium text-text-primary">{{ $review->title }}</p>
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
                                        </div>

                                        <x-ui.badge :status="$review->statusLabel()" />
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
    </div>
</x-app-layout>
