<?php

namespace App\Http\Controllers\Web\Member;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\ActivityLogger;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberWalletController extends Controller
{
    public function __construct(protected PaystackService $paystack) {}

    /**
     * Show the wallet page.
     */
    public function index(Request $request)
    {
        $wallet = $request->user()->getOrCreateWallet();

        return view('member.wallet', [
            'wallet'       => $wallet,
            'transactions' => $wallet->transactions()->latest()->paginate(20),
        ]);
    }

    /**
     * Initialize Paystack payment for wallet funding.
     */
    public function fundWithPaystack(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:100|max:1000000',
        ]);

        try {
            $result = $this->paystack->initialize(
                $request->user(),
                (float) $data['amount'],
                ['purpose' => 'wallet_funding']
            );

            // Store pending payment
            Payment::create([
                'payment_code'      => $result['reference'],
                'user_id'           => $request->user()->id,
                'amount'            => $data['amount'],
                'currency'          => 'NGN',
                'method'            => 'paystack',
                'status'            => 'pending',
                'gateway_reference' => $result['reference'],
                'notes'             => 'Wallet funding via Paystack',
            ]);

            return redirect($result['authorization_url']);

        } catch (\Exception $e) {
            return back()->with('error', 'Could not start payment: ' . $e->getMessage());
        }
    }

    /**
     * Paystack redirects here after the user completes payment.
     */
    public function paystackCallback(Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (!$reference) {
            return redirect()->route('member.wallet')->with('error', 'No payment reference received.');
        }

        try {
            $verification = $this->paystack->verify($reference);

            if (!($verification['status'] ?? false)) {
                throw new \Exception($verification['message'] ?? 'Verification failed');
            }

            $txn = $verification['data'];

            if ($txn['status'] !== 'success') {
                Payment::where('gateway_reference', $reference)->update(['status' => 'failed']);
                return redirect()->route('member.wallet')->with('error', 'Payment was not successful.');
            }

            $payment = Payment::where('gateway_reference', $reference)->first();

            if (!$payment) {
                return redirect()->route('member.wallet')->with('error', 'Payment record not found.');
            }

            // Idempotency guard
            if ($payment->status === 'successful') {
                return redirect()->route('member.wallet')->with('success', 'Payment already processed.');
            }

            // Credit wallet
            DB::transaction(function () use ($payment, $txn) {
                $payment->update([
                    'status'           => 'successful',
                    'gateway_response' => $txn,
                    'paid_at'          => now(),
                ]);

                $wallet = $payment->user->getOrCreateWallet();
                $wallet->credit(
                    $payment->amount,
                    'Wallet funded via Paystack (' . $payment->gateway_reference . ')',
                    'paystack',
                    $payment
                );

                ActivityLogger::log('Wallet funded via Paystack', $payment, [
                    'amount'    => $payment->amount,
                    'reference' => $payment->gateway_reference,
                ]);
            });

            return redirect()->route('member.wallet')
                ->with('success', 'Wallet funded successfully! ₦' . number_format($payment->amount, 2));

        } catch (\Exception $e) {
            Payment::where('gateway_reference', $reference)->update(['status' => 'failed']);
            return redirect()->route('member.wallet')->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }
}