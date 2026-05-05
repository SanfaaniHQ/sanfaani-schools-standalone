<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolSubscriptionController extends Controller
{
    public function index()
    {
        return view('admin.school-subscriptions.index', [
            'subscriptions' => SchoolSubscription::with(['school', 'subscriptionPlan'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create()
    {
        return view('admin.school-subscriptions.create', [
            'schools' => School::with(['subscriptions' => fn ($query) => $query->latest(), 'subscriptions.subscriptionPlan'])
                ->orderBy('name')
                ->get(),
            'plans' => SubscriptionPlan::with('features')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => ['required', Rule::exists('schools', 'id')],
            'subscription_plan_id' => ['required', Rule::exists('subscription_plans', 'id')],
            'status' => ['required', Rule::in(['trial', 'active', 'grace', 'expired', 'cancelled', 'superseded'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'trial_ends_at' => ['nullable', 'date'],
            'grace_ends_at' => ['nullable', 'date'],
            'amount_due' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', Rule::in(['pending', 'manual_pending', 'paid', 'failed', 'cancelled'])],
        ]);

        $plan = SubscriptionPlan::with('features')->findOrFail($data['subscription_plan_id']);

        SchoolSubscription::where('school_id', $data['school_id'])
            ->whereIn('status', ['trial', 'active', 'grace'])
            ->update(['status' => 'superseded']);

        SchoolSubscription::create($data + [
            'billing_cycle' => $plan->billing_cycle,
            'pricing_model' => $plan->pricing_model,
            'price' => $plan->price,
            'currency' => $plan->currency,
            'activated_by' => auth()->id(),
            'plan_name_snapshot' => $plan->name,
            'price_snapshot' => $plan->price,
            'billing_cycle_snapshot' => $plan->billing_cycle,
            'pricing_model_snapshot' => $plan->pricing_model,
            'features_snapshot' => $plan->features->mapWithKeys(fn ($feature) => [
                $feature->feature_key => [
                    'enabled' => $feature->is_enabled,
                    'limit' => $feature->limit_value,
                ],
            ])->all(),
        ]);

        return redirect()
            ->route('admin.school-subscriptions.index')
            ->with('success', 'School subscription assigned successfully.');
    }
}
