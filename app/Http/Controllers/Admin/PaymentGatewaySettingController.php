<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewaySetting;
use App\Services\AuditLogService;
use App\Services\PaymentGatewaySettingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentGatewaySettingController extends Controller
{
    public function index(PaymentGatewaySettingService $settings)
    {
        return view('admin.payment-settings.index', [
            'gateways' => $settings->gateways(),
            'settings' => PaymentGatewaySetting::all()->keyBy(fn ($setting) => $setting->gateway.'_'.$setting->mode),
            'masker' => $settings,
        ]);
    }

    public function update(Request $request, string $gateway, PaymentGatewaySettingService $settings, AuditLogService $auditLog)
    {
        abort_unless(in_array($gateway, $settings->gateways(), true), 404);

        $data = $request->validate([
            'mode' => ['required', Rule::in(['test', 'live'])],
            'is_enabled' => ['nullable', 'boolean'],
            'public_key' => ['nullable', 'string', 'max:2000'],
            'secret_key' => ['nullable', 'string', 'max:2000'],
            'encryption_key' => ['nullable', 'string', 'max:2000'],
            'webhook_secret' => ['nullable', 'string', 'max:2000'],
            'callback_url' => ['nullable', 'url', 'max:255'],
        ]);

        $setting = $settings->setting($gateway, $data['mode']);
        $update = [
            'is_enabled' => (bool) ($data['is_enabled'] ?? false),
            'callback_url' => $data['callback_url'] ?? $setting->callback_url,
        ];

        foreach (['public_key', 'secret_key', 'encryption_key', 'webhook_secret'] as $secretField) {
            if (filled($data[$secretField] ?? null)) {
                $update[$secretField] = $data[$secretField];
            }
        }

        $setting->update($update);

        $auditLog->log('payment_gateway_settings_updated', $setting, null, metadata: [
            'gateway' => $gateway,
            'mode' => $data['mode'],
            'is_enabled' => $setting->is_enabled,
        ], request: $request);

        return back()->with('success', ucfirst($gateway).' payment settings saved successfully.');
    }
}
