<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret    = config('paystack.webhook_secret');
        $signature = $request->header('x-paystack-signature');
        $computed  = hash_hmac('sha512', $request->getContent(), $secret);

        if ($signature !== $computed) {
            Log::warning('Paystack webhook: invalid signature');
            return response()->json(['status' => false], 401);
        }

        $event = $request->input('event');
        $data  = $request->input('data');

        if ($event !== 'charge.success') {
            return response()->json(['status' => true]);
        }

        $reference = $data['reference'] ?? null;

        $payment = Payment::where('gateway_reference', $reference)->first();

        if (!$payment || $payment->status === 'successful') {
            return response()->json(['status' => true]);
        }

        DB::transaction(function () use ($payment, $data) {
            $payment->update([
                'status'           => 'successful',
                'gateway_response' => $data,
                'paid_at'          => now(),
            ]);

            $wallet = $payment->user->getOrCreateWallet();
            $wallet->credit(
                $payment->amount,
                'Wallet funded via Paystack (' . $payment->gateway_reference . ')',
                'paystack',
                $payment
            );

            ActivityLogger::log('Wallet funded via Paystack webhook', $payment);
        });

        return response()->json(['status' => true]);
    }
}