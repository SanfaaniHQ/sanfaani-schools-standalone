<div class="space-y-3">
    @foreach ($checks as $check)
        <div class="flex flex-col gap-2 rounded-md border border-border-subtle bg-bg-secondary px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-text-primary">{{ $check['label'] }}</p>
                <p class="mt-1 text-xs text-text-secondary">{{ $check['message'] }}</p>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="rounded-md px-2 py-1 font-semibold {{ $check['status'] === 'pass' ? 'bg-green-100 text-green-700' : ($check['status'] === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                    {{ str($check['status'])->upper() }}
                </span>
                <span class="max-w-[14rem] truncate text-text-muted">{{ $check['value'] }}</span>
            </div>
        </div>
    @endforeach
</div>
