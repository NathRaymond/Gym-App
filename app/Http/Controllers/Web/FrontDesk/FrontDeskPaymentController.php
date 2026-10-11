<?php

namespace App\Http\Controllers\Web\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Membership;
use App\Services\ActivityLogger;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class FrontDeskPaymentController extends Controller
{
    public function __construct(protected PaymentService $payments) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'payable_type' => 'required|in:booking,membership',
            'payable_id'   => 'required|integer',
            'amount'       => 'required|numeric|min:1',
            'method'       => 'required|in:cash,pos,bank_transfer',
            'notes'        => 'nullable|string|max:500',
        ]);

        $payable = $data['payable_type'] === 'booking'
            ? Booking::findOrFail($data['payable_id'])
            : Membership::findOrFail($data['payable_id']);

        $maxAmount = $payable instanceof Booking
            ? $payable->balance
            : (float) $payable->plan->price;

        // Allow amount == 0 check
        if ($data['amount'] > $maxAmount) {
            return back()->with('error', 'Amount (₦' . number_format($data['amount'], 2) . ') exceeds outstanding balance (₦' . number_format($maxAmount, 2) . ').');
        }

        try {
            $this->payments->record(
                $payable->user,
                $data['amount'],
                $data['method'],
                $payable,
                $request->user()->id,
                $data['notes'] ?? 'Front-desk payment',
                'successful'
            );

            ActivityLogger::log('Front-desk payment recorded', $payable, [
                'amount' => $data['amount'],
                'method' => $data['method'],
            ]);

            return redirect()->route('frontdesk.payments')
                ->with('success', "Payment of ₦" . number_format($data['amount'], 2) . " recorded successfully.");

        } catch (\Exception $e) {
            return back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }
}