@if (session('next_actions'))
    <div class="mb-6 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
        <p class="text-sm font-semibold text-emerald-900">Saved successfully. Next actions:</p>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach (session('next_actions') as $action)
                <a href="{{ $action['href'] }}"
                   class="rounded-lg bg-white px-3 py-2 text-sm font-medium text-emerald-900 shadow-sm hover:bg-emerald-100">
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    </div>
@endif
