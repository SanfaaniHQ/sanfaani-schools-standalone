<x-app-layout>
    <x-slot name="header">
        @php
            $resolvedBranding = app(\App\Services\Branding\BrandingService::class)->forSchool($school);
            $dashboardTitle = match ($roleContext) {
                'teacher' => 'Teacher Dashboard',
                'result_officer' => 'Result Officer Dashboard',
                default => data_get($resolvedBranding, 'dashboard_heading', 'School Dashboard'),
            };
        @endphp
        <x-ui.page-header :title="$dashboardTitle" :description="$school->name">
            @if($inSupportMode ?? false)
                <x-ui.badge tone="warning">
                    <svg class="me-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                    </svg>
                    Support access active: acting as {{ ucfirst(str_replace('_', ' ', $roleContext)) }}
                </x-ui.badge>
            @endif
        </x-ui.page-header>
    </x-slot>

    <div class="mb-6">
        <x-onboarding-progress-widget />
    </div>

    @if($roleContext === 'teacher')
        @include('school._teacher-dashboard')
    @elseif($roleContext === 'result_officer')
        @include('school._result-officer-dashboard')
    @else
        @include('school._school-admin-dashboard')
    @endif
</x-app-layout>
