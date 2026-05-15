<?php

namespace App\Services;

use App\Models\PaymentGatewaySetting;

class PaymentGatewaySettingService
{
    public function gateways(): array
    {
        return ['paystack', 'flutterwave'];
    }

    public function setting(string $gateway, string $mode = 'test'): PaymentGatewaySetting
    {
        return PaymentGatewaySetting::firstOrCreate([
            'gateway' => $gateway,
            'mode' => $mode,
        ], [
            'is_enabled' => false,
            'callback_url' => route('public.results.payment.callback', ['gateway' => $gateway], false),
        ]);
    }

    public function enabledForParentPayment(): ?PaymentGatewaySetting
    {
        return PaymentGatewaySetting::where('is_enabled', true)
            ->orderByRaw("CASE WHEN mode = 'live' THEN 0 ELSE 1 END")
            ->first();
    }

    public function mask(?string $value): string
    {
        if (! filled($value)) {
            return 'Not set';
        }

        $value = (string) $value;

        if (strlen($value) <= 8) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 7).'****'.substr($value, -4);
    }
}
