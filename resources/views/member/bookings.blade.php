@extends('layouts.app')
@section('title', 'My Bookings')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10" x-data="bookingPage()">

        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold">My Bookings</h1>
                <p class="text-sm text-gray-500">View, pay for, and manage your bookings</p>
            </div>
            <a href="{{ route('member.bookings.create') }}"
                class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                + New Booking
            </a>
        </div>

        {{-- Table --}}
        <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left p-4">Code</th>
                            <th class="text-left p-4">Type</th>
                            <th class="text-left p-4">Date &amp; Time</th>
                            <th class="text-left p-4">Amount</th>
                            <th class="text-left p-4">Paid</th>
                            <th class="text-left p-4">Balance</th>
                            <th class="text-left p-4">Status</th>
                            <th class="text-right p-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $b)
                            <tr class="border-t hover:bg-gray-50 transition">
                                <td class="p-4">
                                    <span
                                        class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $b->booking_code }}</span>
                                </td>
                                <td class="p-4">
                                    <div class="font-medium text-xs">
                                        {{ $b->gymClass?->name ?? ucfirst(str_replace('_', ' ', $b->type)) }}
                                    </div>
                                    @if ($b->trainer)
                                        <div class="text-[10px] text-gray-500">with {{ $b->trainer->user->full_name }}</div>
                                    @endif
                                </td>
                                <td class="p-4 text-xs">
                                    <div>{{ $b->booking_date->format('M d, Y') }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $b->start_time }} – {{ $b->end_time }}</div>
                                </td>
                                <td class="p-4 text-xs font-medium">₦{{ number_format($b->total_amount, 2) }}</td>
                                <td class="p-4 text-xs text-green-600">₦{{ number_format($b->amount_paid, 2) }}</td>
                                <td class="p-4 text-xs">
                                    @if ($b->balance > 0)
                                        <span class="font-semibold text-red-600">₦{{ number_format($b->balance, 2) }}</span>
                                    @else
                                        <span class="text-green-600 text-[10px] font-medium">Cleared</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span
                                        class="rounded-full px-2 py-1 text-[11px] font-medium
                                    {{ $b->status === 'confirmed' ? 'bg-blue-50 text-blue-700' : '' }}
                                    {{ $b->status === 'pending' ? 'bg-yellow-50 text-yellow-700' : '' }}
                                    {{ $b->status === 'in_progress' ? 'bg-purple-50 text-purple-700' : '' }}
                                    {{ $b->status === 'completed' ? 'bg-green-50 text-green-700' : '' }}
                                    {{ $b->status === 'cancelled' ? 'bg-red-50 text-red-700' : '' }}
                                    {{ $b->status === 'no_show' ? 'bg-gray-100 text-gray-600' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $b->status)) }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        {{-- Pay button --}}
                                        @if ($b->balance > 0 && in_array($b->status, ['pending', 'confirmed']))
                                            <button type="button"
                                                @click="openPayModal({{ $b->id }}, '{{ $b->booking_code }}', {{ $b->balance }})"
                                                class="text-xs rounded-lg bg-brand-600 text-white px-3 py-1.5 font-medium hover:bg-brand-700">
                                                Pay
                                            </button>
                                        @endif

                                        {{-- Cancel button --}}
                                        @if (in_array($b->status, ['pending', 'confirmed']) && !$b->booking_date->isPast())
                                            <form method="POST" action="{{ route('member.bookings.cancel', $b) }}"
                                                onsubmit="return confirmSubmit(event, 'Cancel booking {{ $b->booking_code }}? This cannot be undone.', 'Yes, cancel it')">
                                                @csrf
                                                <button type="submit"
                                                    class="text-xs rounded-lg bg-red-50 text-red-700 px-2 py-1.5 font-medium hover:bg-red-100">
                                                    Cancel
                                                </button>
                                            </form>
                                        @elseif($b->status === 'cancelled')
                                            <span class="text-[10px] text-gray-400">Cancelled</span>
                                        @elseif($b->status === 'completed')
                                            <span class="text-[10px] text-gray-400">Done</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-12 text-center">
                                    <div class="text-4xl mb-3">📅</div>
                                    <div class="text-gray-500 font-medium">No bookings yet</div>
                                    <div class="text-xs text-gray-400 mt-1">Book your first session to get started</div>
                                    <a href="{{ route('member.bookings.create') }}"
                                        class="inline-block mt-4 rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-700">
                                        + Book a Session
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($bookings->hasPages())
            <div class="mt-4">{{ $bookings->links() }}</div>
        @endif

        {{-- ============================================ --}}
        {{-- PAYMENT MODAL --}}
        {{-- ============================================ --}}
        <div x-show="payModal.open" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            @click.self="payModal.open = false">

            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-lg font-bold">Pay for Booking</h2>
                        <p class="text-xs text-gray-500 mt-1">
                            <span class="font-mono" x-text="payModal.bookingCode"></span>
                        </p>
                    </div>
                    <button @click="payModal.open = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <div class="rounded-xl bg-brand-50 border border-brand-200 p-4 mb-4">
                    <div class="text-xs text-brand-700">Amount Due</div>
                    <div class="text-2xl font-bold text-brand-800">
                        ₦<span x-text="payModal.amount.toLocaleString('en-NG', {minimumFractionDigits: 2})"></span>
                    </div>
                </div>

                <form method="POST" :action="`/member/bookings/${payModal.bookingId}/pay`"
                    onsubmit="return confirmSubmit(event, 'Confirm payment?', 'Yes, pay now')">
                    @csrf

                    <div class="mb-4">
                        <label class="text-sm font-medium mb-2 block">Payment Method</label>
                        <div class="space-y-2">
                            <label
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-brand-400 transition">
                                <input type="radio" name="payment_method" value="wallet" checked class="text-brand-600">
                                <div>
                                    <div class="text-sm font-medium">💰 Pay from Wallet</div>
                                    <div class="text-[11px] text-gray-500">
                                        Balance: ₦{{ number_format(auth()->user()->wallet?->balance ?? 0, 2) }}
                                    </div>
                                </div>
                            </label>

                            <label
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-brand-400 transition">
                                <input type="radio" name="payment_method" value="cash" class="text-brand-600">
                                <div>
                                    <div class="text-sm font-medium">💵 Pay at Gym (Front Desk)</div>
                                    <div class="text-[11px] text-gray-500">
                                        Note your intent — pay cash/POS at the gym
                                    </div>
                                </div>
                            </label>

                            <label
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg opacity-50 cursor-not-allowed">
                                <input type="radio" name="payment_method" value="paystack" disabled
                                    class="text-brand-600">
                                <div>
                                    <div class="text-sm font-medium">🏦 Card (Coming Soon)</div>
                                    <div class="text-[11px] text-gray-500">Paystack / Flutterwave</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" @click="payModal.open = false"
                            class="flex-1 rounded-lg bg-gray-100 py-2.5 text-sm font-semibold text-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 rounded-lg bg-brand-600 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                            Pay Now
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Membership upsell if no active membership --}}
        @if (!auth()->user()->hasActiveMembership())
            <div class="mt-6 rounded-2xl bg-gradient-to-br from-brand-600 to-brand-500 text-white p-6">
                <div class="flex justify-between items-center flex-wrap gap-3">
                    <div>
                        <div class="font-semibold text-lg">💡 Save money with a membership</div>
                        <div class="text-sm text-white/90 mt-1">
                            Subscribe to a plan and get unlimited access to eligible group classes.
                        </div>
                    </div>
                    <a href="{{ route('member.membership') }}"
                        class="rounded-lg bg-white text-brand-700 px-4 py-2 text-sm font-semibold hover:bg-gray-100">
                        View Plans →
                    </a>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function bookingPage() {
                return {
                    payModal: {
                        open: false,
                        bookingId: null,
                        bookingCode: '',
                        amount: 0,
                    },
                    openPayModal(id, code, amount) {
                        this.payModal.bookingId = id;
                        this.payModal.bookingCode = code;
                        this.payModal.amount = parseFloat(amount);
                        this.payModal.open = true;
                    }
                }
            }
        </script>
    @endpush
@endsection
