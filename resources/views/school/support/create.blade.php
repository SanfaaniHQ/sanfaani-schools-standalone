<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">New Support Request</h2>
            <p class="mt-1 text-sm text-gray-500">Share the issue clearly so support can help quickly.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('school.support.store') }}" class="space-y-6 rounded-2xl bg-white p-6 shadow-sm">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Subject</label>
                    <input name="subject" value="{{ old('subject') }}" class="mt-1 block w-full rounded-xl border-gray-300">
                </div>
                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category" class="mt-1 block w-full rounded-xl border-gray-300">
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" @selected(old('category') === $category)>{{ ucfirst(str_replace('_', ' ', $category)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Priority</label>
                        <select name="priority" class="mt-1 block w-full rounded-xl border-gray-300">
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority }}" @selected(old('priority', 'normal') === $priority)>{{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @if ($canDirectEscalate)
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Route To</label>
                            <select name="route_to" class="mt-1 block w-full rounded-xl border-gray-300">
                                <option value="school_admin" @selected(old('route_to', $role === 'school_admin' ? 'super_admin' : 'school_admin') === 'school_admin')>School Admin</option>
                                <option value="super_admin" @selected(old('route_to', $role === 'school_admin' ? 'super_admin' : 'school_admin') === 'super_admin')>Super Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Escalation Reason</label>
                            <input name="escalation_reason" value="{{ old('escalation_reason') }}" class="mt-1 block w-full rounded-xl border-gray-300">
                        </div>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea name="message" rows="6" class="mt-1 block w-full rounded-xl border-gray-300">{{ old('message') }}</textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('school.support.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Cancel</a>
                    <button class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
