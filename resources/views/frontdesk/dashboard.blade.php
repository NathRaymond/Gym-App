@extends('layouts.app')
@section('title', 'Front Desk Dashboard')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold">Front Desk Dashboard</h1>
            <p class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('frontdesk.verify-ticket') }}"
               class="rounded-lg bg-brand-600 px-4 py-2 text-sm text-white font-semibold hover:bg-brand-700">
                🎫 Verify Ticket
            </a>
            <a href="{{ route('frontdesk.payments') }}"
               class="rounded-lg bg-gray-800 px-4 py-2 text-sm text-white font-semibold hover:bg-gray-900">
                💵 Record Payment
            </a>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="flex justify-between items-start">
                <div class="text-xs text-gray-500">Today's Bookings</div>
                <div class="text-xl">📅</div>
            </div>
            <div class="text-2xl font-bold mt-2">{{ $todayBookings->count() }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="flex justify-between items-start">
                <div class="text-xs text-gray-500">Pending Payments</div>
                <div class="text-xl">⏳</div>
            </div>
            <div class="text-2xl font-bold mt-2 text-red-600">{{ $pendingPayments->count() }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="flex justify-between items-start">
                <div class="text-xs text-gray-500">Active Tickets</div>
                <div class="text-xl">🎫</div>
            </div>
            <div class="text-2xl font-bold mt-2 text-green-600">{{ $pendingTickets }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="flex justify-between items-start">
                <div class="text-xs text-gray-500">Expiring (7 days)</div>
                <div class="text-xl">⚠️</div>
            </div>
            <div class="text-2xl font-bold mt-2 text-orange-600">{{ $membershipsExpiring->count() }}</div>
        </div>
    </div>

    {{-- Two-column layout --}}
    <div class="grid lg:grid-cols-2 gap-6">

        {{-- Today's Bookings --}}
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold">Today's Bookings</h2>
                <a href="{{ route('frontdesk.bookings') }}" class="text-xs text-brand-600 hover:underline">
                    View all →
                </a>
            </div>
            @forelse($todayBookings as $b)
                <div class="flex justify-between items-center py-3 border-b last:border-0 text-sm">
                    <div>
                        <div class="font-medium">{{ $b->user->full_name }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $b->gymClass?->name ?? ucfirst(str_replace('_', ' ', $b->type)) }}
                            · {{ $b->start_time }}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs rounded-full px-2 py-1 h-fit font-medium
                            {{ $b->status === 'confirmed' ? 'bg-blue-50 text-blue-700' : '' }}
                            {{ $b->status === 'pending'   ? 'bg-yellow-50 text-yellow-700' : '' }}
                            {{ $b->status === 'completed' ? 'bg-green-50 text-green-700' : '' }}
                            {{ $b->status === 'cancelled' ? 'bg-red-50 text-red-700' : '' }}">
                            {{ ucfirst($b->status) }}
                        </span>
                        @if($b->balance > 0)
                            <div class="text-xs text-red-600 font-medium mt-1">
                                ₦{{ number_format($b->balance, 2) }} owing
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center py-4">No bookings today.</p>
            @endforelse
        </div>

        {{-- Pending Payments --}}
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold">Pending Payments</h2>
                <a href="{{ route('frontdesk.payments') }}" class="text-xs text-brand-600 hover:underline">
                    Record payment →
                </a>
            </div>
            @forelse($pendingPayments as $b)
                <div class="flex justify-between items-center py-3 border-b last:border-0 text-sm">
                    <div>
                        <div class="font-medium">{{ $b->user->full_name }}</div>
                        <div class="text-xs text-gray-500 font-mono">{{ $b->booking_code }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-red-600">₦{{ number_format($b->balance, 2) }}</div>
                        <div class="text-xs text-gray-400">owing</div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center py-4">No pending payments. 🎉</p>
            @endforelse
        </div>
    </div>

    {{-- Expiring Memberships --}}
    @if($membershipsExpiring->count())
        <div class="mt-6 rounded-2xl bg-orange-50 border border-orange-200 p-6">
            <h2 class="font-semibold text-orange-800 mb-3">
                ⚠️ Memberships Expiring Soon (7 days)
            </h2>
            <div class="space-y-2">
                @foreach($membershipsExpiring as $m)
                    <div class="flex justify-between items-center bg-white rounded-lg px-4 py-2 text-sm">
                        <div>
                            <div class="font-medium">{{ $m->user->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $m->plan->name }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-orange-600 font-medium">
                                Expires {{ $m->end_date->format('M d, Y') }}
                            </div>
                            <div class="text-[10px] text-gray-500">
                                {{ $m->daysRemaining() }} days left
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection