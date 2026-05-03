@php
    $platformName = config('sanfaani.platform_name', 'Sanfaani Schools');
@endphp

<section class="bg-gray-950">
    <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8">
        <h2 class="text-3xl font-semibold text-white sm:text-4xl">
            {{ $title ?? 'Ready to make result management easier?' }}
        </h2>
        <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-gray-300">
            {{ $body ?? 'Start with a guided demo and see how ' . $platformName . ' can support your school operations.' }}
        </p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('landing.demo') }}" class="rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-gray-950 shadow-sm hover:bg-gray-100">
                Request Demo
            </a>
            <a href="{{ route('public.results.index') }}" class="rounded-2xl border border-white/20 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10">
                Check Result
            </a>
        </div>
    </div>
</section>
