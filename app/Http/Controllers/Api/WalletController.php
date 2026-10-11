<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function show(Request $request)
    {
        $wallet = $request->user()->getOrCreateWallet();
        return response()->json([
            'wallet' => $wallet,
            'transactions' => $wallet->transactions()->latest()->limit(20)->get(),
        ]);
    }

    public function transactions(Request $request)
    {
        $wallet = $request->user()->getOrCreateWallet();
        $q = $wallet->transactions()->latest();
        if ($request->filled('type')) $q->where('type', $request->type);
        return response()->json($q->paginate($request->get('per_page', 20)));
    }

    public function fund(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:100',
            'user_id' => 'nullable|exists:users,id', // For front-desk funding
            'method' => 'nullable|in:paystack,flutterwave,cash,pos,transfer,manual',
        ]);

        $user = ($request->user()->isStaff() && !empty($data['user_id']))
            ? \App\Models\User::find($data['user_id'])
            : $request->user();

        // For cash/manual methods, credit immediately. For gateways, you would integrate provider.
        $wallet = $user->getOrCreateWallet();
        $txn = $wallet->credit(
            $data['amount'],
            'Wallet funded via ' . ($data['method'] ?? 'manual'),
            $data['method'] ?? 'manual',
            null,
            $request->user()->id
        );

        // Also record a Payment record
        \App\Models\Payment::create([
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'method' => $data['method'] ?? 'manual',
            'status' => 'successful',
            'processed_by' => $request->user()->id,
            'paid_at' => now(),
            'notes' => 'Wallet funding',
        ]);

        ActivityLogger::log('Wallet funded', $user, [
            'amount' => $data['amount'],
            'method' => $data['method'] ?? 'manual',
        ]);

        return response()->json([
            'message' => 'Wallet funded successfully',
            'wallet' => $wallet->fresh(),
            'transaction' => $txn,
        ]);
    }
}