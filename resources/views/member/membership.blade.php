@extends('layouts.app')
@section('title', 'Membership')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">My Membership</h1>
            <p class="text-sm text-gray-500">View and manage your gym subscription</p>
        </div>
        @if(auth()->user()->wallet)
            <div class="rounded-lg bg-brand-50 px-3 py-1.5 text-xs text-brand-700">
                💰 Wallet: <strong>₦{{ number_format(auth()->user()->wallet->balance, 2) }}</strong>
            </div>
        @endif
    </div>

    {{-- ============================================ --}}
    {{-- AVAILABLE PLANS --}}
    {{-- ============================================ --}}
    <div class="flex justify-between items-center mb-3">
        <h2 class="font-semibold text-gray-700 text-lg">Available Plans</h2>
        @if(auth()->user()->hasActiveMembership())
            <span class="text-xs text-green-600 font-medium">
                ✅ You have an active membership
            </span>
        @endif
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-10">
        @foreach($plans as $p)
            <div class="rounded-xl bg-white border {{ $p->is_featured ? 'border-brand-500 ring-2 ring-brand-500/20' : 'border-gray-200' }} p-3 flex flex-col relative">

                @if($p->is_featured)
                    <span class="absolute -top-2 left-3 rounded-full bg-brand-600 text-white text-[10px] font-semibold px-2 py-0.5">
                        ⭐ POPULAR
                    </span>
                @endif

                <h3 class="font-semibold text-sm mt-1">{{ $p->name }}</h3>

                {{-- ✅ NEW: Plan description --}}
                @if($p->description)
                    <p class="text-[10px] text-gray-500 mt-0.5 leading-tight line-clamp-2">
                        {{ $p->description }}
                    </p>
                @endif

                <div class="mt-2">
                    <span class="text-xl font-bold">₦{{ number_format($p->price) }}</span>
                    <span class="text-[11px] text-gray-500">/{{ $p->duration_days }}d</span>
                </div>

                <ul class="mt-2 text-[11px] text-gray-600 space-y-1 flex-1">
                    @foreach(array_slice($p->features ?? [], 0, 4) as $f)
                        <li class="flex items-start gap-1">
                            <span class="text-brand-600">✓</span>
                            <span class="leading-tight">{{ $f }}</span>
                        </li>
                    @endforeach
                </ul>

                {{-- Insufficient wallet warning --}}
                @if(auth()->user()->wallet && auth()->user()->wallet->balance < $p->price)
                    <div class="mt-2 rounded bg-yellow-50 border border-yellow-200 px-2 py-1 text-[10px] text-yellow-800">
                        ⚠️ Fund wallet first
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST"
                      action="{{ route('member.membership.subscribe') }}"
                      onsubmit="return confirmSubmit(event, 'Subscribe to {{ $p->name }} for ₦{{ number_format($p->price) }}?', 'Yes, subscribe')"
                      class="mt-2.5 space-y-1.5">
                    @csrf
                    <input type="hidden" name="membership_plan_id" value="{{ $p->id }}">

                    <select name="payment_method" required
                            class="w-full rounded-md border-gray-300 border px-2 py-1.5 text-[11px] focus:border-brand-500 focus:ring-brand-500">
                        <option value="wallet">💳 Wallet</option>
                        <option value="paystack">🏦 Paystack</option>
                        <option value="cash">💵 Pay at Gym</option>
                    </select>

                    <button type="submit"
                            class="w-full rounded-md bg-brand-600 py-1.5 text-xs font-semibold text-white hover:bg-brand-700 transition">
                        Subscribe
                    </button>
                </form>
            </div>
        @endforeach
    </div>

    {{-- ============================================ --}}
    {{-- CURRENT & PAST SUBSCRIPTIONS --}}
    {{-- ============================================ --}}
    <div class="flex justify-between items-center mb-3">
        <h2 class="font-semibold text-gray-700 text-lg">Current &amp; Past Subscriptions</h2>
    </div>

    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden mb-10">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left p-3">Code</th>
                        <th class="text-left p-3">Plan</th>
                        <th class="text-left p-3">Period</th>
                        <th class="text-left p-3">Days Left</th>
                        <th class="text-left p-3">Status</th>
                        <th class="text-left p-3">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($memberships as $m)
                        <tr class="border-t hover:bg-gray-50 transition">
                            <td class="p-3">
                                <span class="font-mono text-[11px] bg-gray-100 px-1.5 py-0.5 rounded">{{ $m->membership_code }}</span>
                            </td>
                            <td class="p-3">
                                <div class="font-medium text-xs">{{ $m->plan->name }}</div>
                                <div class="text-[10px] text-gray-500">{{ $m->plan->duration_days }} days</div>
                            </td>
                            <td class="p-3 text-xs">
                                <div>{{ $m->start_date->format('M d, Y') }}</div>
                                <div class="text-[10px] text-gray-500">→ {{ $m->end_date->format('M d, Y') }}</div>
                            </td>
                            <td class="p-3 text-xs">
                                @if($m->status === 'active' && $m->end_date->isFuture())
                                    <span class="font-medium {{ $m->daysRemaining() <= 7 ? 'text-orange-600' : 'text-green-600' }}">
                                        {{ $m->daysRemaining() }} days
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-medium
                                    {{ $m->status === 'active'    ? 'bg-green-50 text-green-700' : '' }}
                                    {{ $m->status === 'pending'   ? 'bg-yellow-50 text-yellow-700' : '' }}
                                    {{ $m->status === 'expired'   ? 'bg-gray-100 text-gray-600' : '' }}
                                    {{ $m->status === 'cancelled' ? 'bg-red-50 text-red-700' : '' }}
                                    {{ $m->status === 'frozen'    ? 'bg-blue-50 text-blue-700' : '' }}">
                                    {{ ucfirst($m->status) }}
                                </span>
                            </td>
                            <td class="p-3 text-xs font-medium">₦{{ number_format($m->plan->price, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center">
                                <div class="text-3xl mb-2">💳</div>
                                <div class="text-gray-500 text-sm font-medium">No membership yet</div>
                                <div class="text-[11px] text-gray-400 mt-1">Choose a plan above to get started</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Help text --}}
    <div class="rounded-xl bg-gray-50 border border-gray-200 p-4">
        <h3 class="font-semibold text-xs mb-2">💡 Good to know</h3>
        <ul class="text-[11px] text-gray-600 space-y-1">
            <li>• <strong>Pay from Wallet</strong> — instant activation, ticket generated immediately</li>
            <li>• <strong>Paystack</strong> — pay with your card via secure checkout</li>
            <li>• <strong>Pay at Gym</strong> — front desk records your cash/POS payment, then membership activates</li>
            <li>• You can only have <strong>one active membership</strong> at a time</li>
            <li>• Having an active membership makes eligible <strong>group classes free</strong></li>
        </ul>
    </div>
</div>
@endsection