<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MembershipController extends Controller
{
    public function plans()
    {
        return response()->json(MembershipPlan::active()->orderBy('display_order')->get());
    }

    public function index(Request $request)
    {
        $q = Membership::with(['plan', 'user']);

        if (!$request->user()->isStaff()) {
            $q->where('user_id', $request->user()->id);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        return response()->json($q->latest()->paginate($request->get('per_page', 15)));
    }

    /**
     * Subscribe to a plan (API version → returns JSON).
     * Payment can be immediate (wallet) or manual/later.
     */
    public function subscribe(Request $request, PaymentService $payments)
    {
        $data = $request->validate([
            'membership_plan_id' => 'required|exists:membership_plans,id',
            'payment_method'     => 'nullable|in:wallet,cash,pos,bank_transfer,paystack,flutterwave,manual,none',
            'start_date'         => 'nullable|date|after_or_equal:today',
            'user_id'            => 'nullable|exists:users,id',
        ]);

        $user = ($request->user()->isStaff() && !empty($data['user_id']))
            ? User::find($data['user_id'])
            : $request->user();

        $plan = MembershipPlan::findOrFail($data['membership_plan_id']);

        try {
            $membership = DB::transaction(function () use ($user, $plan, $data, $request, $payments) {
                $start = !empty($data['start_date'])
                    ? Carbon::parse($data['start_date'])
                    : now();
                $end = $start->copy()->addDays($plan->duration_days);

                $membership = Membership::create([
                    'user_id'            => $user->id,
                    'membership_plan_id' => $plan->id,
                    'start_date'         => $start,
                    'end_date'           => $end,
                    'amount_paid'        => 0,
                    'status'             => 'pending',
                    'sessions_remaining' => $plan->sessions_included,
                    'created_by'         => $request->user()->id,
                ]);

                $amount = (float) $plan->price;
                $method = $data['payment_method'] ?? 'none';

                if ($method === 'wallet') {
                    if ($user->wallet->balance < $amount) {
                        throw new \Exception('Insufficient wallet balance.');
                    }
                    $payments->payWithWallet($user, $amount, $membership, "Membership: {$plan->name}");
                } elseif (
                    in_array($method, ['cash', 'pos', 'bank_transfer', 'manual'])
                    && $request->user()->isStaff()
                ) {
                    $payments->record(
                        $user, $amount, $method, $membership,
                        $request->user()->id, 'Front-desk payment'
                    );
                }

                ActivityLogger::log('Membership created', $membership, [
                    'plan'   => $plan->name,
                    'user'   => $user->full_name,
                    'amount' => $amount,
                    'method' => $method,
                ]);

                return $membership;
            });

            return response()->json([
                'message'    => 'Membership created successfully',
                'membership' => $membership->fresh()->load('plan', 'user'),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}