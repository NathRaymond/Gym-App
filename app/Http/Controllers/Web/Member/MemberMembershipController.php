<?php

namespace App\Http\Controllers\Web\Member;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Services\ActivityLogger;
use App\Services\PaystackService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MemberMembershipController extends Controller
{
    public function __construct(
        protected PaymentService $payments,
        protected PaystackService $paystack
    ) {}

    public function index(Request $request)
    {
        return view('member.membership', [
            'memberships' => $request->user()->memberships()->with('plan')->latest()->get(),
            'plans'       => MembershipPlan::active()->orderBy('display_order')->get(),
        ]);
    }

    /**
     * Handle subscription with payment method branch.
     */
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'payment_method'     => 'required|in:wallet,paystack,flutterwave,cash',
        ]);

        $user = $request->user();
        $plan = MembershipPlan::findOrFail($data['membership_plan_id']);

        if ($user->hasActiveMembership()) {
            return back()->with('error', 'You already have an active membership.');
        }

        // ============================================
        // PAYSTACK → redirect flow
        // ============================================
        if ($data['payment_method'] === 'paystack') {
            try {
                // Create a pending membership first
                $membership = DB::transaction(function () use ($user, $plan) {
                    return Membership::create([
                        'user_id'            => $user->id,
                        'membership_plan_id' => $plan->id,
                        'start_date'         => now(),
                        'end_date'           => now()->addDays($plan->duration_days),
                        'amount_paid'        => 0,
                        'status'             => 'pending',
                        'sessions_remaining' => $plan->sessions_included,
                        'created_by'         => $user->id,
                    ]);
                });

                // Initialize Paystack
                $result = $this->paystack->initialize(
                    $user,
                    (float) $plan->price,
                    [
                        'purpose'       => 'membership_subscription',
                        'membership_id' => $membership->id,
                        'plan_name'     => $plan->name,
                    ]
                );

                // Store a pending payment so we can match the callback
                Payment::create([
                    'payment_code'      => $result['reference'],
                    'user_id'           => $user->id,
                    'payable_type'      => Membership::class,
                    'payable_id'        => $membership->id,
                    'amount'            => $plan->price,
                    'currency'          => 'NGN',
                    'method'            => 'paystack',
                    'status'            => 'pending',
                    'gateway_reference' => $result['reference'],
                    'notes'             => "Membership subscription: {$plan->name}",
                ]);

                ActivityLogger::log('Membership Paystack initiated', $membership, [
                    'plan'      => $plan->name,
                    'reference' => $result['reference'],
                ]);

                return redirect($result['authorization_url']);

            } catch (\Exception $e) {
                return back()->with('error', 'Could not start Paystack payment: ' . $e->getMessage());
            }
        }

        // ============================================
        // WALLET → immediate
        // ============================================
        if ($data['payment_method'] === 'wallet') {
            try {
                DB::transaction(function () use ($user, $plan) {
                    if ($user->wallet->balance < $plan->price) {
                        throw new \Exception('Insufficient wallet balance. Please fund your wallet first.');
                    }

                    $membership = Membership::create([
                        'user_id'            => $user->id,
                        'membership_plan_id' => $plan->id,
                        'start_date'         => now(),
                        'end_date'           => now()->addDays($plan->duration_days),
                        'amount_paid'        => 0,
                        'status'             => 'pending',
                        'sessions_remaining' => $plan->sessions_included,
                        'created_by'         => $user->id,
                    ]);

                    $this->payments->payWithWallet(
                        $user,
                        (float) $plan->price,
                        $membership,
                        "Membership: {$plan->name}"
                    );

                    ActivityLogger::log('Membership subscribed via wallet', $membership, [
                        'plan' => $plan->name,
                    ]);
                });

                return redirect()->route('member.membership')
                    ->with('success', "Successfully subscribed to {$plan->name}!");

            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        // ============================================
        // CASH → front desk flow
        // ============================================
        if ($data['payment_method'] === 'cash') {
            $membership = Membership::create([
                'user_id'            => $user->id,
                'membership_plan_id' => $plan->id,
                'start_date'         => now(),
                'end_date'           => now()->addDays($plan->duration_days),
                'amount_paid'        => 0,
                'status'             => 'pending',
                'sessions_remaining' => $plan->sessions_included,
                'created_by'         => $user->id,
            ]);

            ActivityLogger::log('Membership pending cash payment', $membership, [
                'plan' => $plan->name,
            ]);

            return redirect()->route('member.membership')
                ->with('info', "Membership reserved! Please pay ₦" . number_format($plan->price, 2) . " at the front desk to activate it.");
        }

        return back()->with('error', 'Unsupported payment method.');
    }

    /**
     * Paystack callback for membership payments.
     */
    public function paystackCallback(Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (!$reference) {
            return redirect()->route('member.membership')->with('error', 'No payment reference received.');
        }

        try {
            $verification = $this->paystack->verify($reference);

            if (!($verification['status'] ?? false)) {
                throw new \Exception($verification['message'] ?? 'Verification failed');
            }

            $txn = $verification['data'];

            if ($txn['status'] !== 'success') {
                Payment::where('gateway_reference', $reference)->update(['status' => 'failed']);
                return redirect()->route('member.membership')->with('error', 'Payment was not successful.');
            }

            $payment = Payment::where('gateway_reference', $reference)->first();

            if (!$payment) {
                return redirect()->route('member.membership')->with('error', 'Payment record not found.');
            }

            if ($payment->status === 'successful') {
                return redirect()->route('member.membership')->with('success', 'Payment already processed.');
            }

            DB::transaction(function () use ($payment, $txn) {
                $payment->update([
                    'status'           => 'successful',
                    'gateway_response' => $txn,
                    'paid_at'          => now(),
                ]);

                $membership = $payment->payable;
                $planPrice = (float) $membership->plan->price;

                $membership->update([
                    'amount_paid'    => $planPrice,
                    'payment_status' => 'paid',
                    'status'         => 'active',
                    'payment_method' => 'paystack',
                ]);

                // Generate membership ticket
                $this->payments->generateMembershipTicket($membership);

                ActivityLogger::log('Membership paid via Paystack', $membership, [
                    'reference' => $payment->gateway_reference,
                    'amount'    => $planPrice,
                ]);
            });

            return redirect()->route('member.membership')
                ->with('success', 'Membership activated successfully! Your ticket has been generated.');

        } catch (\Exception $e) {
            Payment::where('gateway_reference', $reference)->update(['status' => 'failed']);
            return redirect()->route('member.membership')->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }
}