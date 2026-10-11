@extends('layouts.app')
@section('title', 'Front Desk')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Front Desk</h1>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <h2 class="font-semibold mb-4">Record Manual Payment</h2>
            <form method="POST" action="/api/payments" class="space-y-3" x-data="frontPay()">
                @csrf
                <select x-model="type" class="w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="booking">Booking</option>
                    <option value="membership">Membership</option>
                </select>
                <select x-model="payable_id" class="w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="">-- Select --</option>
                    <template x-if="type === 'booking'">
                        <template x-for="b in {{ $bookings->toJson() }}" :key="b.id">
                            <option :value="b.id" x-text="b.booking_code + ' - ' + b.user.first_name + ' (₦' + (b.total_amount - b.amount_paid) + ')'"></option>
                        </template>
                    </template>
                    <template x-if="type === 'membership'">
                        <template x-for="m in {{ $memberships->toJson() }}" :key="m.id">
                            <option :value="m.id" x-text="m.membership_code + ' - ' + m.user.first_name"></option>
                        </template>
                    </template>
                </select>
                <input type="number" x-model="amount" placeholder="Amount ₦" class="w-full rounded-lg border-gray-300 border px-3 py-2">
                <select x-model="method" class="w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="cash">Cash</option>
                    <option value="pos">POS</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="manual">Manual</option>
                </select>
                <input x-model="notes" placeholder="Notes (optional)" class="w-full rounded-lg border-gray-300 border px-3 py-2">
                <button type="button" @click="submit()" class="w-full rounded-lg bg-brand-600 py-2 font-semibold text-white">Record Payment</button>
                <p x-show="msg" x-text="msg" class="text-sm text-green-600"></p>
            </form>
        </div>

        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <h2 class="font-semibold mb-4">Verify Ticket</h2>
            <div x-data="verify()">
                <input x-model="code" placeholder="Ticket code (TKT-...)" class="w-full rounded-lg border-gray-300 border px-3 py-2 mb-3">
                <button @click="check()" class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Verify</button>
                <div x-show="result" class="mt-4 rounded-xl border p-4" :class="result?.valid ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                    <div class="font-semibold" x-text="result?.message"></div>
                    <template x-if="result?.ticket">
                        <div class="text-sm mt-2">
                            <div>User: <span x-text="result.ticket.user.first_name + ' ' + result.ticket.user.last_name"></span></div>
                            <div>Type: <span x-text="result.ticket.type"></span></div>
                            <div>Valid until: <span x-text="result.ticket.valid_until"></span></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-2xl bg-white border border-gray-200 p-6">
        <h2 class="font-semibold mb-4">Pending Bookings</h2>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600"><tr><th class="text-left p-3">Code</th><th class="text-left p-3">User</th><th class="text-left p-3">Amount</th><th class="text-left p-3">Paid</th><th class="text-left p-3">Balance</th></tr></thead>
            <tbody>
                @foreach($bookings as $b)
                    <tr class="border-t">
                        <td class="p-3">{{ $b->booking_code }}</td>
                        <td class="p-3">{{ $b->user->full_name }}</td>
                        <td class="p-3">₦{{ number_format($b->total_amount, 2) }}</td>
                        <td class="p-3">₦{{ number_format($b->amount_paid, 2) }}</td>
                        <td class="p-3 font-semibold text-red-600">₦{{ number_format($b->balance, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@push('scripts')
<script>
function frontPay() {
    return {
        type: 'booking', payable_id: '', amount: '', method: 'cash', notes: '', msg: '',
        async submit() {
            const res = await fetch('/api/payments', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': 'Bearer {{ auth()->user()->createToken("web")->plainTextToken ?? "" }}' },
                body: JSON.stringify({ payable_type: this.type, payable_id: this.payable_id, amount: this.amount, method: this.method, notes: this.notes })
            });
            const d = await res.json();
            this.msg = d.message || 'Done';
            if (res.ok) setTimeout(() => location.reload(), 900);
        }
    }
}
function verify() {
    return {
        code: '', result: null,
        async check() {
            const res = await fetch('/api/tickets/verify', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': 'Bearer {{ auth()->user()->createToken("web")->plainTextToken ?? "" }}' },
                body: JSON.stringify({ ticket_code: this.code })
            });
            const d = await res.json();
            this.result = d;
        }
    }
}
</script>
@endpush
@endsection
