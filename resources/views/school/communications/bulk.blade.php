<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">School / Communication / Bulk</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Bulk Communication</h2>
                <p class="mt-1 text-sm text-gray-500">Send chunked communication by class, arm, session, result status, and staff cohorts.</p>
            </div>
            <a href="{{ route('school.communications.history') }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">History</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('school.communications.bulk.send') }}" class="space-y-5 rounded-2xl bg-white p-6 shadow-sm">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div><label class="block text-sm font-medium text-gray-700">Audience</label><select name="audience" class="mt-1 block w-full rounded-xl border-gray-300"><option value="class">Class</option><option value="arm">Arm</option><option value="session">Session</option><option value="selected_students">Selected Students</option><option value="teachers">Teachers</option><option value="result_officers">Result Officers</option></select></div>
                    <div><label class="block text-sm font-medium text-gray-700">Class</label><select name="school_class_id" class="mt-1 block w-full rounded-xl border-gray-300"><option value="">Select class</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }} {{ $class->section }}</option>@endforeach</select></div>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <div><label class="block text-sm font-medium text-gray-700">Session</label><select name="academic_session_id" class="mt-1 block w-full rounded-xl border-gray-300"><option value="">Any session</option>@foreach($sessions as $session)<option value="{{ $session->id }}">{{ $session->name }}</option>@endforeach</select></div>
                    <div><label class="block text-sm font-medium text-gray-700">Term</label><select name="term_id" class="mt-1 block w-full rounded-xl border-gray-300"><option value="">Any term</option>@foreach($terms as $term)<option value="{{ $term->id }}">{{ $term->name }}</option>@endforeach</select></div>
                    <div><label class="block text-sm font-medium text-gray-700">Student Status</label><select name="student_status" class="mt-1 block w-full rounded-xl border-gray-300"><option value="">Any status</option><option value="active">Active</option><option value="inactive">Inactive</option><option value="graduated">Graduated</option><option value="transferred">Transferred</option><option value="withdrawn">Withdrawn</option></select></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Published Result Status</label><select name="published_result_status" class="mt-1 block w-full rounded-xl border-gray-300"><option value="">Any</option><option value="published">Published only</option><option value="not_published">Not published only</option></select></div>
                <div><label class="block text-sm font-medium text-gray-700">Type</label><select name="type" class="mt-1 block w-full rounded-xl border-gray-300"><option value="result_notification">Result Notification</option><option value="report_card">Report Card</option><option value="scratch_card">Scratch Card</option><option value="payment_reminder">Payment Reminder</option><option value="attendance_warning">Attendance Warning</option><option value="custom_message">Custom Message</option></select></div>
                <div><label class="block text-sm font-medium text-gray-700">Subject</label><input name="subject" class="mt-1 block w-full rounded-xl border-gray-300"></div>
                <div><label class="block text-sm font-medium text-gray-700">Message</label><textarea name="message" rows="7" class="mt-1 block w-full rounded-xl border-gray-300"></textarea></div>
                <div class="flex justify-end"><button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white">Send Bulk Email</button></div>
            </form>
        </div>
    </div>
</x-app-layout>
