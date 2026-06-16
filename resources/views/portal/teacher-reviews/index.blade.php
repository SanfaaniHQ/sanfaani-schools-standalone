<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Teacher Reviews</h2>
            <p class="mt-1 text-sm text-gray-500">
                Submit feedback about teacher support and classroom experience.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="lg:col-span-1">
                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Submit Review</h3>

                    @if ($errors->any())
                        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('portal.teacher-reviews.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-gray-700">Teacher</span>
                            <select name="teacher_user_id" class="w-full rounded-lg border-gray-300 text-sm" required>
                                <option value="">Select teacher</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        @if ($children->isNotEmpty())
                            <label class="block text-sm">
                                <span class="mb-1 block font-medium text-gray-700">Child</span>
                                <select name="student_id" class="w-full rounded-lg border-gray-300 text-sm">
                                    <option value="">General review</option>
                                    @foreach ($children as $child)
                                        <option value="{{ $child->id }}">{{ $child->fullName() }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @elseif ($student)
                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                        @endif

                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-gray-700">Rating</span>
                            <select name="rating" class="w-full rounded-lg border-gray-300 text-sm" required>
                                <option value="5">5  Excellent</option>
                                <option value="4">4  Good</option>
                                <option value="3">3  Average</option>
                                <option value="2">2  Needs improvement</option>
                                <option value="1">1  Poor</option>
                            </select>
                        </label>

                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-gray-700">Title</span>
                            <input type="text" name="title" class="w-full rounded-lg border-gray-300 text-sm">
                        </label>

                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-gray-700">Comment</span>
                            <textarea name="comment" rows="5" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                        </label>

                        <button type="submit" class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                            Submit Review
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="rounded-2xl border bg-white shadow-sm">
                    <div class="border-b px-5 py-4">
                        <h3 class="text-base font-semibold text-gray-900">My Reviews</h3>
                    </div>

                    @if ($reviews->isEmpty())
                        <div class="p-8 text-center text-sm text-gray-500">
                            No teacher review submitted yet.
                        </div>
                    @else
                        <div class="divide-y">
                            @foreach ($reviews as $review)
                                <div class="p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $review->teacher?->name ?? 'Teacher' }}</h4>
                                            <p class="mt-1 text-sm text-gray-500">
                                                Rating: {{ $review->rating }}/5
                                                @if ($review->student)
                                                     Student: {{ $review->student->fullName() }}
                                                @endif
                                            </p>
                                            @if ($review->title)
                                                <p class="mt-2 font-medium text-gray-900">{{ $review->title }}</p>
                                            @endif
                                            @if ($review->comment)
                                                <p class="mt-1 text-sm text-gray-600">{{ $review->comment }}</p>
                                            @endif
                                        </div>

                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase text-gray-700">
                                            {{ $review->statusLabel() }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t px-5 py-3">
                            {{ $reviews->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
