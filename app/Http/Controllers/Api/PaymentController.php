<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Membership;
use App\Models\Payment;
use App\Services\ActivityLogger;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $service) {}

    public function index(Request $request)
    {
        $q = Payment::with(['user', 'processor']);
        if (!$request->user()->isStaff()) $q->where('user_id', $request->user()->id);
        if ($request->filled('status')) $q->where('status', $request->status);
        if ($request->filled('method')) $q->where('method', $request->method);
        if ($request->filled('from')) $q->whereDate('created_at', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('created_at', '<=', $request->to);
        return response()->json($q->latest()->paginate($request->get('per_page', 15)));
    }

    /**
     * Customer pays for booking/membership. Method: wallet or manual (front-desk cash).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'payable_type' => 'required|in:booking,membership',
            'payable_id' => 'required|integer',
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:wallet,cash,pos,bank_transfer,paystack,flutterwave,manual',
            'user_id' => 'nullable|exists:users,id', // front-desk can pay on behalf
            'notes' => 'nullable|string|max:500',
        ]);

        $payable = $data['payable_type'] === 'booking'
            ? Booking::findOrFail($data['payable_id'])
            : Membership::findOrFail($data['payable_id']);

        $user = ($request->user()->isStaff() && !empty($data['user_id']))
            ? \App\Models\User::find($data['user_id'])
            : $payable->user;

        // Security: only staff can pay for others
        if (!$request->user()->isStaff() && $payable->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized.');
        }

        if ($data['amount'] > $payable->total_amount - $payable->amount_paid) {
            return response()->json(['message' => 'Amount exceeds outstanding balance.'], 422);
        }

        if ($data['method'] === 'wallet') {
            $payment = $this->service->payWithWallet($user, $data['amount'], $payable, $data['notes'] ?? null);
        } else {
            $payment = $this->service->record(
                $user, $data['amount'], $data['method'], $payable,
                $request->user()->id, $data['notes'] ?? null, 'successful'
            );
        }

        ActivityLogger::log('Payment recorded', $payable, [
            'amount' => $data['amount'],
            'method' => $data['method'],
            'payment_code' => $payment->payment_code,
        ]);

        return response()->json([
            'message' => 'Payment recorded successfully',
            'payment' => $payment,
            'payable' => $payable->fresh(),
        ], 201);
    }
}