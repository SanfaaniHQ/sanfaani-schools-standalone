<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanFeature;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        return view('admin.subscription-plans.index', [
            'plans' => SubscriptionPlan::withCount('features')
                ->orderBy('sort_order')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create()
    {
        return view('admin.subscription-plans.create', [
            'plan' => new SubscriptionPlan(['currency' => 'NGN', 'status' => 'active']),
            'featureKeys' => $this->featureKeys(),
            'planFeatures' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePlan($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name']);

        $plan = SubscriptionPlan::create($data);
        $this->syncFeatures($plan, $request);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan created successfully.');
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        return view('admin.subscription-plans.edit', [
            'plan' => $subscriptionPlan->load('features'),
            'featureKeys' => $this->featureKeys(),
            'planFeatures' => $subscriptionPlan->features->keyBy('feature_key'),
        ]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $data = $this->validatePlan($request, $subscriptionPlan);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $subscriptionPlan->id);

        $subscriptionPlan->update($data);
        $this->syncFeatures($subscriptionPlan, $request);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan updated successfully.');
    }

    public function archive(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->update(['status' => 'archived']);

        return back()->with('success', 'Subscription plan archived.');
    }

    public function activate(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->update(['status' => 'active']);

        return back()->with('success', 'Subscription plan activated.');
    }

    private function validatePlan(Request $request, ?SubscriptionPlan $plan = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'pricing_model' => ['required', Rule::in(['flat', 'per_student'])],
            'billing_cycle' => ['required', Rule::in(['monthly', 'term', 'annual'])],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'is_trial' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive', 'archived'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]) + [
            'is_trial' => false,
            'sort_order' => 0,
        ];
    }

    private function syncFeatures(SubscriptionPlan $plan, Request $request): void
    {
        $features = $request->input('features', []);

        foreach ($this->featureKeys() as $featureKey => $featureName) {
            PlanFeature::updateOrCreate(
                [
                    'subscription_plan_id' => $plan->id,
                    'feature_key' => $featureKey,
                ],
                [
                    'feature_name' => $featureName,
                    'is_enabled' => (bool) data_get($features, "{$featureKey}.enabled", false),
                    'limit_value' => data_get($features, "{$featureKey}.limit_value") ?: null,
                ]
            );
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'plan';
        $slug = $base;
        $counter = 2;

        while (SubscriptionPlan::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public static function featureKeys(): array
    {
        return [
            'student_bulk_upload' => 'Student Bulk Upload',
            'manual_result_entry' => 'Manual Result Entry',
            'csv_result_upload' => 'CSV Result Upload',
            'result_publishing' => 'Result Publishing',
            'scratch_cards' => 'Scratch Cards',
            'public_result_checker' => 'Public Result Checker',
            'report_card_basic' => 'Report Card Basic',
            'report_card_customization' => 'Report Card Customization',
            'report_card_signature' => 'Report Card Signatures',
            'report_card_auto_comments' => 'Report Card Auto Comments',
            'report_card_pdf' => 'Report Card PDF',
            'report_card_qr' => 'Report Card QR',
            'report_card_templates' => 'Report Card Templates',
            'pdf_result' => 'PDF Result',
            'qr_verification' => 'QR Verification',
            'assessment_results' => 'Assessment Results',
            'cbt_results' => 'CBT Results',
            'sms_units' => 'SMS Units',
            'mobile_app' => 'Mobile App',
            'biometric_attendance' => 'Biometric Attendance',
            'website_customization' => 'Website Customization',
        ];
    }
}
