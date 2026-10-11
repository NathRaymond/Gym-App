@extends('layouts.app')
@section('title', 'All Bookings')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">All Bookings</h1>
        <a href="{{ route('admin.bookings.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">+ Add Booking</a>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Code or member name..." class="rounded-lg border-gray-300 border px-3 py-2 text-sm w-64">
        <select name="status" class="rounded-lg border-gray-300 border px-3 py-2 text-sm">
            <option value="">All Status</option>
            @foreach(['pending','confirmed','in_progress','completed','cancelled','no_show'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ request('date') }}" class="rounded-lg border-gray-300 border px-3 py-2 text-sm">
        <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white">Filter</button>
        @if(request()->hasAny(['search','status','date']))
            <a href="{{ route('admin.bookings.index') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium">Reset</a>
        @endif
    </form>

    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left p-4">Code</th>
                    <th class="text-left p-4">Member</th>
                    <th class="text-left p-4">Type</th>
                    <th class="text-left p-4">Date</th>
                    <th class="text-left p-4">Amount</th>
                    <th class="text-left p-4">Status</th>
                    <th class="text-right p-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-4 font-mono text-xs">{{ $b->booking_code }}</td>
                        <td class="p-4">
                            <div class="font-medium">{{ $b->user->full_name ?? '—' }}</div>
                        </td>
                        <td class="p-4">{{ $b->gymClass?->name ?? ucfirst(str_replace('_',' ',$b->type)) }}</td>
                        <td class="p-4">{{ $b->booking_date->format('M d, Y') }} · {{ $b->start_time }}</td>
                        <td class="p-4">₦{{ number_format($b->total_amount, 2) }}</td>
                        <td class="p-4">
                            <span class="rounded-full px-2 py-0.5 text-xs
                                {{ $b->status === 'confirmed' ? 'bg-blue-50 text-blue-700' : '' }}
                                {{ $b->status === 'pending' ? 'bg-yellow-50 text-yellow-700' : '' }}
                                {{ $b->status === 'completed' ? 'bg-green-50 text-green-700' : '' }}
                                {{ $b->status === 'cancelled' ? 'bg-red-50 text-red-700' : '' }}">
                                {{ ucfirst(str_replace('_',' ',$b->status)) }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('admin.bookings.show', $b) }}" class="text-brand-600 hover:underline text-xs">View</a>
                            <a href="{{ route('admin.bookings.edit', $b) }}" class="text-gray-600 hover:underline text-xs ml-2">Edit</a>
                            <form method="POST" action="{{ route('admin.bookings.destroy', $b) }}" class="inline" onsubmit="return confirmAction(event, 'Delete booking?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs ml-2">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-8 text-center text-gray-500">No bookings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $bookings->withQueryString()->links() }}</div>
</div>
@endsection