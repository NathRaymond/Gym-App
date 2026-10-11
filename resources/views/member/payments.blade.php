@extends('layouts.app')
@section('title', 'My Payments')

@section('content')
<div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-2xl font-bold mb-6">My Payments</h1>

    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left p-4">Code</th>
                    <th class="text-left p-4">Amount</th>
                    <th class="text-left p-4">Method</th>
                    <th class="text-left p-4">Status</th>
                    <th class="text-left p-4">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr class="border-t border-gray-100">
                        <td class="p-4 font-mono text-xs">{{ $p->payment_code }}</td>
                        <td class="p-4 font-semibold">₦{{ number_format($p->amount, 2) }}</td>
                        <td class="p-4">{{ ucfirst(str_replace('_', ' ', $p->method)) }}</td>
                        <td class="p-4">
                            <span class="rounded-full px-2 py-1 text-xs
                                {{ $p->status === 'successful' ? 'bg-green-50 text-green-700' : '' }}
                                {{ $p->status === 'pending' ? 'bg-yellow-50 text-yellow-700' : '' }}
                                {{ $p->status === 'failed' ? 'bg-red-50 text-red-700' : '' }}
                                {{ $p->status === 'refunded' ? 'bg-gray-100 text-gray-600' : '' }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="p-4 text-gray-500">{{ $p->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500">
                            No payments yet. <a href="{{ route('member.wallet') }}" class="text-brand-600 font-medium">Fund your wallet</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
</div>
@endsection