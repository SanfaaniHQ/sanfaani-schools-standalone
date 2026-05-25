<div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
    <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
    <p class="mt-2 break-words text-lg font-semibold text-gray-900">{{ $value }}</p>
    @isset($meta)
        <p class="mt-1 text-sm text-gray-500">{{ $meta }}</p>
    @endisset
</div>
