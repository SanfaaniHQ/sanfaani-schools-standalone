<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-text-primary">
                @if($roleContext === 'teacher')
                    Teacher Command Center
                @elseif($roleContext === 'result_officer')
                    Result Officer Command Center
                @else
                    School Operations Command Center
                @endif
            </h2>

            <p class="mt-1 text-sm text-text-secondary">
                {{ $school->name }}
            </p>

            @if($inSupportMode ?? false)
                <div class="mt-2 inline-flex items-center rounded-md bg-amber-500/10 px-2 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:text-amber-300">
                    <svg class="me-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                    </svg>
                    Support access active: acting as {{ ucfirst(str_replace('_', ' ', $roleContext)) }}
                </div>
            @endif
        </div>
    </x-slot>

    @if($roleContext === 'teacher')
        @include('school._teacher-dashboard')
    @elseif($roleContext === 'result_officer')
        @include('school._result-officer-dashboard')
    @else
        @include('school._school-admin-dashboard')
    @endif
</x-app-layout>
