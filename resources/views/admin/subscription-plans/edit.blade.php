<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">Edit Subscription Plan</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.subscription-plans.update', $plan) }}" class="space-y-6 rounded-2xl bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')
                @include('admin.subscription-plans.form-fields')
            </form>
        </div>
    </div>
</x-app-layout>
