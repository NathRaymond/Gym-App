<?php

namespace App\Http\Controllers\Web\Member;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaystackService;
use Illuminate\Http\Request;

class PaystackCallbackController extends Controller
{
    public function __construct(protected PaystackService $paystack) {}

    public function handle(Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (!$reference) {
            return redirect()->route('member.dashboard')->with('error', 'No payment reference.');
        }

        $payment = Payment::where('gateway_reference', $reference)->first();

        if (!$payment) {
            return redirect()->route('member.dashboard')->with('error', 'Payment record not found.');
        }

        // Route to the correct handler based on payment purpose
        if ($payment->payable_type === \App\Models\Membership::class) {
            return app(MemberMembershipController::class)->paystackCallback($request);
        }

        if ($payment->payable_type === \App\Models\Booking::class) {
            return app(MemberPaymentController::class)->paystackBookingCallback($request);
        }

        // Default: wallet funding
        return app(MemberWalletController::class)->paystackCallback($request);
    }
}