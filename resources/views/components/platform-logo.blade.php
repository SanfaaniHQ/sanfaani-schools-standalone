@props([
    'class' => 'h-10 w-auto',
    'markClass' => 'flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-700 text-sm font-semibold text-white',
    'showName' => true,
    'nameClass' => 'text-base font-semibold text-gray-950',
])

@php
    $useSchoolBranding = request()->routeIs('school.*') && ($schoolBranding->is_school_context ?? false);
    $logoUrl = $useSchoolBranding ? $schoolBranding->logo_url : $platformLogoUrl;
    $brandName = $useSchoolBranding ? $schoolBranding->name : $platformSettings->platform_name;
    $initials = $useSchoolBranding ? $schoolBranding->initials : $platformInitials;
@endphp

@if ($logoUrl)
    <img src="{{ $logoUrl }}" alt="{{ $brandName }}" {{ $attributes->merge(['class' => $class]) }}>
@else
    <span {{ $attributes->merge(['class' => $markClass]) }}>{{ $initials }}</span>
@endif

@if ($showName)
    <span class="{{ $nameClass }}">{{ $brandName }}</span>
@endif
