<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\School;
use App\Services\AuditLogService;
use App\Services\PaymentGatewaySettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResultCheckerPaymentController extends Controller
{
    public function initiate(Request $request, PaymentGatewaySettingService $gateways, AuditLogService $auditLog, ?School $school = null)
    {
        $gateway = $gateways->enabledForParentPayment();

        if (! $gateway) {
            return back()->with('error', 'Parent direct payment is not enabled for this result checker yet.');
        }

        $transaction = PaymentTransaction::create([
            'school_id' => $school?->id,
            'amount' => 0,
            'currency' => config('app.currency', 'NGN'),
            'payment_method' => 'gateway',
            'payment_gateway' => $gateway->gateway,
            'payment_reference' => 'RCP-'.Str::upper(Str::random(12)),
            'status' => 'pending',
            'metadata' => [
                'foundation_only' => true,
                'gateway_mode' => $gateway->mode,
                'school_slug' => $school?->slug,
            ],
        ]);

        $auditLog->log('result_checker_payment_initiated', $transaction, null, metadata: [
            'gateway' => $gateway->gateway,
            'mode' => $gateway->mode,
            'school_id' => $school?->id,
        ], request: $request);

        return back()->with('success', 'Payment request created. Gateway redirect will be enabled after server-side verification is completed.');
    }

    public function initiateForSchool(Request $request, School $school, PaymentGatewaySettingService $gateways, AuditLogService $auditLog)
    {
        if ($school->status !== 'active') {
            abort(404);
        }

        return $this->initiate($request, $gateways, $auditLog, $school);
    }

    public function callback(Request $request, ?string $gateway = null)
    {
        return redirect()
            ->route('public.results.index')
            ->with('status', 'Payment callback received. Result access will be confirmed after server-side verification.');
    }

    public function callbackForSchool(Request $request, School $school, ?string $gateway = null)
    {
        if ($school->status !== 'active') {
            abort(404);
        }

        return redirect()
            ->route('public.school.results.index', ['school' => $school->slug])
            ->with('status', 'Payment callback received. Result access will be confirmed after server-side verification.');
    }
}
