@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">

    {{-- Greeting --}}
    <div class="flex justify-between items-center mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold">Welcome back, {{ auth()->user()->first_name }} 👋</h1>
            <p class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</p>
        </div>
        <a href="{{ route('member.bookings.create') }}"
           class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            + New Booking
        </a>
    </div>

    {{-- ============================================ --}}
    {{-- PENDING PAYMENTS ALERT --}}
    {{-- ============================================ --}}
    @php
        $pendingBookings = auth()->user()->bookings()
            ->where('payment_status', '!=', 'paid')
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['sessionSlot.sessionType', 'gymClass'])
            ->latest()
            ->get();
    @endphp

    @if($pendingBookings->count())
        <div class="mb-6 rounded-2xl bg-yellow-50 border border-yellow-200 p-5">
            <div class="flex items-start gap-3">
                <div class="text-2xl">⏳</div>
                <div class="flex-1">
                    <h3 class="font-semibold text-yellow-800">
                        {{ $pendingBookings->count() }} booking{{ $pendingBookings->count() > 1 ? 's' : '' }} awaiting payment
                    </h3>
                    <p class="text-sm text-yellow-700 mt-1">
                        Complete payment to receive your tickets and confirm your sessions.
                    </p>

                    <div class="mt-3 space-y-2">
                        @foreach($pendingBookings as $b)
                            @php
                                $title = $b->sessionSlot?->sessionType?->name 
                                      ?? $b->gymClass?->name 
                                      ?? ucfirst(str_replace('_', ' ', $b->type));
                            @endphp
                            <div class="flex justify-between items-center bg-white rounded-lg px-4 py-2 border border-yellow-100">
                                <div>
                                    <div class="text-sm font-medium">{{ $title }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $b->booking_date->format('M d, Y') }} · {{ $b->start_time }} ·
                                        <span class="font-mono">{{ $b->booking_code }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-red-600">₦{{ number_format($b->balance, 2) }}</div>
                                    <a href="{{ route('member.bookings') }}"
                                       class="text-xs text-brand-600 hover:underline font-medium">
                                        Pay now →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================ --}}
    {{-- STAT CARDS --}}
    {{-- ============================================ --}}
    @php
        $ticketCount = auth()->user()->tickets()
            ->where('status', 'active')
            ->where('valid_until', '>=', now())
            ->count();
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

        {{-- Wallet --}}
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="flex justify-between items-start">
                <div class="text-sm text-gray-500">Wallet Balance</div>
                <div class="text-xl">💰</div>
            </div>
            <div class="text-2xl font-bold mt-2">₦{{ number_format($wallet->balance, 2) }}</div>
            <a href="{{ route('member.wallet') }}" class="text-xs text-brand-600 font-medium hover:underline">
                Fund wallet →
            </a>
            @if($wallet->balance < 5000)
                <div class="mt-2 text-[10px] text-orange-600 font-medium">
                    ⚠️ Balance is low
                </div>
            @endif
        </div>

        {{-- Membership --}}
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="flex justify-between items-start">
                <div class="text-sm text-gray-500">Membership</div>
                <div class="text-xl">🎫</div>
            </div>
            <div class="text-lg font-bold mt-2 truncate">
                {{ $activeMembership?->plan->name ?? 'None' }}
            </div>
            @if($activeMembership)
                @php $days = $activeMembership->daysRemaining(); @endphp
                <div class="text-xs {{ $days <= 7 ? 'text-orange-600' : 'text-green-600' }} font-medium">
                    {{ $days }} day{{ $days !== 1 ? 's' : '' }} left
                </div>
            @else
                <a href="{{ route('member.membership') }}" class="text-xs text-brand-600 font-medium hover:underline">
                    Subscribe →
                </a>
            @endif
        </div>

        {{-- Upcoming Bookings --}}
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="flex justify-between items-start">
                <div class="text-sm text-gray-500">Upcoming</div>
                <div class="text-xl">📅</div>
            </div>
            <div class="text-2xl font-bold mt-2">{{ $upcomingBookings->count() }}</div>
            <a href="{{ route('member.bookings.create') }}" class="text-xs text-brand-600 font-medium hover:underline">
                Book a session →
            </a>
        </div>

        {{-- Active Tickets --}}
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="flex justify-between items-start">
                <div class="text-sm text-gray-500">Active Tickets</div>
                <div class="text-xl">🎟️</div>
            </div>
            <div class="text-2xl font-bold mt-2">{{ $ticketCount }}</div>
            <a href="{{ route('member.tickets') }}" class="text-xs text-brand-600 font-medium hover:underline">
                View tickets →
            </a>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- QUICK ACTIONS --}}
    {{-- ============================================ --}}
    <div class="mt-6 rounded-2xl bg-gradient-to-br from-brand-600 to-brand-500 p-5 text-white">
        <h2 class="text-sm font-semibold mb-3">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <a href="{{ route('member.bookings.create') }}"
               class="rounded-lg bg-white/10 backdrop-blur border border-white/20 p-3 text-center hover:bg-white/20 transition">
                <div class="text-xl">📅</div>
                <div class="text-xs font-medium mt-1">Book Session</div>
            </a>
            <a href="{{ route('member.membership') }}"
               class="rounded-lg bg-white/10 backdrop-blur border border-white/20 p-3 text-center hover:bg-white/20 transition">
                <div class="text-xl">💳</div>
                <div class="text-xs font-medium mt-1">Membership</div>
            </a>
            <a href="{{ route('member.wallet') }}"
               class="rounded-lg bg-white/10 backdrop-blur border border-white/20 p-3 text-center hover:bg-white/20 transition">
                <div class="text-xl">💰</div>
                <div class="text-xs font-medium mt-1">Fund Wallet</div>
            </a>
            <a href="{{ route('member.tickets') }}"
               class="rounded-lg bg-white/10 backdrop-blur border border-white/20 p-3 text-center hover:bg-white/20 transition">
                <div class="text-xl">🎟️</div>
                <div class="text-xs font-medium mt-1">My Tickets</div>
            </a>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- UPCOMING BOOKINGS + RECENT PAYMENTS --}}
    {{-- ============================================ --}}
    <div class="mt-6 grid lg:grid-cols-2 gap-6">

        {{-- Upcoming Bookings --}}
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold">Upcoming Bookings</h2>
                <a href="{{ route('member.bookings') }}" class="text-xs text-brand-600 hover:underline">View all →</a>
            </div>
            @forelse($upcomingBookings as $b)
                @php
                    $title = $b->sessionSlot?->sessionType?->name 
                          ?? $b->gymClass?->name 
                          ?? ucfirst(str_replace('_', ' ', $b->type));
                    $trainerName = $b->sessionSlot?->trainer?->user?->full_name 
                                ?? $b->trainer?->user?->full_name;
                    $location = $b->sessionSlot?->location;
                @endphp
                <div class="flex justify-between items-center py-3 border-b last:border-0">
                    <div>
                        <div class="font-medium text-sm">{{ $title }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $b->booking_date->format('M d') }} · {{ $b->start_time }} – {{ $b->end_time }}
                            @if($location)
                                · {{ $location }}
                            @endif
                        </div>
                        @if($trainerName)
                            <div class="text-[10px] text-gray-400">with {{ $trainerName }}</div>
                        @endif
                    </div>
                    <span class="text-xs rounded-full px-2 py-1 h-fit font-medium
                        {{ $b->status === 'confirmed' ? 'bg-blue-50 text-blue-700' : '' }}
                        {{ $b->status === 'pending' ? 'bg-yellow-50 text-yellow-700' : '' }}">
                        {{ ucfirst($b->status) }}
                    </span>
                </div>
            @empty
                <div class="text-center py-6">
                    <div class="text-3xl mb-2">📅</div>
                    <p class="text-sm text-gray-500">No upcoming bookings</p>
                    <a href="{{ route('member.bookings.create') }}"
                       class="inline-block mt-3 text-xs text-brand-600 font-medium hover:underline">
                        Book your first session →
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Recent Payments --}}
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold">Recent Payments</h2>
                <a href="{{ route('member.payments') }}" class="text-xs text-brand-600 hover:underline">View all →</a>
            </div>
            @forelse($recentPayments as $p)
                <div class="flex justify-between items-center py-3 border-b last:border-0">
                    <div>
                        <div class="font-medium text-sm">₦{{ number_format($p->amount, 2) }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $p->created_at->format('M d, Y') }} ·
                            {{ ucfirst(str_replace('_', ' ', $p->method)) }}
                        </div>
                    </div>
                    <span class="text-xs rounded-full px-2 py-1 h-fit font-medium
                        {{ $p->status === 'successful' ? 'bg-green-50 text-green-700' : '' }}
                        {{ $p->status === 'pending'    ? 'bg-yellow-50 text-yellow-700' : '' }}
                        {{ $p->status === 'failed'     ? 'bg-red-50 text-red-700' : '' }}">
                        {{ ucfirst($p->status) }}
                    </span>
                </div>
            @empty
                <div class="text-center py-6">
                    <div class="text-3xl mb-2">💳</div>
                    <p class="text-sm text-gray-500">No payments yet</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- NOTIFICATIONS --}}
    {{-- ============================================ --}}
    @if(isset($notifications) && $notifications->count() > 0)
        <div class="mt-6 rounded-2xl bg-white border border-gray-200 p-6">
            <h2 class="font-semibold mb-4">Recent Notifications</h2>
            <div class="space-y-2">
                @foreach($notifications->take(5) as $n)
                    <div class="flex items-start gap-3 py-2 border-b last:border-0">
                        <span class="text-lg">
                            @if($n->type === 'success') ✅
                            @elseif($n->type === 'warning') ⚠️
                            @elseif($n->type === 'error') ❌
                            @else 🔔
                            @endif
                        </span>
                        <div class="flex-1">
                            <div class="text-sm font-medium {{ !$n->is_read ? 'text-gray-900' : 'text-gray-500' }}">
                                {{ $n->title }}
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ $n->message }}</div>
                            <div class="text-[10px] text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection