@extends('layouts.app')
@section('title', 'Booking ' . $booking->booking_code)
@section('content')
<div class="mx-auto max-w-5xl px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Booking {{ $booking->booking_code }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.bookings.edit', $booking) }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm text-white font-semibold">Edit</a>
            <a href="{{ route('admin.bookings.index') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold">Back</a>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <h2 class="font-semibold mb-3">Details</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Member</dt><dd class="font-medium">{{ $booking->user->full_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Type</dt><dd>{{ ucfirst(str_replace('_',' ',$booking->type)) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Date</dt><dd>{{ $booking->booking_date->format('M d, Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Time</dt><dd>{{ $booking->start_time }} - {{ $booking->end_time }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd>{{ ucfirst(str_replace('_',' ',$booking->status)) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Amount</dt><dd>₦{{ number_format($booking->total_amount, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Paid</dt><dd>₦{{ number_format($booking->amount_paid, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Balance</dt><dd class="text-red-600 font-semibold">₦{{ number_format($booking->balance, 2) }}</dd></div>
            </dl>
        </div>

        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <h2 class="font-semibold mb-3">Payments ({{ $booking->payments->count() }})</h2>
            @forelse($booking->payments as $p)
                <div class="flex justify-between py-2 border-b last:border-0 text-sm">
                    <div>
                        <div class="font-medium">₦{{ number_format($p->amount, 2) }}</div>
                        <div class="text-xs text-gray-500">{{ $p->method }} · {{ $p->created_at->format('M d') }}</div>
                    </div>
                    <span class="text-xs rounded-full bg-green-50 text-green-700 px-2 py-0.5 h-fit">{{ $p->status }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">No payments yet.</p>
            @endforelse

            <h2 class="font-semibold mt-4 mb-3">Tickets ({{ $booking->tickets->count() }})</h2>
            @forelse($booking->tickets as $t)
                <div class="text-sm py-2 border-b last:border-0">
                    <div class="font-mono">{{ $t->ticket_code }}</div>
                    <div class="text-xs text-gray-500">Valid until {{ $t->valid_until->format('M d, H:i') }} · {{ $t->status }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No tickets yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection