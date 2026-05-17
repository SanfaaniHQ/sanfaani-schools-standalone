@props(['href', 'active' => false, 'icon' => 'circle'])

@php
    $base = 'group flex min-h-11 items-center gap-3 rounded-md border-s-2 px-3 py-2.5 text-sm font-medium transition duration-200 ease-default focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 focus-visible:ring-offset-bg-primary';
    $state = $active
        ? 'border-brand-primary bg-bg-secondary text-text-primary'
        : 'border-transparent text-text-secondary hover:bg-bg-secondary hover:text-text-primary';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $base . ' ' . $state]) }} @if ($active) aria-current="page" @endif>
    <svg aria-hidden="true" class="h-4 w-4 shrink-0 {{ $active ? 'text-brand-primary' : 'text-text-tertiary group-hover:text-text-primary' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        @switch($icon)
            @case('activity')
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                @break
            @case('archive')
                <path d="M21 8v13H3V8"></path>
                <path d="M1 3h22v5H1z"></path>
                <path d="M10 12h4"></path>
                @break
            @case('bar-chart')
                <path d="M3 3v18h18"></path>
                <path d="M7 16V9"></path>
                <path d="M12 16V5"></path>
                <path d="M17 16v-3"></path>
                @break
            @case('book-open')
                <path d="M2 4h7a3 3 0 0 1 3 3v13a3 3 0 0 0-3-3H2z"></path>
                <path d="M22 4h-7a3 3 0 0 0-3 3v13a3 3 0 0 1 3-3h7z"></path>
                @break
            @case('calendar')
                <path d="M8 2v4"></path>
                <path d="M16 2v4"></path>
                <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                <path d="M3 10h18"></path>
                @break
            @case('clipboard-list')
                <rect x="8" y="2" width="8" height="4" rx="1"></rect>
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                <path d="M9 12h6"></path>
                <path d="M9 16h6"></path>
                @break
            @case('code')
                <path d="m16 18 6-6-6-6"></path>
                <path d="m8 6-6 6 6 6"></path>
                @break
            @case('credit-card')
                <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                <path d="M2 10h20"></path>
                @break
            @case('file-text')
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <path d="M14 2v6h6"></path>
                <path d="M16 13H8"></path>
                <path d="M16 17H8"></path>
                @break
            @case('graduation-cap')
                <path d="m22 10-10-5-10 5 10 5z"></path>
                <path d="M6 12v5c3 2 9 2 12 0v-5"></path>
                @break
            @case('home')
                <path d="m3 11 9-8 9 8"></path>
                <path d="M5 10v10h14V10"></path>
                @break
            @case('layout-grid')
                <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                @break
            @case('mail')
                <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                <path d="m3 7 9 6 9-6"></path>
                @break
            @case('pie-chart')
                <path d="M21 12a9 9 0 1 1-9-9v9z"></path>
                <path d="M12 3a9 9 0 0 1 9 9h-9z"></path>
                @break
            @case('settings')
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.6V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9v.1a1.7 1.7 0 0 0 1.6 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"></path>
                @break
            @case('shield')
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"></path>
                @break
            @case('users')
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                @break
            @case('wallet')
                <path d="M20 7H5a3 3 0 0 0 0 6h15v6H5a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h15z"></path>
                <path d="M16 13h.01"></path>
                @break
            @default
                <circle cx="12" cy="12" r="4"></circle>
        @endswitch
    </svg>

    <span class="truncate">{{ $slot }}</span>
</a>
