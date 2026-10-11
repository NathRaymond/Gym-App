@extends('layouts.app')
@section('title', 'Record Payment')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10" x-data="paymentPage()">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Record Payment</h1>
            <p class="text-sm text-gray-500">Record cash, POS, or bank transfer payments</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">

        {{-- Pending Bookings --}}
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold">Pending Bookings</h2>
                <span class="text-xs bg-yellow-50 text-yellow-700 px-2 py-1 rounded-full">
                    {{ $bookings->total() }} pending
                </span>
            </div>

            @forelse($bookings as $b)
                <div class="flex justify-between items-center py-3 border-b last:border-0 text-sm">
                    <div>
                        <div class="font-medium">{{ $b->user->full_name }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $b->gymClass?->name ?? ucfirst(str_replace('_', ' ', $b->type)) }}
                            · <span class="font-mono">{{ $b->booking_code }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-red-600">₦{{ number_format($b->balance, 2) }}</div>
                        <button type="button"
                                @click="openPayModal('booking', {{ $b->id }}, '{{ $b->booking_code }}', '{{ $b->user->full_name }}', {{ $b->balance }})"
                                class="mt-1 text-xs rounded-lg bg-brand-600 text-white px-3 py-1 font-medium hover:bg-brand-700">
                            Record Payment
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center py-6">
                    No pending bookings. 🎉
                </p>
            @endforelse

            @if($bookings->hasPages())
                <div class="mt-4">{{ $bookings->links() }}</div>
            @endif
        </div>

        {{-- Pending Memberships --}}
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold">Pending Memberships</h2>
                <span class="text-xs bg-yellow-50 text-yellow-700 px-2 py-1 rounded-full">
                    {{ $memberships->total() }} pending
                </span>
            </div>

            @forelse($memberships as $m)
                <div class="flex justify-between items-center py-3 border-b last:border-0 text-sm">
                    <div>
                        <div class="font-medium">{{ $m->user->full_name }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $m->plan->name }}
                            · <span class="font-mono">{{ $m->membership_code }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-red-600">₦{{ number_format($m->plan->price, 2) }}</div>
                        <button type="button"
                                @click="openPayModal('membership', {{ $m->id }}, '{{ $m->membership_code }}', '{{ $m->user->full_name }}', {{ $m->plan->price }})"
                                class="mt-1 text-xs rounded-lg bg-brand-600 text-white px-3 py-1 font-medium hover:bg-brand-700">
                            Record Payment
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center py-6">
                    No pending memberships. 🎉
                </p>
            @endforelse

            @if($memberships->hasPages())
                <div class="mt-4">{{ $memberships->links() }}</div>
            @endif
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- RECORD PAYMENT MODAL --}}
    {{-- ============================================ --}}
    <div x-show="modal.open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @click.self="modal.open = false">

        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">

            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-lg font-bold">Record Payment</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <span class="font-mono" x-text="modal.code"></span>
                    </p>
                </div>
                <button @click="modal.open = false" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <div class="rounded-xl bg-gray-50 border border-gray-200 p-4 mb-4">
                <div class="text-xs text-gray-500" x-text="modal.userName"></div>
                <div class="text-xs text-gray-500 mt-1">Amount Due</div>
                <div class="text-2xl font-bold text-gray-800">
                    ₦<span x-text="modal.amount.toLocaleString('en-NG', {minimumFractionDigits: 2})"></span>
                </div>
            </div>

            <form method="POST" :action="`/frontdesk/payments`"
                  onsubmit="return confirmSubmit(event, 'Confirm this payment was received?', 'Yes, record it')">
                @csrf

                {{-- Payable identification --}}
                <input type="hidden" name="payable_type" :value="modal.type">
                <input type="hidden" name="payable_id" :value="modal.id">

                {{-- Amount (editable — supports partial payments) --}}
                <div class="mb-4">
                    <label class="text-sm font-medium">Amount Received (₦)</label>
                    <input type="number" step="0.01" name="amount" :value="modal.amount" required
                           class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2 focus:border-brand-500 focus:ring-brand-500">
                    <p class="text-xs text-gray-500 mt-1">Leave as-is to pay in full, or change for partial</p>
                </div>

                {{-- Payment method --}}
                <div class="mb-4">
                    <label class="text-sm font-medium mb-2 block">Payment Method</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="method" value="cash" checked class="peer sr-only">
                            <div class="rounded-lg border-2 border-gray-200 p-3 text-center peer-checked:border-brand-500 peer-checked:bg-brand-50 transition">
                                <div class="text-lg">💵</div>
                                <div class="text-xs font-medium mt-1">Cash</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="method" value="pos" class="peer sr-only">
                            <div class="rounded-lg border-2 border-gray-200 p-3 text-center peer-checked:border-brand-500 peer-checked:bg-brand-50 transition">
                                <div class="text-lg">💳</div>
                                <div class="text-xs font-medium mt-1">POS</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="method" value="bank_transfer" class="peer sr-only">
                            <div class="rounded-lg border-2 border-gray-200 p-3 text-center peer-checked:border-brand-500 peer-checked:bg-brand-50 transition">
                                <div class="text-lg">🏦</div>
                                <div class="text-xs font-medium mt-1">Transfer</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="mb-4">
                    <label class="text-sm font-medium">Notes (optional)</label>
                    <input type="text" name="notes" placeholder="e.g., Receipt #12345"
                           class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="modal.open = false"
                            class="flex-1 rounded-lg bg-gray-100 py-2.5 text-sm font-semibold text-gray-700">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 rounded-lg bg-brand-600 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                        Confirm Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function paymentPage() {
    return {
        modal: {
            open: false,
            type: 'booking',
            id: null,
            code: '',
            userName: '',
            amount: 0,
        },
        openPayModal(type, id, code, userName, amount) {
            this.modal = {
                open: true,
                type: type,          // 'booking' or 'membership'
                id: id,
                code: code,
                userName: userName,
                amount: parseFloat(amount),
            };
        }
    }
}
</script>
@endpush
@endsection