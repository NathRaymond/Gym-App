@extends('layouts.app')
@section('title', 'Front Desk — Bookings')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">All Bookings</h1>
            <p class="text-sm text-gray-500">View and manage member bookings</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search code or member..."
               class="rounded-lg border-gray-300 border px-3 py-2 text-sm w-64">
        <select name="status" class="rounded-lg border-gray-300 border px-3 py-2 text-sm">
            <option value="">All Status</option>
            @foreach(['pending','confirmed','in_progress','completed','cancelled','no_show'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>
                    {{ ucfirst(str_replace('_', ' ', $s)) }}
                </option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ request('date') }}"
               class="rounded-lg border-gray-300 border px-3 py-2 text-sm">
        <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white">Filter</button>
        @if(request()->hasAny(['search', 'status', 'date']))
            <a href="{{ route('frontdesk.bookings') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium">
                Reset
            </a>
        @endif
    </form>

    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left p-4">Code</th>
                        <th class="text-left p-4">Member</th>
                        <th class="text-left p-4">Booking</th>
                        <th class="text-left p-4">Date &amp; Time</th>
                        <th class="text-left p-4">Amount</th>
                        <th class="text-left p-4">Balance</th>
                        <th class="text-left p-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                        <tr class="border-t hover:bg-gray-50 transition">
                            <td class="p-4">
                                <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $b->booking_code }}</span>
                            </td>
                            <td class="p-4">
                                <div class="font-medium">{{ $b->user->full_name ?? '—' }}</div>
                                <div class="text-xs text-gray-500">{{ $b->user->phone ?? '' }}</div>
                            </td>
                            <td class="p-4">
                                <div class="text-xs">{{ $b->gymClass?->name ?? ucfirst(str_replace('_', ' ', $b->type)) }}</div>
                                @if($b->trainer)
                                    <div class="text-[10px] text-gray-500">with {{ $b->trainer->user->full_name }}</div>
                                @endif
                            </td>
                            <td class="p-4 text-xs">
                                <div>{{ $b->booking_date->format('M d, Y') }}</div>
                                <div class="text-[10px] text-gray-500">{{ $b->start_time }} – {{ $b->end_time }}</div>
                            </td>
                            <td class="p-4 text-xs font-medium">₦{{ number_format($b->total_amount, 2) }}</td>
                            <td class="p-4 text-xs">
                                @if($b->balance > 0)
                                    <span class="font-semibold text-red-600">₦{{ number_format($b->balance, 2) }}</span>
                                @else
                                    <span class="text-green-600 text-[10px] font-medium">Cleared</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="rounded-full px-2 py-1 text-[11px] font-medium
                                    {{ $b->status === 'confirmed'   ? 'bg-blue-50 text-blue-700' : '' }}
                                    {{ $b->status === 'pending'     ? 'bg-yellow-50 text-yellow-700' : '' }}
                                    {{ $b->status === 'in_progress' ? 'bg-purple-50 text-purple-700' : '' }}
                                    {{ $b->status === 'completed'   ? 'bg-green-50 text-green-700' : '' }}
                                    {{ $b->status === 'cancelled'   ? 'bg-red-50 text-red-700' : '' }}
                                    {{ $b->status === 'no_show'     ? 'bg-gray-100 text-gray-600' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $b->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center">
                                <div class="text-4xl mb-3">📅</div>
                                <div class="text-gray-500 font-medium">No bookings found</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($bookings->hasPages())
        <div class="mt-4">{{ $bookings->withQueryString()->links() }}</div>
    @endif
</div>
@endsection