<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Teacher Review Moderation</h2>
            <p class="mt-1 text-sm text-gray-500">
                Review, approve, or reject teacher feedback submitted by parents and students.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <form method="GET" class="rounded-2xl border bg-white p-4 shadow-sm">
                <div class="grid gap-4 sm:grid-cols-3">
                    <label class="text-sm">
                        <span class="mb-1 block font-medium text-gray-700">Status</span>
                        <select name="status" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">All statuses</option>
                            <option value="pending" @selected($filters['status'] === 'pending')>Pending</option>
                            <option value="approved" @selected($filters['status'] === 'approved')>Approved</option>
                            <option value="rejected" @selected($filters['status'] === 'rejected')>Rejected</option>
                        </select>
                    </label>

                    <div class="flex items-end">
                        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                            Filter
                        </button>
                    </div>
                </div>
            </form>

            <div class="rounded-2xl border bg-white shadow-sm">
                @if ($reviews->isEmpty())
                    <div class="p-8 text-center text-sm text-gray-500">
                        No teacher review found.
                    </div>
                @else
                    <div class="divide-y">
                        @foreach ($reviews as $review)
                            <div class="p-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $review->teacher?->name ?? 'Teacher' }}</h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Submitted by {{ $review->reviewer?->name ?? 'User' }}
                                             Rating {{ $review->rating }}/5
                                            @if ($review->student)
                                                 Student: {{ $review->student->fullName() }}
                                            @endif
                                        </p>

                                        @if ($review->title)
                                            <p class="mt-3 font-medium text-gray-900">{{ $review->title }}</p>
                                        @endif

                                        @if ($review->comment)
                                            <p class="mt-1 text-sm text-gray-600">{{ $review->comment }}</p>
                                        @endif

                                        <span class="mt-3 inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase text-gray-700">
                                            {{ $review->statusLabel() }}
                                        </span>
                                    </div>

                                    @if ($review->status === 'pending')
                                        <div class="w-full max-w-sm space-y-3">
                                            <form method="POST" action="{{ route('school.teacher-reviews.approve', ['teacherReview' => $review->id]) }}" class="space-y-2">
                                                @csrf
                                                <input type="text" name="moderation_note" placeholder="Approval note" class="w-full rounded-lg border-gray-300 text-sm">
                                                <button type="submit" class="rounded-lg bg-green-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-800">
                                                    Approve
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('school.teacher-reviews.reject', ['teacherReview' => $review->id]) }}" class="space-y-2">
                                                @csrf
                                                <input type="text" name="moderation_note" placeholder="Rejection note" class="w-full rounded-lg border-gray-300 text-sm">
                                                <button type="submit" class="rounded-lg bg-red-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-800">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-500">
                                            Reviewed by {{ $review->moderator?->name ?? 'School team' }}
                                            @if ($review->reviewed_at)
                                                on {{ $review->reviewed_at->format('M d, Y') }}
                                            @endif
                                        </p>
                                    @endif
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
</x-app-layout>
