@props([
    'class' => 'h-10 w-auto',
    'markClass' => 'flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-700 text-sm font-semibold text-white',
    'showName' => true,
    'nameClass' => 'text-base font-semibold text-gray-950',
])

@if ($platformLogoUrl)
    <img src="{{ $platformLogoUrl }}" alt="{{ $platformSettings->platform_name }}" {{ $attributes->merge(['class' => $class]) }}>
@else
    <span {{ $attributes->merge(['class' => $markClass]) }}>{{ $platformInitials }}</span>
@endif

@if ($showName)
    <span class="{{ $nameClass }}">{{ $platformSettings->platform_name }}</span>
@endif
