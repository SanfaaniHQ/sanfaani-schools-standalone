<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-900">{{ $policy->exists ? 'Edit' : 'Add' }} Result Access Policy</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ $policy->exists ? route('admin.result-access-policies.update', $policy) : route('admin.result-access-policies.store') }}" class="space-y-6 rounded-2xl bg-white p-6 shadow-sm">
                @csrf
                @if($policy->exists) @method('PUT') @endif
                <div class="grid gap-6 md:grid-cols-2">
                    <div><label class="block text-sm font-medium text-gray-700">School</label><select name="school_id" class="mt-1 block w-full rounded-xl border-gray-300">@foreach($schools as $school)<option value="{{ $school->id }}" @selected(old('school_id', $policy->school_id) == $school->id)>{{ $school->name }}</option>@endforeach</select></div>
                    <div><label class="block text-sm font-medium text-gray-700">Name</label><input name="name" value="{{ old('name', $policy->name) }}" class="mt-1 block w-full rounded-xl border-gray-300"></div>
                    <div><label class="block text-sm font-medium text-gray-700">Access Mode</label><select name="access_mode" class="mt-1 block w-full rounded-xl border-gray-300"><option value="scratch_card" @selected(old('access_mode', $policy->access_mode) === 'scratch_card')>Scratch Card</option><option value="school_paid" @selected(old('access_mode', $policy->access_mode) === 'school_paid')>School Paid</option><option value="parent_paid" @selected(old('access_mode', $policy->access_mode) === 'parent_paid')>Parent Paid</option><option value="hybrid" @selected(old('access_mode', $policy->access_mode) === 'hybrid')>Hybrid</option></select></div>
                    <div><label class="block text-sm font-medium text-gray-700">Status</label><select name="status" class="mt-1 block w-full rounded-xl border-gray-300"><option value="active" @selected(old('status', $policy->status) === 'active')>Active</option><option value="inactive" @selected(old('status', $policy->status) === 'inactive')>Inactive</option><option value="archived" @selected(old('status', $policy->status) === 'archived')>Archived</option></select></div>
                    <div><label class="block text-sm font-medium text-gray-700">Starts At</label><input type="date" name="starts_at" value="{{ old('starts_at', $policy->starts_at?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-xl border-gray-300"></div>
                    <div><label class="block text-sm font-medium text-gray-700">Ends At</label><input type="date" name="ends_at" value="{{ old('ends_at', $policy->ends_at?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-xl border-gray-300"></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Notes</label><textarea name="notes" rows="3" class="mt-1 block w-full rounded-xl border-gray-300">{{ old('notes', $policy->notes) }}</textarea></div>

                <div class="rounded-2xl bg-gray-50 p-4">
                    <h3 class="text-base font-semibold text-gray-900">Default Rule</h3>
                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <select name="academic_session_id" class="rounded-xl border-gray-300"><option value="">Any session</option>@foreach($sessions as $session)<option value="{{ $session->id }}" @selected(old('academic_session_id', $rule->academic_session_id) == $session->id)>{{ $session->name }}</option>@endforeach</select>
                        <select name="term_id" class="rounded-xl border-gray-300"><option value="">Any term</option>@foreach($terms as $term)<option value="{{ $term->id }}" @selected(old('term_id', $rule->term_id) == $term->id)>{{ $term->name }}</option>@endforeach</select>
                        <select name="result_type" class="rounded-xl border-gray-300"><option value="term_result">Term Result</option><option disabled>Assessment/Test - Available on selected plans</option><option disabled>CBT - Available on selected plans</option></select>
                        <select name="access_scope" class="rounded-xl border-gray-300"><option value="term" @selected(old('access_scope', $rule->access_scope) === 'term')>Term</option><option value="session" @selected(old('access_scope', $rule->access_scope) === 'session')>Session</option><option value="year" @selected(old('access_scope', $rule->access_scope) === 'year')>Year</option><option value="custom" @selected(old('access_scope', $rule->access_scope) === 'custom')>Custom</option></select>
                        <input type="number" name="max_access_per_student" value="{{ old('max_access_per_student', $rule->max_access_per_student) }}" placeholder="Max access/student" class="rounded-xl border-gray-300">
                        <input type="number" name="max_access_per_card" value="{{ old('max_access_per_card', $rule->max_access_per_card) }}" placeholder="Max access/card" class="rounded-xl border-gray-300">
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-5">
                        @foreach(['requires_scratch_card' => 'Requires scratch card', 'allows_parent_payment' => 'Parent payment', 'allows_school_paid_access' => 'School paid', 'allows_pdf_download' => 'PDF download'] as $field => $label)
                            <label class="rounded-xl bg-white p-3 text-sm"><input type="hidden" name="{{ $field }}" value="0"><input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $rule->{$field})) class="mr-2 rounded border-gray-300">{{ $label }}</label>
                        @endforeach
                        <select name="rule_status" class="rounded-xl border-gray-300"><option value="active" @selected(old('rule_status', $rule->status) === 'active')>Active rule</option><option value="inactive" @selected(old('rule_status', $rule->status) === 'inactive')>Inactive rule</option></select>
                    </div>
                </div>
                @if ($errors->any()) <div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">Please fix the highlighted fields.</div> @endif
                <div class="flex justify-end gap-3"><a href="{{ route('admin.result-access-policies.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm">Cancel</a><button data-loading-text="Saving..." class="rounded-xl bg-gray-900 px-4 py-2 text-sm text-white">Save Policy</button></div>
            </form>
        </div>
    </div>
</x-app-layout>
