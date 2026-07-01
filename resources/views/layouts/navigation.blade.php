@php
    $roleContext = app(\App\Services\CurrentSchoolService::class)->roleContext(Auth::user());
    $navBrandName = data_get($schoolBranding ?? null, 'name') ?: ($platformSettings->platform_name ?? config('app.name', 'Sanfaani Schools'));
    $navLogoUrl = data_get($schoolBranding ?? null, 'logo_url') ?: ($platformLogoUrl ?? null);
    $navInitials = data_get($schoolBranding ?? null, 'initials') ?: ($platformInitials ?? 'SS');
    $navColor = data_get($schoolBranding ?? null, 'primary_color') ?: '#047857';
    $behavior = app(\App\Services\System\DeploymentBehaviorService::class);
    $canGroup = fn (string $group) => $behavior->allowsRouteGroup($group, user: Auth::user());
@endphp
<nav x-data="{ open: false }" x-on:keydown.escape.window="open = false" class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 shadow-sm backdrop-blur">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        @if ($navLogoUrl)
                            <img src="{{ $navLogoUrl }}" alt="{{ $navBrandName }} logo" class="block h-9 w-9 rounded-xl border border-slate-200 bg-white object-contain p-1">
                        @else
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl text-xs font-semibold text-white" style="background: {{ $navColor }}">{{ $navInitials }}</span>
                        @endif
                        <span class="hidden max-w-40 truncate text-sm font-semibold text-slate-900 lg:inline">{{ $navBrandName }}</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 lg:-my-px lg:ms-10 lg:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if (Auth::user()->activeSchoolRoles()->count() > 1 || Auth::user()->roles()->count() > 1 || Auth::user()->hasRole('super_admin'))
                        <x-nav-link :href="route('role-context.index')" :active="request()->routeIs('role-context.*')">
                            {{ __('ui.switch_role') }}
                        </x-nav-link>
                    @endif

                    @if (in_array($roleContext, ['school_admin', 'super_admin'], true) || Auth::user()->hasRole('super_admin'))
                        <x-nav-link :href="route('school.feature-control.index')" :active="request()->routeIs('school.feature-control.*')">
                            {{ __('ui.feature_control') }}
                        </x-nav-link>

                        <x-nav-link :href="route('school.role-permissions.index')" :active="request()->routeIs('school.role-permissions.*')">
                            {{ __('ui.roles_permissions') }}
                        </x-nav-link>
                    @endif

                    @if ($roleContext === 'school_admin')
                        <x-nav-link :href="route('school.profile.edit')" :active="request()->routeIs('school.profile.*')">
                            School Profile
                        </x-nav-link>

                        <x-nav-link :href="route('school.staff.index')" :active="request()->routeIs('school.staff.*')">
                            Staff
                        </x-nav-link>

                        <x-nav-link :href="route('school.admission-number-settings.edit')" :active="request()->routeIs('school.admission-number-settings.*')">
                            Admission Numbers
                        </x-nav-link>

                        <x-nav-link :href="route('school.branding.edit')" :active="request()->routeIs('school.branding.*')">
                            Branding
                        </x-nav-link>

                        <x-nav-link :href="route('school.sessions.index')" :active="request()->routeIs('school.sessions.*')">
                            Sessions
                        </x-nav-link>

                        <x-nav-link :href="route('school.terms.index')" :active="request()->routeIs('school.terms.*')">
                            Terms
                        </x-nav-link>

                        @schoolFeature('communication.logs.view')
                            <x-nav-link :href="route('school.communications.index')" :active="request()->routeIs('school.communications.index', 'school.communications.logs*', 'school.communications.templates*')">
                                Communication Center
                            </x-nav-link>
                        @endschoolFeature

                        @schoolFeature('communication.bulk')
                            <x-nav-link :href="route('school.communications.bulk')" :active="request()->routeIs('school.communications.bulk*')">
                                Bulk Communication
                            </x-nav-link>
                        @endschoolFeature
                    @endif

                    @if (Auth::user()->hasRole('super_admin'))
                        @if ($canGroup('platform_settings') || $canGroup('local_school_settings'))
                            <x-nav-link :href="route('admin.platform-settings.edit')" :active="request()->routeIs('admin.platform-settings.*')">
                                {{ $canGroup('local_school_settings') ? 'Local School Settings' : __('ui.system_settings') }}
                            </x-nav-link>
                        @endif
                        @if ($canGroup('platform_result_system'))
                            <x-nav-link :href="route('admin.result-system.index')" :active="request()->routeIs('admin.result-system.*')">
                                Result System
                            </x-nav-link>
                        @endif
                        @if ($canGroup('system_maintenance'))
                            <x-nav-link :href="route('admin.system-maintenance.index')" :active="request()->routeIs('admin.system-maintenance.*')">
                                Maintenance
                            </x-nav-link>
                        @endif
                        @if ($canGroup('platform_communications'))
                            <x-nav-link :href="route('admin.communications.index')" :active="request()->routeIs('admin.communications.*')">
                                Communications
                            </x-nav-link>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden lg:ms-6 lg:flex lg:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            @if (Auth::user()->avatarUrl())
                                <img src="{{ Auth::user()->avatarUrl() }}" alt="{{ Auth::user()->name }} avatar" class="h-7 w-7 rounded-full object-cover">
                            @else
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-900 text-xs font-semibold text-white">{{ Auth::user()->initials() }}</span>
                            @endif
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center lg:hidden">
                <button type="button" @click="open = ! open" class="inline-flex h-11 w-11 items-center justify-center rounded-md text-gray-500 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary" aria-label="{{ __('ui.open_navigation') }}" aria-controls="legacy-mobile-navigation" :aria-expanded="open.toString()">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div id="legacy-mobile-navigation" x-cloak x-show="open" x-transition class="max-h-[calc(100dvh-4rem)] overflow-y-auto overscroll-contain lg:hidden" @click="if ($event.target.closest('a')) open = false">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if (Auth::user()->activeSchoolRoles()->count() > 1 || Auth::user()->roles()->count() > 1 || Auth::user()->hasRole('super_admin'))
                <x-responsive-nav-link :href="route('role-context.index')" :active="request()->routeIs('role-context.*')">
                    {{ __('ui.switch_role') }}
                </x-responsive-nav-link>
            @endif

            @if (in_array($roleContext, ['school_admin', 'super_admin'], true) || Auth::user()->hasRole('super_admin'))
                <x-responsive-nav-link :href="route('school.feature-control.index')" :active="request()->routeIs('school.feature-control.*')">
                    {{ __('ui.feature_control') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('school.role-permissions.index')" :active="request()->routeIs('school.role-permissions.*')">
                    {{ __('ui.roles_permissions') }}
                </x-responsive-nav-link>
            @endif

            @if ($roleContext === 'school_admin')
                <x-responsive-nav-link :href="route('school.profile.edit')" :active="request()->routeIs('school.profile.*')">
                    School Profile
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('school.staff.index')" :active="request()->routeIs('school.staff.*')">
                    Staff
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('school.admission-number-settings.edit')" :active="request()->routeIs('school.admission-number-settings.*')">
                    Admission Numbers
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('school.branding.edit')" :active="request()->routeIs('school.branding.*')">
                    Branding
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('school.sessions.index')" :active="request()->routeIs('school.sessions.*')">
                    Sessions
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('school.terms.index')" :active="request()->routeIs('school.terms.*')">
                    Terms
                </x-responsive-nav-link>
                @schoolFeature('communication.logs.view')
                    <x-responsive-nav-link :href="route('school.communications.index')" :active="request()->routeIs('school.communications.index', 'school.communications.logs*', 'school.communications.templates*')">
                        Communication Center
                    </x-responsive-nav-link>
                @endschoolFeature

                @schoolFeature('communication.bulk')
                    <x-responsive-nav-link :href="route('school.communications.bulk')" :active="request()->routeIs('school.communications.bulk*')">
                        Bulk Communication
                    </x-responsive-nav-link>
                @endschoolFeature
            @endif

            @if (Auth::user()->hasRole('super_admin'))
                @if ($canGroup('platform_settings') || $canGroup('local_school_settings'))
                    <x-responsive-nav-link :href="route('admin.platform-settings.edit')" :active="request()->routeIs('admin.platform-settings.*')">
                        {{ $canGroup('local_school_settings') ? 'Local School Settings' : __('ui.system_settings') }}
                    </x-responsive-nav-link>
                @endif
                @if ($canGroup('platform_result_system'))
                    <x-responsive-nav-link :href="route('admin.result-system.index')" :active="request()->routeIs('admin.result-system.*')">
                        Result System
                    </x-responsive-nav-link>
                @endif
                @if ($canGroup('system_maintenance'))
                    <x-responsive-nav-link :href="route('admin.system-maintenance.index')" :active="request()->routeIs('admin.system-maintenance.*')">
                        Maintenance
                    </x-responsive-nav-link>
                @endif
                @if ($canGroup('platform_communications'))
                    <x-responsive-nav-link :href="route('admin.communications.index')" :active="request()->routeIs('admin.communications.*')">
                        Communications
                    </x-responsive-nav-link>
                @endif
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
