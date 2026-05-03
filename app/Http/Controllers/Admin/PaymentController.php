<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\ScratchCardBatch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index()
    {
        return view('admin.payments.index', [
            'payments' => PaymentTransaction::with(['school', 'student', 'confirmedBy', 'payable'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function confirm(Request $request, PaymentTransaction $payment)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'payment_method' => ['required', Rule::in(['bank_transfer', 'cash', 'manual'])],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'manual_payment_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment->update([
            'amount' => $data['amount'],
            'currency' => strtoupper($data['currency']),
            'payment_method' => $data['payment_method'],
            'payment_reference' => $data['payment_reference'] ?? $payment->payment_reference,
            'manual_payment_note' => $data['manual_payment_note'] ?? null,
            'status' => 'paid',
            'paid_at' => now(),
            'confirmed_at' => now(),
            'confirmed_by' => auth()->id(),
        ]);

        if ($payment->payable instanceof ScratchCardBatch) {
            $payment->payable->update([
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'payment_method' => $payment->payment_method,
                'payment_reference' => $payment->payment_reference,
                'payment_status' => 'paid',
                'payment_confirmed_at' => now(),
                'payment_confirmed_by' => auth()->id(),
            ]);
        }

        return back()->with('success', 'Payment confirmed successfully.');
    }
}
