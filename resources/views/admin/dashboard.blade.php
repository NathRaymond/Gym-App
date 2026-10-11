@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Admin Dashboard</h1>
            <p class="text-sm text-gray-500">Welcome back, {{ auth()->user()->first_name }}</p>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-xs text-gray-500">Revenue Today</div>
            <div class="text-xl font-bold mt-1">₦{{ number_format($revenueToday, 0) }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-xs text-gray-500">Revenue This Month</div>
            <div class="text-xl font-bold mt-1">₦{{ number_format($revenueMonth, 0) }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-xs text-gray-500">Total Members</div>
            <div class="text-xl font-bold mt-1">{{ number_format($totalMembers) }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-xs text-gray-500">Active Members</div>
            <div class="text-xl font-bold mt-1">{{ number_format($activeMembers) }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-xs text-gray-500">Bookings Today</div>
            <div class="text-xl font-bold mt-1">{{ $bookingsToday }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-xs text-gray-500">Pending Bookings</div>
            <div class="text-xl font-bold mt-1">{{ $pendingBookings }}</div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mt-6 rounded-2xl bg-white border border-gray-200 p-6">
        <h2 class="font-semibold mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-3">
            <a href="{{ route('admin.users.create') }}" class="rounded-xl border border-gray-200 p-4 hover:border-brand-400 hover:bg-brand-50 transition">
                <div class="text-2xl">👤</div>
                <div class="text-sm font-medium mt-2">Add User</div>
            </a>
            <a href="{{ route('admin.plans.create') }}" class="rounded-xl border border-gray-200 p-4 hover:border-brand-400 hover:bg-brand-50 transition">
                <div class="text-2xl">📋</div>
                <div class="text-sm font-medium mt-2">New Plan</div>
            </a>
            <a href="{{ route('admin.classes.create') }}" class="rounded-xl border border-gray-200 p-4 hover:border-brand-400 hover:bg-brand-50 transition">
                <div class="text-2xl">🏋️</div>
                <div class="text-sm font-medium mt-2">New Class</div>
            </a>
            <a href="{{ route('admin.trainers.create') }}" class="rounded-xl border border-gray-200 p-4 hover:border-brand-400 hover:bg-brand-50 transition">
                <div class="text-2xl">💪</div>
                <div class="text-sm font-medium mt-2">Add Trainer</div>
            </a>
            <a href="{{ route('admin.bookings.create') }}" class="rounded-xl border border-gray-200 p-4 hover:border-brand-400 hover:bg-brand-50 transition">
                <div class="text-2xl">📅</div>
                <div class="text-sm font-medium mt-2">New Booking</div>
            </a>
            <a href="{{ route('admin.announcements.create') }}" class="rounded-xl border border-gray-200 p-4 hover:border-brand-400 hover:bg-brand-50 transition">
                <div class="text-2xl">📢</div>
                <div class="text-sm font-medium mt-2">Announce</div>
            </a>
            <a href="{{ route('admin.reports.revenue') }}" class="rounded-xl border border-gray-200 p-4 hover:border-brand-400 hover:bg-brand-50 transition">
                <div class="text-2xl">💰</div>
                <div class="text-sm font-medium mt-2">Revenue</div>
            </a>
            <a href="{{ route('admin.activity') }}" class="rounded-xl border border-gray-200 p-4 hover:border-brand-400 hover:bg-brand-50 transition">
                <div class="text-2xl">🔍</div>
                <div class="text-sm font-medium mt-2">Audit Log</div>
            </a>
        </div>
    </div>

    {{-- Revenue Chart --}}
    <div class="mt-6 rounded-2xl bg-white border border-gray-200 p-6">
        <h2 class="font-semibold mb-4">Revenue – Last 30 Days</h2>
        <canvas id="revChart" height="80"></canvas>
    </div>

    {{-- Recent Activity --}}
    <div class="mt-6 grid lg:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold">Recent Payments</h2>
                <a href="{{ route('admin.payments') }}" class="text-xs text-brand-600 hover:underline">View all →</a>
            </div>
            @forelse($recentPayments as $p)
                <div class="flex justify-between py-2 border-b last:border-0 text-sm">
                    <div>
                        <div class="font-medium">{{ $p->user->full_name ?? '—' }}</div>
                        <div class="text-xs text-gray-500">{{ $p->created_at->diffForHumans() }} · {{ $p->method }}</div>
                    </div>
                    <div class="font-semibold">₦{{ number_format($p->amount, 2) }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No payments yet.</p>
            @endforelse
        </div>

        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold">Recent Bookings</h2>
                <a href="{{ route('admin.bookings.index') }}" class="text-xs text-brand-600 hover:underline">View all →</a>
            </div>
            @forelse($recentBookings as $b)
                <div class="flex justify-between py-2 border-b last:border-0 text-sm">
                    <div>
                        <div class="font-medium">{{ $b->user->full_name ?? '—' }}</div>
                        <div class="text-xs text-gray-500">{{ $b->gymClass?->name ?? 'Session' }} · {{ $b->booking_date->format('M d') }}</div>
                    </div>
                    <span class="text-xs rounded-full bg-gray-100 px-2 py-1 h-fit">{{ ucfirst($b->status) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">No bookings yet.</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
const ctx = document.getElementById('revChart');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json(collect($revenueChart)->pluck('date')),
            datasets: [{
                label: 'Revenue',
                data: @json(collect($revenueChart)->pluck('total')),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,.1)',
                fill: true,
                tension: .3
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
}
</script>
@endpush
@endsection