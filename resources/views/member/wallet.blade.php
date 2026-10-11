@extends('layouts.app')
@section('title', 'Wallet')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10">

    {{-- Balance Card --}}
    <div class="rounded-2xl bg-gradient-to-br from-brand-700 to-brand-500 text-white p-8 mb-6">
        <div class="text-sm text-white/80">Available Balance</div>
        <div class="text-4xl font-bold mt-2">₦{{ number_format($wallet->balance, 2) }}</div>
        <div class="mt-4 flex gap-6 text-sm text-white/80">
            <span>Total funded: ₦{{ number_format($wallet->total_funded, 2) }}</span>
            <span>Total spent: ₦{{ number_format($wallet->total_spent, 2) }}</span>
        </div>
    </div>

    {{-- Fund Wallet with Paystack --}}
    <div class="rounded-2xl bg-white border border-gray-200 p-6 mb-6">
        <h2 class="font-semibold mb-4">Fund Wallet</h2>

        <form method="POST" action="{{ route('member.wallet.fund.paystack') }}"
              onsubmit="return confirmSubmit(event, 'Proceed to Paystack to fund your wallet?', 'Yes, continue')">
            @csrf

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Amount (₦)</label>
                    <input type="number" name="amount" id="fund_amount"
                           min="100" max="1000000" step="50"
                           value="{{ old('amount', 5000) }}" required
                           class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="w-full rounded-lg bg-brand-600 py-2.5 font-semibold text-white hover:bg-brand-700">
                        💳 Pay with Paystack
                    </button>
                </div>
            </div>
        </form>

        {{-- Quick amounts --}}
        <div class="mt-3 flex flex-wrap gap-2 text-xs">
            <span class="text-gray-500 self-center">Quick amounts:</span>
            @foreach([1000, 5000, 10000, 20000, 50000] as $amt)
                <button type="button" onclick="document.getElementById('fund_amount').value = {{ $amt }}"
                        class="rounded-full bg-gray-100 px-3 py-1 hover:bg-gray-200 transition">
                    ₦{{ number_format($amt) }}
                </button>
            @endforeach
        </div>

        <p class="mt-3 text-xs text-gray-500">
            🔒 You'll be redirected to Paystack's secure checkout to complete payment.
        </p>
    </div>

    {{-- Transactions --}}
    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-semibold">Transaction History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left p-4">Reference</th>
                        <th class="text-left p-4">Description</th>
                        <th class="text-left p-4">Type</th>
                        <th class="text-left p-4">Amount</th>
                        <th class="text-left p-4">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                        <tr class="border-t">
                            <td class="p-4 font-mono text-xs">{{ $t->reference }}</td>
                            <td class="p-4 text-xs">{{ $t->description }}</td>
                            <td class="p-4">
                                <span class="text-xs font-medium {{ $t->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ ucfirst($t->type) }}
                                </span>
                            </td>
                            <td class="p-4 text-xs font-semibold">
                                {{ $t->type === 'credit' ? '+' : '-' }}₦{{ number_format($t->amount, 2) }}
                            </td>
                            <td class="p-4 text-xs text-gray-500">{{ $t->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500 text-sm">
                                No transactions yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</div>
@endsection