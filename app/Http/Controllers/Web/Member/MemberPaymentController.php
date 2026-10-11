<?php

namespace App\Http\Controllers\Web\Member;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Membership;
use App\Services\ActivityLogger;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class MemberPaymentController extends Controller
{
    public function __construct(protected PaymentService $payments) {}

    /**
     * Pay for a booking (from member dashboard).
     */
    public function payBooking(Request $request, Booking $booking)
    {
        // Security: only owner can pay
        if ($booking->user_id !== $request->user()->id) {
            abort(403, 'You can only pay for your own bookings.');
        }

        // Booking must be unpaid
        if ($booking->payment_status === 'paid') {
            return back()->with('error', 'This booking is already paid.');
        }

        $request->validate([
            'payment_method' => 'required|in:wallet,cash,pos,paystack,flutterwave',
        ]);

        $method = $request->payment_method;
        $amount = $booking->balance;

        if ($amount <= 0) {
            return back()->with('error', 'Nothing to pay.');
        }

        try {
            if ($method === 'wallet') {
                $wallet = $request->user()->getOrCreateWallet();

                if ($wallet->balance < $amount) {
                    return back()->with('error', 'Insufficient wallet balance. Please fund your wallet first.');
                }

                $this->payments->payWithWallet(
                    $request->user(),
                    $amount,
                    $booking,
                    "Payment for booking {$booking->booking_code}"
                );
            } elseif (in_array($method, ['cash', 'pos'])) {
                // Pay at gym — record intent, front desk will confirm
                return back()->with('info',
                    'Booking noted. Please pay ₦' . number_format($amount, 2) .
                    ' at the front desk to complete your booking.'
                );
            } else {
                // paystack, flutterwave → not yet implemented
                return back()->with('error', 'This payment method is coming soon. Please use wallet or pay at the gym.');
            }

            ActivityLogger::log('Booking paid by member', $booking, [
                'amount' => $amount,
                'method' => $method,
            ]);

            return redirect()->route('member.bookings')
                ->with('success', "Payment successful! ₦" . number_format($amount, 2) . " paid for booking {$booking->booking_code}.");

        } catch (\Exception $e) {
            return back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    /**
     * Pay for a membership.
     */
    public function payMembership(Request $request, Membership $membership)
    {
        if ($membership->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'payment_method' => 'required|in:wallet,cash,pos,paystack,flutterwave',
        ]);

        $method = $request->payment_method;
        $amount = (float) $membership->plan->price;

        try {
            if ($method === 'wallet') {
                $wallet = $request->user()->getOrCreateWallet();

                if ($wallet->balance < $amount) {
                    return back()->with('error', 'Insufficient wallet balance.');
                }

                $this->payments->payWithWallet(
                    $request->user(),
                    $amount,
                    $membership,
                    "Membership payment: {$membership->plan->name}"
                );
            } else {
                return back()->with('error', 'Please use wallet or pay at the gym.');
            }

            return redirect()->route('member.membership')
                ->with('success', 'Membership activated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}