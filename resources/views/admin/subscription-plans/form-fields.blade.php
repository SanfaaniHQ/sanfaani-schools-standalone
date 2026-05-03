<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-gray-700">Name</label>
        <input name="name" value="{{ old('name', $plan->name) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Slug</label>
        <input name="slug" value="{{ old('slug', $plan->slug) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">Description</label>
    <textarea name="description" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900">{{ old('description', $plan->description) }}</textarea>
</div>

<div class="grid gap-6 md:grid-cols-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Price</label>
        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $plan->price ?? 0) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Currency</label>
        <input name="currency" value="{{ old('currency', $plan->currency ?? 'NGN') }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Pricing Model</label>
        <select name="pricing_model" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
            <option value="per_student" @selected(old('pricing_model', $plan->pricing_model) === 'per_student')>Per student</option>
            <option value="flat" @selected(old('pricing_model', $plan->pricing_model) === 'flat')>Flat</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Billing Cycle</label>
        <select name="billing_cycle" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
            <option value="term" @selected(old('billing_cycle', $plan->billing_cycle) === 'term')>Term</option>
            <option value="monthly" @selected(old('billing_cycle', $plan->billing_cycle) === 'monthly')>Monthly</option>
            <option value="annual" @selected(old('billing_cycle', $plan->billing_cycle) === 'annual')>Annual</option>
        </select>
    </div>
</div>

<div class="grid gap-6 md:grid-cols-3">
    <div>
        <label class="block text-sm font-medium text-gray-700">Duration Days</label>
        <input type="number" min="1" name="duration_days" value="{{ old('duration_days', $plan->duration_days) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Status</label>
        <select name="status" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
            <option value="active" @selected(old('status', $plan->status) === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $plan->status) === 'inactive')>Inactive</option>
            <option value="archived" @selected(old('status', $plan->status) === 'archived')>Archived</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Sort Order</label>
        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm">
    </div>
</div>

<label class="flex items-center gap-3 rounded-xl border border-gray-200 p-4 text-sm font-medium text-gray-700">
    <input type="hidden" name="is_trial" value="0">
    <input type="checkbox" name="is_trial" value="1" @checked(old('is_trial', $plan->is_trial)) class="rounded border-gray-300 text-gray-900">
    Trial plan
</label>

<div class="rounded-2xl bg-gray-50 p-4">
    <h3 class="text-base font-semibold text-gray-900">Features</h3>
    <div class="mt-4 grid gap-3 md:grid-cols-2">
        @foreach ($featureKeys as $featureKey => $featureName)
            @php($feature = $planFeatures->get($featureKey))
            <div class="rounded-xl bg-white p-4">
                <label class="flex items-center gap-3 text-sm font-medium text-gray-800">
                    <input type="hidden" name="features[{{ $featureKey }}][enabled]" value="0">
                    <input type="checkbox" name="features[{{ $featureKey }}][enabled]" value="1" @checked(old("features.$featureKey.enabled", $feature?->is_enabled)) class="rounded border-gray-300 text-gray-900">
                    {{ $featureName }}
                </label>
                <input type="number" min="0" name="features[{{ $featureKey }}][limit_value]" value="{{ old("features.$featureKey.limit_value", $feature?->limit_value) }}" placeholder="Optional limit" class="mt-3 block w-full rounded-lg border-gray-300 text-sm shadow-sm">
            </div>
        @endforeach
    </div>
</div>

<div class="flex justify-end gap-3">
    <a href="{{ route('admin.subscription-plans.index') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
    <button type="submit" data-loading-text="Saving..." class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Save Plan</button>
</div>
